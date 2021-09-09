<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\HttpFoundation;

use League\OpenAPIValidation\PSR7\ValidatorBuilder as Psr7ValidatorBuilder;
use N1215\OpenApiValidation\Util\MakeHttpMessageFactory;
use N1215\OpenApiValidation\Util\OpenApiFilePath;
use N1215\OpenApiValidation\RequestValidationFailed;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestValidatorTest extends TestCase
{
    use MakeHttpMessageFactory;
    use OpenApiFilePath;

    public function testValidateSuccess(): void
    {
        $requestValidator = $this->makeRequestValidator();
        $request = Request::create('https://example.com/hello?name=Taro', 'GET');
        $requestValidator->validate($request);
        $this->assertTrue(true);
    }

    public function testValidateFailed(): void
    {
        $requestValidator = $this->makeRequestValidator();
        $request = Request::create('https://example.com/hello', 'GET');

        $this->expectException(RequestValidationFailed::class);

        $requestValidator->validate($request);
    }

    public function testValidateSuccessWhenDisabled(): void
    {
        $requestValidator = $this->makeRequestValidator();
        $request = Request::create('https://example.com/hello', 'GET');

        $requestValidator->enable(false);
        $requestValidator->validate($request);

        $this->assertTrue(true);
    }

    public function testValidateFailedWhenEnabled(): void
    {
        $requestValidator = $this->makeRequestValidator();
        $request = Request::create('https://example.com/hello', 'GET');

        $this->expectException(RequestValidationFailed::class);

        $requestValidator->enable(false);
        $requestValidator->enable(true);
        $requestValidator->validate($request);
    }

    private function makeRequestValidator(): RequestValidator
    {
        $psr7ServerRequestValidator = (new Psr7ValidatorBuilder())
            ->fromJsonFile($this->getJsonFilePath())
            ->getServerRequestValidator();
        return new RequestValidator(
            $this->makeHttpMessageFactory(),
            $psr7ServerRequestValidator
        );
    }
}
