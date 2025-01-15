<?php

declare(strict_types=1);

namespace App\Controllers;

use Valitron\Validator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/** Controller for submitting code to be executed */
class CodeSubmission
{
    private static $DOCKER_PATH;
    private static $DOCKERFILE_PATH;
    private static $TIMEOUT = 5;
    private static $MEMORY_LIMIT = 128;
    private static $MAX_LENGTH = 256;

    private Validator $codeValidator;

    public function __construct()
    {
        self::$DOCKER_PATH = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'Docker';

        self::$DOCKERFILE_PATH = self::$DOCKER_PATH
            . DIRECTORY_SEPARATOR . 'Dockerfile';

        $this->codeValidator = new Validator();
        $this->codeValidator->mapFieldsRules([
            'code' => ['required'],
            'input' => ['required'],
        ]);
    }

    public function __invoke(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        // Validate the request body
        $validator = $this->codeValidator->withData($body);
        if(!$validator->validate()){ // If validation fails
            $response->getBody()
                     ->write(json_encode($validator->errors(),
                                                 JSON_FORCE_OBJECT));

            return $response->withStatus(422);
        }
        
        // Create a Docker container worker to run the code
        $container_name = $this->createWorker();

        $this->writeToContainerFile($body['code'], 'user_code.py', $container_name);
        $this->writeToContainerFile($body['input'], 'input.txt', $container_name);

        try{ // Run the user code in the Docker container
            $json_output = $this->executeCode($container_name);
        }
        catch(\Exception $e){ // Shouldn't happen, but just in case
            $json_output = [
                'status' => 'error',
                'stderr' => $e->getMessage(),
            ];
        }
        finally{ // Remove the Docker container
            shell_exec("docker rm -f $container_name");
        }

        $response->getBody()->write(json_encode($json_output, JSON_FORCE_OBJECT));
        return $response;
    }

    private function createWorker(): string
    {
        // Build the Docker image
        $build_command = sprintf(
            "docker build -t python-restricted-image -f %s %s",
            escapeshellarg(self::$DOCKERFILE_PATH),
            escapeshellarg(self::$DOCKER_PATH)
        );
        
        shell_exec($build_command);
        
        // Run the Docker container
        $container_name = uniqid('container-', true);
        $docker_run_command = "docker run -itd --name " . escapeshellarg($container_name)
            . " -m 128m" // Limit memory usage to 128MB
            . " python-restricted-image"; // Use the Docker image

        shell_exec($docker_run_command);

        return $container_name;
    }

    private function writeToContainerFile(array|string $data, string $filename, string $container_name)
    {
        // If data is a list, convert it to a string
        // by joining the elements with a newline character
        if(is_array($data)){
            $data = implode("\n", $data);
        }

        $tempFile = tempnam(sys_get_temp_dir(), '');
        file_put_contents($tempFile, $data);

        // Copy the code to the Docker container
        shell_exec(sprintf(
            "docker exec -i %s tee %s < %s",
            escapeshellarg($container_name),
            escapeshellarg($filename),
            escapeshellarg($tempFile)
        ));

        // Remove the temporary file
        unlink($tempFile);
    }

    private function executeCode(string $container_name)
    {
        $json_output = [
            'status' => '',
            'stdout' => '',
            'stderr' => '',
        ];

        // Create temporary file to store the stderr
        $tempErrorFile = tempnam(sys_get_temp_dir(), 'stderr');
        $tempOutputFile = tempnam(sys_get_temp_dir(), 'stdout');

        try{ 
            $execute_code_command = sprintf(
                "docker exec %s timeout %d python3 execute_user_code.py",
                escapeshellarg($container_name),
                self::$TIMEOUT
            ); // Execute the code in the Docker container and limit the execution time
            
            $route_std_command = sprintf(
                " > %s 2> %s",
                escapeshellarg($tempOutputFile),
                escapeshellarg($tempErrorFile)
            ); // Route the stdout and stderr to temporary files

            exec($execute_code_command . $route_std_command, result_code: $return_code);

            // Read the stdout and stderr from the temporary files
            $output = file_get_contents($tempOutputFile, length: self::$MAX_LENGTH);
            $stderr = file_get_contents($tempErrorFile, length: self::$MAX_LENGTH);

            $json_output['status'] = $return_code === 0 ? 'success' : 'error';
            $json_output['stdout'] = $output;
            $json_output['stderr'] = $return_code === 124 ? 'Timed out' : $stderr;
        }
        catch(\Exception $e){
            $json_output['status'] = 'error';
            $json_output['stderr'] = $e->getMessage();
        }
        finally{
            // Remove the temporary file
            unlink($tempErrorFile);
            unlink($tempOutputFile);
        }

        return $json_output;
    }
}