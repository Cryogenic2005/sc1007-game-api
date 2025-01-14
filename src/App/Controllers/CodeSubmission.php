<?php

declare(strict_types=1);

namespace App\Controllers;

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

    public function __construct()
    {
        self::$DOCKER_PATH = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'Docker';

        self::$DOCKERFILE_PATH = self::$DOCKER_PATH
            . DIRECTORY_SEPARATOR . 'Dockerfile';
    }

    public function __invoke(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        $code = $body['code'];
        
        // Get the current time, saves as a unique identifier for the request
        $request_id = uniqid();

        // Create a temporary file to store the code
        $tempFile = $this->createTempfile($code, $request_id);

        // Create a Docker container worker to run the code
        $container_name = $this->createWorker($request_id);

        // Copy the code to the Docker container
        $copy_command = sprintf(
            "docker cp %s %s:/home/restricted/code/user_code.py",
            escapeshellarg($tempFile),
            escapeshellarg($container_name)
        );
        shell_exec($copy_command);

        // Run the code in the Docker container
        $json_output = $this->executeCode($container_name);

        // Cleanup
        $this->cleanup($tempFile, $container_name);

        $response->getBody()->write(json_encode($json_output, JSON_FORCE_OBJECT));
        return $response;
    }

    private function createTempfile(string $code, string $request_id): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), "script{$request_id}_");
        file_put_contents($tempFile, $code);

        return $tempFile;
    }

    private function createWorker(string $request_id): string
    {
        // Build the Docker image
        $build_command = sprintf(
            "docker build -t python-restricted-image -f %s %s",
            escapeshellarg(self::$DOCKERFILE_PATH),
            escapeshellarg(self::$DOCKER_PATH)
        );

        shell_exec($build_command);
        
        // Run the Docker container
        $container_name = "python-restricted-container-{$request_id}";
        
        $docker_run_command = "docker run -itd --name " . escapeshellarg($container_name)
            . " -m 128m" // Limit memory usage to 128MB
            . " python-restricted-image"; // Use the Docker image

        shell_exec($docker_run_command);

        return $container_name;
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
            // Execute the code in the Docker container, and redirect stderr to a file
            $execute_code_command = sprintf(
                "docker exec %s timeout %d python3 /home/restricted/code/user_code.py",
                escapeshellarg($container_name),
                self::$TIMEOUT
            );
            $route_std_command = " >$tempOutputFile 2>$tempErrorFile";

            exec($execute_code_command . $route_std_command, result_code: $return_code);

            // Read the stdout file
            $output = file_get_contents($tempOutputFile, length: self::$MAX_LENGTH);

            // Read the stderr file
            $stderr = file_get_contents($tempErrorFile, length: self::$MAX_LENGTH);

            // Get the output of the code execution
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
            // unlink($tempErrorFile);
            // unlink($tempOutputFile);
        }

        return $json_output;
    }

    private function cleanup(string $tempFile, string $container_name)
    {
        // Remove the temporary file
        unlink($tempFile);

        // Remove the Docker container
        shell_exec("docker rm -f $container_name");
    }
}