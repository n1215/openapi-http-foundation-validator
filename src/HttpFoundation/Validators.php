<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\HttpFoundation;

use cebe\openapi\spec\OpenApi;

class Validators
{
    public function __construct(
        private readonly OpenApi $schema,
        private readonly RequestValidator $requestValidator,
        private readonly ResponseValidator $responseValidator
    ) {
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
