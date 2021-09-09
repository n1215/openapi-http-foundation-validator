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
    protected string $key;

    /**
     * @var mixed
     */
    protected $value;

    protected ?DateTimeInterface $expiration;

    /**
     * @param string $key
     * @param mixed $value
     * @param DateTimeInterface|null $expiration
     */
    public function __construct(string $key, $value, ?DateTimeInterface $expiration = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->expiration = $expiration;
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
    public function get()
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
    public function set($value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expiresAt($expiration): self
    {
        $this->expiration = $expiration;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expiresAfter($time): self
    {
        if (is_int($time)) {
            if ($time <= 0) {
                throw new InvalidArgumentException('$time should be a positive integer, DateInterval, or null.');
            }
            $this->expiration = (new DateTimeImmutable())->add(DateInterval::createFromDateString("{$time} seconds"));
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
