<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\HttpFoundation;

use League\OpenAPIValidation\PSR7\ServerRequestValidator as Psr7ServerRequestValidator;
use N1215\OpenApiValidation\RequestValidationFailed;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;

final class RequestValidator implements RequestValidatorInterface
{
    private bool $isEnabled = true;

    public function __construct(
        private readonly HttpMessageFactoryInterface $psrHttpFactory,
        private readonly Psr7ServerRequestValidator $psr7ServerRequestValidator
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate(Request $httpFoundationRequest): void
    {
        if (!$this->isEnabled) {
            return;
        }

        $request = $this->psrHttpFactory->createRequest($httpFoundationRequest);

        try {
            $this->psr7ServerRequestValidator->validate($request);
        } catch (ValidationFailed $e) {
            throw new RequestValidationFailed('failed to validate request', 0, $e);
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
