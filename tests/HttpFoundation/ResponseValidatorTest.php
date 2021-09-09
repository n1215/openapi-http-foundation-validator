<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\HttpFoundation;

use League\OpenAPIValidation\PSR7\ValidatorBuilder as Psr7ValidatorBuilder;
use N1215\OpenApiValidation\Util\MakeHttpMessageFactory;
use N1215\OpenApiValidation\Util\OpenApiFilePath;
use N1215\OpenApiValidation\OperationAddress;
use N1215\OpenApiValidation\ResponseValidationFailed;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseValidatorTest extends TestCase
{
    use MakeHttpMessageFactory;
    use OpenApiFilePath;

    public function testValidateSuccess(): void
    {
        $responseValidator = $this->makeResponseValidator();
        $operationAddress = new OperationAddress('/hello', 'GET');
        $response = new JsonResponse(['message' => 'Hello, Taro']);

        $responseValidator->validate($operationAddress, $response);
        $this->assertTrue(true);
    }

    public function testValidateFailed(): void
    {
        $responseValidator = $this->makeResponseValidator();
        $operationAddress = new OperationAddress('/hello', 'GET');
        $response = new JsonResponse(['message' => 1]);

        $this->expectException(ResponseValidationFailed::class);

        $responseValidator->validate($operationAddress, $response);
    }

    public function testValidateSuccessWhenDisabled(): void
    {
        $responseValidator = $this->makeResponseValidator();
        $operationAddress = new OperationAddress('/hello', 'GET');
        $response = new JsonResponse(['message' => 1]);

        $responseValidator->enable(false);
        $responseValidator->validate($operationAddress, $response);

        $this->assertTrue(true);
    }

    public function testValidateFailedWhenEnabled(): void
    {
        $responseValidator = $this->makeResponseValidator();
        $operationAddress = new OperationAddress('/hello', 'GET');
        $response = new JsonResponse(['message' => 1]);

        $this->expectException(ResponseValidationFailed::class);

        $responseValidator->enable(false);
        $responseValidator->enable(true);
        $responseValidator->validate($operationAddress, $response);
    }

    private function makeResponseValidator(): ResponseValidator
    {
        $psr7ResponseValidator = (new Psr7ValidatorBuilder())
            ->fromJsonFile($this->getJsonFilePath())
            ->getResponseValidator();
        return new ResponseValidator(
            $this->makeHttpMessageFactory(),
            $psr7ResponseValidator
        );
    }
}
