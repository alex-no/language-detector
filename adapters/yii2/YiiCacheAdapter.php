<?php
namespace LanguageDetector\Adapters\Yii2;

use LanguageDetector\Core\Contracts\CacheInterface as CoreCacheInterface;
use yii\caching\CacheInterface as YiiCacheInterface;

class YiiCacheAdapter implements CoreCacheInterface
{
    private YiiCacheInterface $cache;

    public function __construct(YiiCacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function get($key)
    {
        return $this->cache->get($key);
    }

    public function set($key, $value, $ttl = null)
    {
        $this->cache->set($key, $value, $ttl);
    }
}
