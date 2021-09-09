<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\Util;

trait OpenApiFilePath
{
    protected function getJsonFilePath(): string
    {
        return __DIR__ . '/openapi.json';
    }

    protected function getYamlFilePath(): string
    {
        return __DIR__ . '/openapi.yaml';
    }
}
