# openapi-http-foundation-validator
OpenAPI(v3) Validators for Symfony http-foundation, using `league/openapi-psr7-validator` and `symfony/psr-http-message-bridge`.

## Requirements
- PHP >= 8.0

## Installation

```shell
composer require n1215/openapi-http-foundation-validator
```

## Usage

### 1. install PSR-17 HTTP Factory implementation.
- You can use any implementation of PSR-17 HTTP Factory.
  - ex. `nyholm/psr7`

```shell
composer require nyholm/psr7
```

### 2. create http message factory

```php
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
/** @var \Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface $httpMessageFactory */
$httpMessageFactory = new \Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory(
    serverRequestFactory: $psr17Factory,
    streamFactory: $psr17Factory,
    uploadedFileFactory: $psr17Factory,
    responseFactory: $psr17Factory
);
```

### 3. create validator builder

- A builder can be created from YAML file, YAML string, JSON file, or JSON string.
- You can use PSR-16 simple cache instead of PSR-6 Cache.

#### example1
```php
/** @var \N1215\OpenApiValidation\HttpFoundation\ValidatorBuilder $validatorBuilder */
$validatorBuilder = (new \N1215\OpenApiValidation\HttpFoundation\ValidatorBuilder($psr17Factory))
    ->fromYamlFile('/path/to/openapi.yaml')
    ->setCache(new YourPsr6Cache(), 86400);
```


#### example2
```php
/** @var \N1215\OpenApiValidation\HttpFoundation\ValidatorBuilder $validatorBuilder */
$validatorBuilder = (new \N1215\OpenApiValidation\HttpFoundation\ValidatorBuilder($psr17Factory))
    ->fromJsonFile('/path/to/openapi.json')
    ->setSimpleCache(new YourPsr16Cache(), 3600);
```

### 4. get validators from builder

```php
/** @var \N1215\OpenApiValidation\HttpFoundation\Validators $validators */
$validators = $validatorBuilder->getValidators();
```

### 5. validate request

```php
/** @var \Symfony\Component\HttpFoundation\Request $request */
/** @var \N1215\OpenApiValidation\HttpFoundation\RequestValidatorInterface $requestValidator */
$requestValidator = $validators->getRequestValidator();
$responseValidator->validate($request);
```

### 6. validate response

```php
/** @var \Symfony\Component\HttpFoundation\Response $response */
/** @var \N1215\OpenApiValidation\HttpFoundation\ResponseValidatorInterface $responseValidator */
$responseValidator = $validators->getResponseValidator();
$responseValidator->validate(
    new \N1215\OpenApiValidation\OperationAddress('/path', 'GET'),
    $response
);
```

## Usage for Laravel HTTP testing

### 1. create a OpenAPI definition file
./tests/Utils/openapi.yaml

```yaml
openapi: 3.0.1
info:
  description: test
  version: 1.0.0
  title: Test API
servers:
  - url: https://example.com
paths:
  /hello:
    get:
      summary: hello
      tags:
        - hello
      description: say hello
      operationId: hello
      parameters:
        - name: name
          in: query
          description: name
          schema:
            type: string
            description: name
            example: Taro
          required: true
      responses:
        200:
          description: success
          content:
            application/json:
              schema:
                type: object
                required:
                  - message
                properties:
                  message:
                    type: string
                    description: message
                    example: "Hello, Taro!"
        422:
          description: validation failed
          content:
            application/json:
              schema:
                type: object
                required:
                  - errors
                properties:
                  errors:
                    type: object
                    additionalProperties:
                      type: array
                      items:
                        type: string
```

### 2. create a trait

