<?php

namespace Jmf\SimpleCache;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Jmf\SimpleCache\Exception\InvalidArgumentException;
use Jmf\SimpleCache\Storage\CacheEntries;
use Jmf\SimpleCache\Storage\CacheEntry;
use Jmf\SimpleCache\Storage\StorageInterface;
use Override;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;

/**
 * Allows to store any kind of content (text, html, object, etc) into cache.
 */
readonly class Cache implements CacheInterface
{
    public function __construct(
        private StorageInterface $storage,
        private ClockInterface $clock,
        private LoggerInterface $logger,
    ) {
    }

    #[Override]
    public function get(
        string $key,
        mixed $default = null,
    ): mixed {
        $entry = null;

        try {
            $entry = $this->storage->get($key);
        } catch (Throwable $e) {
            $this->logger->critical(
                "Failed retrieving cache content for key '{$key}'.",
                [
                    'exception' => $e,
                ]
            );
        }

        if (null === $entry) {
            return $default;
        }

        if (null !== $entry->getExpirationTimestamp()) {
            $nowTimestamp = $this->clock->now()->getTimestamp();

            if ($nowTimestamp > $entry->getExpirationTimestamp()) {
                return $default;
            }
        }

        return $entry->getContent();
    }

    #[Override]
    public function set(
        string $key,
        mixed $value,
        DateInterval | int | null $ttl = null,
    ): bool {
        $creationDateTime = $this->clock->now();

        $entry = new CacheEntry(
            $key,
            $value,
            $creationDateTime->getTimestamp(),
            $this->getExpirationTimestamp($creationDateTime, $ttl),
        );

        try {
            $this->storage->set($entry);
        } catch (Throwable $e) {
            $this->logger->critical(
                "Failed setting cache content for key '{$key}'.",
                [
                    'exception' => $e,
                ],
            );

            return false;
        }

        return true;
    }

    #[Override]
    public function delete(string $key): bool
    {
        try {
            $this->storage->delete($key);
        } catch (Throwable $e) {
            $this->logger->critical(
                "Failed deleting cache entry '{$key}'.",
                [
                    'exception' => $e,
                ]
            );

            return false;
        }

        return true;
    }

    #[Override]
    public function clear(): bool
    {
        try {
            $this->storage->clear();
        } catch (Throwable $e) {
            $this->logger->critical(
                "Failed clearing cache.",
                [
                    'exception' => $e,
                ]
            );

            return false;
        }

        return true;
    }

    #[Override]
    public function getMultiple(
        iterable $keys,
        mixed $default = null,
    ): iterable {
        $cacheEntries = [];

        try {
            $cacheEntries = $this->storage->getMultiple($keys);
        } catch (Throwable $e) {
            $this->logger->critical(
                "Failed retrieving cache content.",
                [
                    'exception' => $e,
                ]
            );
        }

        $indexed = [];
        foreach ($cacheEntries as $cacheEntry) {
            $indexed[$cacheEntry->getKey()] = $cacheEntry;
        }

        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $indexed[$key] ?? $default;
        }

        return $results;
    }

    #[Override]
    public function setMultiple(
        iterable $values,
        DateInterval | int | null $ttl = null,
    ): bool {
        $creationDateTime = $this->clock->now();

        try {
            $this->storage->setMultiple(
                new CacheEntries(
                    (array) $values,
                    $creationDateTime->getTimestamp(),
                    $this->getExpirationTimestamp($creationDateTime, $ttl),
                )
            );
        } catch (Throwable $e) {
            $this->logger->critical(
                'Failed adding content to cache.',
                [
                    'exception' => $e,
                ]
            );

            return false;
        }

        return true;
    }

    #[Override]
    public function deleteMultiple(iterable $keys): bool
    {
        try {
            $this->storage->deleteMultiple($keys);
        } catch (Throwable $e) {
            $this->logger->critical(
                "Failed deleting cache content.",
                [
                    'exception' => $e,
                ]
            );

            return false;
        }

        return true;
    }

    #[Override]
    public function has(string $key): bool
    {
        try {
            return $this->storage->has($key);
        } catch (Throwable $e) {
            $this->logger->critical(
                "Failed determining cache entry existence.",
                [
                    'exception' => $e,
                ]
            );

        }

        return false;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getExpirationTimestamp(
        DateTimeInterface $creationDateTime,
        DateInterval | int | null $ttl = null,
    ): ?int {
        if (null === $ttl) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            if (0 !== $ttl->invert) {
                throw new InvalidArgumentException('Invalid cache expiration interval.');
            }

            return DateTime::createFromInterface($creationDateTime)->add($ttl)->getTimestamp();
        }

        if ($ttl < 1) {
            throw new InvalidArgumentException('Invalid cache TTL.');
        }

        return $creationDateTime->getTimestamp() + $ttl;
    }
}
