# openapi-http-foundation-validator
OpenAPI(v3) Validators for Symfony http-foundation, using `league/openapi-psr7-validator` and `symfony/psr-http-message-bridge`.

## Requirements
- PHP >= 7.4

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

## Usage for Laravel
see https://github.com/openapi-laravel-validator
