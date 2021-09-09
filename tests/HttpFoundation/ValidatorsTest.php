<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\HttpFoundation;

use League\OpenAPIValidation\PSR7\ValidatorBuilder as Psr7ValidationBuilder;
use N1215\OpenApiValidation\Util\MakeHttpMessageFactory;
use N1215\OpenApiValidation\Util\OpenApiFilePath;
use PHPUnit\Framework\TestCase;

class ValidatorsTest extends TestCase
{
    use OpenApiFilePath;
    use MakeHttpMessageFactory;

    public function testGetRequestValidators(): void
    {
        $requestValidator = $this->makeRequestValidator();
        $validators = new Validators(
            $this->getPsr7ValidatorBuilder()->getServerRequestValidator()->getSchema(),
            $requestValidator,
            $this->makeResponseValidator()
        );

        $this->assertSame($requestValidator, $validators->getRequestValidator());
        $this->assertSame($requestValidator, $validators->getRequestValidator());
    }

    public function testGetResponseValidators(): void
    {
        $responseValidator = $this->makeResponseValidator();
        $validators = new Validators(
            $this->getPsr7ValidatorBuilder()->getServerRequestValidator()->getSchema(),
            $this->makeRequestValidator(),
            $responseValidator
        );

        $this->assertSame($responseValidator, $validators->getResponseValidator());
        $this->assertSame($responseValidator, $validators->getResponseValidator());
    }

    private function makeRequestValidator(): RequestValidator
    {
        return new RequestValidator(
            $this->makeHttpMessageFactory(),
            $this->getPsr7ValidatorBuilder()->getServerRequestValidator()
        );
    }

    private function makeResponseValidator(): ResponseValidator
    {
        $responseValidator = $this->getPsr7ValidatorBuilder()->getResponseValidator();
        return new ResponseValidator(
            $this->makeHttpMessageFactory(),
            $responseValidator
        );
    }

    private function getPsr7ValidatorBuilder(): Psr7ValidationBuilder
    {
        return (new Psr7ValidationBuilder())->fromJsonFile($this->getJsonFilePath());
    }
}
