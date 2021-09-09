<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\Util;

use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

trait MakeHttpMessageFactory
{
    protected function makeHttpMessageFactory(): HttpMessageFactoryInterface
    {
        $psr17Factory = new Psr17Factory();
        return new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
    }
}
