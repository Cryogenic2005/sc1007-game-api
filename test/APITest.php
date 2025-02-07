<?php

declare(strict_types=1);

use App\AppBuilder;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;

class APITest extends TestCase
{
    protected App $app;

    protected function setUp(): void
    {
        $this->app = (new AppBuilder())->get();
    }

    public function testAPI()
    {
        // Create a PSR-7 request object
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/api');

        $response = $this->app->handle($request);
        
        // Assert status code
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame($response->getHeaderLine('Content-Type'), 'application/json');

        // Parse JSON response
        $data = json_decode((string)$response->getBody(), true);

        // Assert response structure
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('status', $data);

        // Assert response values
        $this->assertSame($data['message'], 'Welcome to the API root');
        $this->assertSame($data['status'], 'success');
    }
}