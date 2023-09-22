<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation;

class OperationAddress
{
    public function __construct(
        protected readonly string $path,
        protected string $method
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function method(): string
    {
        return strtolower($this->method);
    }
}
