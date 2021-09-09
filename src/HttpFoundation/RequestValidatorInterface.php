<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\HttpFoundation;

use N1215\OpenApiValidation\RequestValidationFailed;
use Symfony\Component\HttpFoundation\Request;

interface RequestValidatorInterface
{
    /**
     * @throws RequestValidationFailed
     */
    public function validate(Request $httpFoundationRequest): void;

    /**
     * @param bool $isEnabled
     */
    public function enable(bool $isEnabled): void;
}
