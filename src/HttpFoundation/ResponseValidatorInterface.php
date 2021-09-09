<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\HttpFoundation;

use N1215\OpenApiValidation\OperationAddress;
use N1215\OpenApiValidation\ResponseValidationFailed;
use Symfony\Component\HttpFoundation\Response;

interface ResponseValidatorInterface
{
    /**
     * @throws ResponseValidationFailed
     */
    public function validate(OperationAddress $operationAddress, Response $httpFoundationResponse): void;

    /**
     * @param bool $isEnabled
     */
    public function enable(bool $isEnabled): void;
}
