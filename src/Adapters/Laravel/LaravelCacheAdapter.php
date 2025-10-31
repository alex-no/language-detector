<?php
namespace LanguageDetector\Adapters\Laravel;
/**
 * LaravelCacheAdapter.php
 * Adapter to implement PSR-16 (Psr\SimpleCache\CacheInterface) using Laravel's cache repository
 *
 * This file is part of LanguageDetector package.
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Adapters\Laravel
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class LaravelCacheAdapter implements PsrCacheInterface
{
    /**
     * @param CacheRepository $cache
     */
    public function __construct(
        private CacheRepository $cache
    ) {}

    /**
     * @inheritDoc
     */
    public function get($key, $default = null): mixed
    {
        try {
            $value = $this->cache->get($key);
            return $value === null ? $default : $value;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null): bool
    {
        try {
            if ($ttl === null) {
                return $this->cache->forever($key, $value);
            }

            // $ttl may be int (seconds) or DateInterval; Laravel expects seconds for put()
            if ($ttl instanceof \DateInterval) {
                // convert DateInterval to seconds (approx)
                $ttl = (int) (new \DateTimeImmutable())->add($ttl)->getTimestamp() - time();
            }

            $seconds = is_int($ttl) ? $ttl : (int)$ttl;
            return $this->cache->put($key, $value, $seconds);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete($key): bool
    {
        try {
            return $this->cache->forget($key);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        try {
            return $this->cache->flush();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null): bool
    {
        try {
            foreach ($values as $key => $value) {
                $this->set($key, $value, $ttl);
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys): bool
    {
        try {
            foreach ($keys as $key) {
                $this->delete($key);
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function has($key): bool
    {
        try {
            return $this->cache->has($key);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
