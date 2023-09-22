<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\HttpFoundation;

use N1215\OpenApiValidation\OperationAddress;
use N1215\OpenApiValidation\ResponseValidationFailed;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ResponseValidator as Psr7ResponseValidator;
use League\OpenAPIValidation\PSR7\OperationAddress as Psr7OperationAddress;

final class ResponseValidator implements ResponseValidatorInterface
{
    private bool $isEnabled = true;

    public function __construct(
        private readonly HttpMessageFactoryInterface $psrHttpFactory,
        private readonly Psr7ResponseValidator $psr7ResponseValidator
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate(OperationAddress $operationAddress, Response $httpFoundationResponse): void
    {
        if (!$this->isEnabled) {
            return;
        }

        $leagueOperationAddress = new Psr7OperationAddress(
            $operationAddress->path(),
            $operationAddress->method()
        );

        $response = $this->psrHttpFactory->createResponse($httpFoundationResponse);

        try {
            $this->psr7ResponseValidator->validate(
                $leagueOperationAddress,
                $response
            );
        } catch (ValidationFailed $e) {
            throw new ResponseValidationFailed('failed to validate response', 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function enable(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }
}