./tests/Utils/OpenApiAssertion.php
```php
<?php

declare(strict_types=1);

namespace Tests\Utils;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use N1215\OpenApiValidation\OperationAddress;
use Nyholm\Psr7\Factory\Psr17Factory;
use N1215\OpenApiValidation\HttpFoundation\Validators;
use N1215\OpenApiValidation\HttpFoundation\ValidatorBuilder;
use N1215\OpenApiValidation\RequestValidationFailed;
use N1215\OpenApiValidation\ResponseValidationFailed;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

trait OpenApiAssertion
{
    protected ?Validators $validators;

    protected function makeHttpFoundationValidators(): Validators
    {
        $psr17Factory = new Psr17Factory();
        $httpMessageFactory = new PsrHttpFactory(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory
        );
        return (new ValidatorBuilder($httpMessageFactory))
            ->fromYamlFile(__DIR__ . '/openapi.yaml')
            ->setSimpleCache(Cache::store(), 3600)
            ->getValidators();
    }

    protected function setOpenApiAssertion(string $method, string $path): void
    {
        if (!$this instanceof TestCase) {
            throw new \BadMethodCallException('trait '. OpenApiAssertion::class . ' should be used by a subclass of' . TestCase::class);
        }

        $this->validators = $this->makeHttpFoundationValidators();
        Event::listen(
            RequestHandled::class,
            function (RequestHandled $event) use ($method, $path) {
                try {
                    $this->validators->getRequestValidator()->validate($event->request);
                } catch (RequestValidationFailed $e) {
                    $this->fail((string) $e);
                }

                if ($event->response->getStatusCode() >= 500) {
                    return;
                }

                try {
                    $this->validators->getResponseValidator()->validate(
                        new OperationAddress($path, $method),
                        $event->response
                    );
                    $this->assertTrue(true);
                } catch (ResponseValidationFailed $e) {
                    $this->fail((string) $e);
                }
            }
        );
    }

    protected function disableRequestAssertion(): void
    {
        $this->validators->getRequestValidator()->enable(false);
    }

    protected function disableResponseAssertion(): void
    {
        $this->validators->getResponseValidator()->enable(false);
    }
}
```

### 3. use the trait in test class

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Utils\OpenApiAssertion;

class GetHelloTest extends TestCase
{
    use OpenApiAssertion;

    public function testSuccess(): void
    {
        $this->setOpenApiAssertion(
            'get',
            '/hello'
        );

        $response = $this->json(
            'get',
            '/hello?name=Taro'
        );

        $response->assertOk();
        $response->assertJson(['message' => 'Hello, Taro']);
    }

    public function testValidationFailed(): void
    {
        $this->setOpenApiAssertion(
            'get',
            '/hello'
        );

        // disable request validation for invalid request parameters
        $this->disableRequestAssertion();

        $response = $this->json(
            'get',
            '/hello'
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name' => 'The name field is required']);
    }
}
```

## Usage for Laravel Middleware

### 1. create a Middleware class

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use N1215\OpenApiValidation\HttpFoundation\Validators;
use N1215\OpenApiValidation\OperationAddress;
use N1215\OpenApiValidation\RequestValidationFailed;
use N1215\OpenApiValidation\ResponseValidationFailed;
use Symfony\Component\HttpFoundation\Response;

class ValidateWithOpenApi
{
    private string $basePath = '/';

    private Validators $validators;

    private ResponseFactory $responseFactory;

    public function __construct(Validators $validators, ResponseFactory $responseFactory)
    {
        $this->validators = $validators;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $this->validators->getRequestValidator()->validate($request);
        } catch (RequestValidationFailed $e) {
            return $this->responseFactory->json(
                [
                    'message' =>  $e->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $response = $next($request);
        assert($response instanceof Response);

        $pattern = '/^' . preg_quote($this->basePath, '/') . '/';
        $relativePath = '/' . preg_replace($pattern, '', $request->getPathInfo());
        try {
            $this->validators->getResponseValidator()->validate(
                new OperationAddress(
                    $relativePath,
                    $request->method()
                ),
                $response
            );
        } catch (ResponseValidationFailed $e) {
            return $this->responseFactory->json(
                [
                    'message' =>  $e->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $response;
    }
}
```

### 2. register to the service container

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\ValidateWithOpenApi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use N1215\OpenApiValidation\HttpFoundation\ValidatorBuilder;
use N1215\OpenApiValidation\HttpFoundation\Validators;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Validators::class, function () {
            $psr17Factory = new Psr17Factory();
            $httpMessageFactory = new PsrHttpFactory(
                $psr17Factory,
                $psr17Factory,
                $psr17Factory,
                $psr17Factory
            );
            return (new ValidatorBuilder($httpMessageFactory))
                ->fromYamlFile(__DIR__ . '/openapi.yaml')
                ->setSimpleCache(Cache::store(), 3600)
                ->getValidators();
        });
        $this->app->singleton(ValidateWithOpenApi::class);
    }
}
```
