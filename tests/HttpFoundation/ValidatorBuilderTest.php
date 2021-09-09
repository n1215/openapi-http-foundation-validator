<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\HttpFoundation;

use League\OpenAPIValidation\PSR7\SchemaFactory\JsonFileFactory;
use N1215\OpenApiValidation\Util\MakeHttpMessageFactory;
use N1215\OpenApiValidation\Util\OpenApiFilePath;
use PHPUnit\Framework\TestCase;
use Yiisoft\Cache\ArrayCache;

class ValidatorBuilderTest extends TestCase
{
    use MakeHttpMessageFactory;
    use OpenApiFilePath;

    public function testFromJsonFile(): void
    {
        $validatorBuilder = $this->makeValidationBuilder();

        $validators = $validatorBuilder
            ->fromJsonFile($this->getJsonFilePath())
            ->getValidators();

        $this->assertInstanceOf(Validators::class, $validators);
    }

    public function testFromJson(): void
    {
        $validatorBuilder = $this->makeValidationBuilder();
        $json = file_get_contents($this->getJsonFilePath());
        assert($json !== false);

        $validators = $validatorBuilder
            ->fromJson($json)
            ->getValidators();

        $this->assertInstanceOf(Validators::class, $validators);
    }

    public function testFromYamlFile(): void
    {
        $validatorBuilder = $this->makeValidationBuilder();

        $validators = $validatorBuilder
            ->fromYamlFile($this->getYamlFilePath())
            ->getValidators();

        $this->assertInstanceOf(Validators::class, $validators);
    }

    public function testFromYaml(): void
    {
        $validatorBuilder = $this->makeValidationBuilder();
        $yaml = file_get_contents($this->getYamlFilePath());
        assert($yaml !== false);

        $validators = $validatorBuilder
            ->fromYaml($yaml)
            ->getValidators();

        $this->assertInstanceOf(Validators::class, $validators);
    }

    public function testSetSimpleCache(): void
    {
        $validatorBuilder = $this->makeValidationBuilder();
        $cache = new ArrayCache();
        $cacheKey = (new JsonFileFactory($this->getJsonFilePath()))->getCacheKey();

        $validators = $validatorBuilder
            ->fromJsonFile($this->getJsonFilePath())
            ->setSimpleCache($cache)
            ->getValidators();

        $this->assertTrue($cache->has($cacheKey));
        $cachedSchema = $cache->get($cacheKey);
        $this->assertEquals($validators->getSchema(), $cachedSchema);

        $validators2 = $validatorBuilder
            ->fromYamlFile($this->getJsonFilePath())
            ->setSimpleCache($cache)
            ->getValidators();

        $this->assertEquals($validators->getSchema(), $validators2->getSchema());
    }

    public function testSetSimpleCacheWithExpiration(): void
    {
        $validatorBuilder = $this->makeValidationBuilder();
        $cache = new ArrayCache();
        $cacheKey = (new JsonFileFactory($this->getJsonFilePath()))->getCacheKey();

        $validators = $validatorBuilder
            ->fromJsonFile($this->getJsonFilePath())
            ->setSimpleCache($cache, 1)
            ->getValidators();

        $this->assertTrue($cache->has($cacheKey));
        $cachedSchema = $cache->get($cacheKey);
        $this->assertEquals($validators->getSchema(), $cachedSchema);

        sleep(1);
        $this->assertFalse($cache->has($cacheKey));
    }

    public function testOverrideCacheKey(): void
    {
        $validatorBuilder = $this->makeValidationBuilder();
        $cache = new ArrayCache();
        $cacheKey = 'override_openapi_key';

        $validators = $validatorBuilder
            ->fromJsonFile($this->getJsonFilePath())
            ->setSimpleCache($cache)
            ->overrideCacheKey($cacheKey)
            ->getValidators();

        $this->assertTrue($cache->has($cacheKey));
        $cachedSchema = $cache->get($cacheKey);
        $this->assertEquals($validators->getSchema(), $cachedSchema);

        $validators2 = $validatorBuilder
            ->fromYamlFile($this->getJsonFilePath())
            ->setSimpleCache($cache)
            ->getValidators();

        $this->assertEquals($validators->getSchema(), $validators2->getSchema());
    }

    private function makeValidationBuilder(): ValidatorBuilder
    {
        return new ValidatorBuilder($this->makeHttpMessageFactory());
    }
}
