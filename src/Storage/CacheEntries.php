<?php

namespace Jmf\SimpleCache\Storage;

readonly class CacheEntries
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(
        private array $values,
        private int $creationTimestamp,
        private ?int $expirationTimestamp,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getValues(): array
    {
        return $this->values;
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
