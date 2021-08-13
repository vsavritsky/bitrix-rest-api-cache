<?php

namespace BitrixRestApiCache\Cache;

use BitrixRestApi\Responser\Response\BaseSuccessResponse;
use CPHPCache;
use Symfony\Component\HttpFoundation\Request;

class PhpCache
{
    protected $startTime;
    protected $endTime;

    protected $cache;
    protected $cacheTime;
    protected $cacheId;
    protected $cachePath;
    /** @var Request */
    protected $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    public function init($cacheTime = CacheManager::DEFAULT_CACHE_TIME, $cacheId = null)
    {
        $this->startTime = new \DateTime();
        $this->cache = new CPHPCache();
        $this->cacheTime = $cacheTime;

        if ($cacheId) {
            $this->cachePath = $this->cacheId = $cacheId;
        } else {
            $this->cachePath = $this->cacheId = CacheManager::getCacheId($this->request);
        }

        CacheManager::start($this->cachePath);

        if ($this->isHit($this->cacheId)) {
            return $this->getResult($this->cacheId);
        }

        return false;
    }

    public function isHit($cacheId = null): bool
    {
        return $this->cache->InitCache($this->cacheTime, $cacheId ?? $this->cacheId, $this->cachePath);
    }

    public function getResult($cacheId = null)
    {
        $result = false;

        if ($this->cache->InitCache($this->cacheTime, $cacheId ?? $this->cacheId, $this->cachePath)) {
            $vars = $this->cache->GetVars();
            $result = $vars['result'];
        }

        return $result;
    }

    public function addTag($tag)
    {
        CacheManager::addTag($tag);
    }

    public function cache($result)
    {
        $this->cache->StartDataCache();
        CacheManager::end();

        $this->endTime = new \DateTime();

        if ($result instanceof BaseSuccessResponse) {
            $result->setCacheId($this->cacheId);
        }

        $this->cache->EndDataCache(['result' => $result]);
    }
}