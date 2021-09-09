<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\HttpFoundation;

use League\OpenAPIValidation\PSR7\ValidatorBuilder as Psr7ValidatorBuilder;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use N1215\OpenApiValidation\Cache\Psr6CachePool;

class ValidatorBuilder
{
    protected Psr7ValidatorBuilder $psr7ValidatorBuilder;

    protected HttpMessageFactoryInterface $httpMessageFactory;

    public function __construct(HttpMessageFactoryInterface $httpMessageFactory)
    {
        $this->psr7ValidatorBuilder = new Psr7ValidatorBuilder();
        $this->httpMessageFactory = $httpMessageFactory;
    }

    /**
     * @param CacheInterface $psr16Cache
     * @param int|null $ttl
     * @return $this
     */
    public function setSimpleCache(CacheInterface $psr16Cache, ?int $ttl = null): self
    {
        return $this->setCache(new Psr6CachePool($psr16Cache), $ttl);
    }

    /**
     * @param CacheItemPoolInterface $psr6Cache
     * @param int|null $ttl
     * @return $this
     */
    public function setCache(CacheItemPoolInterface $psr6Cache, ?int $ttl = null): self
    {
        $this->psr7ValidatorBuilder->setCache(
            $psr6Cache,
            $ttl
        );
        return $this;
    }

    /**
     * @param string $cacheKey
     * @return $this
     */
    public function overrideCacheKey(string $cacheKey): self
    {
        $this->psr7ValidatorBuilder->overrideCacheKey($cacheKey);
        return $this;
    }

    /**
     * @param string $yaml
     * @return $this
     */
    public function fromYaml(string $yaml): self
    {
        $this->psr7ValidatorBuilder->fromYaml($yaml);
        return $this;
    }

    /**
     * @param string $yamlFile
     * @return $this
     */
    public function fromYamlFile(string $yamlFile): self
    {
        $this->psr7ValidatorBuilder->fromYamlFile($yamlFile);
        return $this;
    }

    /**
     * @param string $json
     * @return $this
     */
    public function fromJson(string $json): self
    {
        $this->psr7ValidatorBuilder->fromJson($json);
        return $this;
    }

    /**
     * @param string $jsonFile
     * @return $this
     */
    public function fromJsonFile(string $jsonFile): self
    {
        $this->psr7ValidatorBuilder->fromJsonFile($jsonFile);
        return $this;
    }

    public function getValidators(): Validators
    {
        $serverRequestValidator = $this->psr7ValidatorBuilder->getServerRequestValidator();
        return new Validators(
            $serverRequestValidator->getSchema(),
            new RequestValidator(
                $this->httpMessageFactory,
                $serverRequestValidator,
            ),
            new ResponseValidator(
                $this->httpMessageFactory,
                $this->psr7ValidatorBuilder->getResponseValidator(),
            )
        );
    }
}
