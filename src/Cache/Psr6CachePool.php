<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\Cache;

use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException as PSR16InvalidArgumentException;

class Psr6CachePool implements CacheItemPoolInterface
{
    public function __construct(
        private readonly CacheInterface $cache
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getItem($key): CacheItemInterface
    {
        try {
            $value = $this->cache->get($key);
        } catch (PSR16InvalidArgumentException $e) {
            throw new Psr6InvalidArgumentException('failed to get item. key=' . $key, 0, $e);
        }
        return new Psr6CacheItem($key, $value);
    }

    /**
     * @param string[] $keys
     * @return array<CacheItemInterface>
     */
    public function getItems(array $keys = []): array
    {
        return array_map([$this, 'getItem'], $keys);
    }

    /**
     * @inheritDoc
     */
    public function hasItem($key): bool
    {
        try {
            return $this->cache->has($key);
        } catch (PSR16InvalidArgumentException $e) {
            throw new Psr6InvalidArgumentException('failed to check item\'s presence. key=' . $key, 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        return $this->cache->clear();
    }

    /**
     * @inheritDoc
     */
    public function deleteItem($key): bool
    {
        try {
            return $this->cache->delete($key);
        } catch (PSR16InvalidArgumentException $e) {
            throw new Psr6InvalidArgumentException('failed to delete item. key=' . $key, 0, $e);
        }
    }

    /**
     * @param string[] $keys
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException&\Throwable
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->deleteItem($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item): bool
    {
        if (!$item instanceof Psr6CacheItem) {
            throw new InvalidArgumentException('please use' . Psr6CacheItem::class);
        }

        $expiration = $item->getExpiration();

        if ($expiration === null) {
            $ttl = null;
        } else {
            $diffSeconds = $expiration->getTimestamp() - time();
            $ttl = max($diffSeconds, 0);
            assert(is_int($ttl));
        }

        try {
            $this->cache->set($item->getKey(), $item->get(), $ttl);
            return true;
        } catch (PSR16InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        return true;
    }
}
