<?php

namespace Jmf\SimpleCache\Storage;

readonly class CacheEntry
{
    public function __construct(
        private string $key,
        private mixed $content,
        private int $creationTimestamp,
        private ?int $expirationTimestamp,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function getCreationTimestamp(): int
    {
        return $this->creationTimestamp;
    }

    public function getExpirationTimestamp(): ?int
    {
        return $this->expirationTimestamp;
    }
}
