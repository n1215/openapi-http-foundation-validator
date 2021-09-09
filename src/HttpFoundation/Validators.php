<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\HttpFoundation;

use cebe\openapi\spec\OpenApi;

class Validators
{
    private OpenApi $schema;

    private RequestValidator $requestValidator;

    private ResponseValidator $responseValidator;

    public function __construct(
        OpenApi $schema,
        RequestValidator $requestValidator,
        ResponseValidator $responseValidator
    ) {
        $this->schema = $schema;
        $this->requestValidator = $requestValidator;
        $this->responseValidator = $responseValidator;
    }

    public function getSchema(): OpenApi
    {
        return $this->schema;
    }

    public function getRequestValidator(): RequestValidatorInterface
    {
        return $this->requestValidator;
    }

    public function getResponseValidator(): ResponseValidatorInterface
    {
        return $this->responseValidator;
    }
}
