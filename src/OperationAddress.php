<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation;

class OperationAddress
{
    protected string $path;

    protected string $method;

    public function __construct(string $path, string $method)
    {
        $this->path = $path;
        $this->method = $method;
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
