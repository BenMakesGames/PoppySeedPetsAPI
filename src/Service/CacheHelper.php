<?php
namespace App\Service;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class CacheHelper
{
    private $cache;

    public function __construct(AdapterInterface $cache)
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