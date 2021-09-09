<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\Cache;

use Exception;
use Psr\Cache\InvalidArgumentException;

class Psr6InvalidArgumentException extends Exception implements InvalidArgumentException
{
}
