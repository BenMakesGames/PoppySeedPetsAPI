<?php
namespace App\Service;

use App\Functions\CacheHelpers;
use Psr\Cache\CacheItemPoolInterface;

class CacheHelper
{
    public function __construct(private readonly CacheItemPoolInterface $cache)
    {
    }

    public function getOrCompute(string $cacheKey, \DateInterval $ttl, callable $computeMethod)
    {
        $item = $this->cache->getItem(CacheHelpers::getCacheItemName($cacheKey));

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