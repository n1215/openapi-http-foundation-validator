<?php

declare(strict_types=1);

namespace N1215\OpenApiValidation\Cache;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;

class Psr6CacheItem implements CacheItemInterface
{
    /**
     * @param string $key
     * @param mixed $value
     * @param DateTimeInterface|null $expiration
     */
    public function __construct(
        private readonly string $key,
        private mixed $value,
        private ?DateTimeInterface $expiration = null
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function get(): mixed
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function isHit(): bool
    {
        return $this->value !== null;
    }

    /**
     * @inheritDoc
     */
    public function set($value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expiresAt($expiration): static
    {
        $this->expiration = $expiration;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expiresAfter($time): static
    {
        if (is_int($time)) {
            if ($time <= 0) {
                throw new InvalidArgumentException('$time should be a positive integer, DateInterval, or null.');
            }
            $interval = DateInterval::createFromDateString("$time seconds");
            if ($interval === false) {
                throw new InvalidArgumentException('Failed to parse DateInterval.');
            }
            $this->expiration = (new DateTimeImmutable())->add($interval);
        } elseif ($time instanceof DateInterval) {
            $this->expiration = (new DateTimeImmutable())->add($time);
        } else {
            $this->expiration = null;
        }

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getExpiration(): ?DateTimeInterface
    {
        return $this->expiration;
    }
}
