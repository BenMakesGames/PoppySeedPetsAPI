<?php
namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;

class CacheHelper
{
    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getOrCompute(string $cacheKey, \DateInterval $ttl, callable $computeMethod)
    {
        $item = $this->cache->getItem($cacheKey);

        if($item->isHit())
            return $item->get();

        $data = $computeMethod();

        $item
            ->set($data)
            ->expiresAfter($ttl)
        ;

        $this->cache->save($item);

        return $data;
    }
}