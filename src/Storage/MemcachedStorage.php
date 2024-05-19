<?php

namespace Jmf\SimpleCache\Storage;

use Jmf\SimpleCache\Exception\CacheException;
use Memcached;
use Override;

readonly class MemcachedStorage implements StorageInterface
{
    private const string HOST_DEFAULT = '127.0.0.1';
    private const int    PORT_DEFAULT = 11211;

    /**
     * @throws CacheException
     */
    public static function createFromCredentials(
        string $host = self::HOST_DEFAULT,
        int $port = self::PORT_DEFAULT,
        ?string $keyPrefix = null,
    ): self {
        self::checkExtension();

        $memcached = new Memcached();
        $memcached->addServer($host, $port);

        if (null !== $keyPrefix) {
            $memcached->setOption(Memcached::OPT_PREFIX_KEY, $keyPrefix);
        }

        return new self($memcached);
    }

    public static function createFromConnection(Memcached $connection): self
    {
        return new self($connection);
    }

    /**
     * @throws CacheException
     */
    private function __construct(
        private Memcached $connection,
    ) {
        self::checkExtension();
    }

    #[Override]
    public function set(CacheEntry $cacheEntry): void
    {
        $expirationTimestamp = $this->getExpirationTimestamp($cacheEntry->getExpirationTimestamp());

        if (!$this->connection->set($cacheEntry->getKey(), $cacheEntry, $expirationTimestamp)) {
            $code    = $this->connection->getResultCode();
            $message = $this->connection->getResultMessage();

            $this->failure("Failed to store cache entry into Memcached server: #{$code} - {$message}");
        }
    }

    #[Override]
    public function setMultiple(CacheEntries $cacheEntries): void
    {
        $expirationTimestamp = $this->getExpirationTimestamp($cacheEntries->getExpirationTimestamp());

        if (!$this->connection->setMulti($cacheEntries->getValues(), $expirationTimestamp)) {
            $code    = $this->connection->getResultCode();
            $message = $this->connection->getResultMessage();

            $this->failure("Failed to store cache entry into Memcached server: #{$code} - {$message}");
        }
    }

    private function getExpirationTimestamp(?int $timestamp): int
    {
        return $timestamp ?? 0;
    }

    #[Override]
    public function get(string $key): ?CacheEntry
    {
        $result = $this->connection->get($key);

        if (false === $result) {
            return null;
        }

        if ($result instanceof CacheEntry) {
            return $result;
        }

        throw new CacheException('Unexpected cache data type retrieved.');
    }

    #[Override]
    public function getMultiple(iterable $keys): iterable
    {
        $result = $this->connection->getMulti((array) $keys);

        if (!is_iterable($result)) {
            throw new CacheException('Failed to retrieve data from cache.');
        }

        $cacheEntries = [];

        foreach ($result as $content) {
            if ($content instanceof CacheEntry) {
                $cacheEntries[] = $content;

                continue;
            }

            throw new CacheException('Unexpected cache data type retrieved.');
        }

        return $cacheEntries;
    }

    #[Override]
    public function has(string $key): bool
    {
        return (null !== $this->get($key));
    }

    #[Override]
    public function delete(string $key): void
    {
        static $expectedResultCodes = [
            Memcached::RES_SUCCESS,
            Memcached::RES_NOTFOUND,
        ];

        $this->connection->delete($key);

        if (!in_array($this->connection->getResultCode(), $expectedResultCodes, true)) {
            $this->failure('Failed to delete cache entry from Memcached server.');
        }
    }

    #[Override]
    public function deleteMultiple(iterable $keys): void
    {
        $results = $this->connection->deleteMulti((array) $keys);

        static $expectedResultCodes = [
            true,
            Memcached::RES_NOTFOUND,
        ];

        foreach ($results as $result) {
            if (!in_array($result, $expectedResultCodes, true)) {
                $this->failure('Failed to delete cache entries from Memcached server.');
            }
        }
    }

    #[Override]
    public function clear(): void
    {
        if (!$this->connection->flush()) {
            $this->failure('Failed to flush Memcached content.');
        }
    }

    /**
     * @throws CacheException
     */
    private static function checkExtension(): void
    {
        if (!extension_loaded('memcached')) {
            throw new CacheException('Memcached extension is not loaded.');
        }
    }

    /**
     * @throws CacheException
     */
    private function failure(string $message): void
    {
        $resultCode    = $this->connection->getResultCode();
        $resultMessage = $this->connection->getResultMessage();

        throw new CacheException("{$message} << #{$resultCode} {$resultMessage}");
    }
}
