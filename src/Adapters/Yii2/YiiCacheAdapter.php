<?php
namespace LanguageDetector\Adapters\Yii2;
/**
 * YiiCacheAdapter.php
 *
 * This file is part of LanguageDetector package.
 *
 * (c) Oleksandr Nosov <alex@4n.com.ua>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license MIT
 * @package LanguageDetector\Adapters\Yii2
 * @author  Oleksandr Nosov <alex@4n.com.ua>
 */
use Psr\SimpleCache\CacheInterface;
use yii\caching\CacheInterface as YiiCacheInterface;

class YiiCacheAdapter implements CacheInterface
{
    /**
     * YiiCacheAdapter constructor.
     * @param YiiCacheInterface $cache
     */
    public function __construct(private YiiCacheInterface $cache) {}

    /**
     * @inheritDoc
     */
    public function get($key, $default = null): mixed
    {
        $value = $this->cache->get($key);
        return $value !== false ? $value : $default;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null): bool
    {
        return $this->cache->set($key, $value, is_int($ttl) ? $ttl : null);
    }

    /**
     * @inheritDoc
     */
    public function delete($key): bool
    {
        return $this->cache->delete($key);
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        return $this->cache->flush();
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
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function has($key): bool
    {
        return $this->cache->exists($key);
    }
}
