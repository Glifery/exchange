<?php

namespace Exchange\CacheBundle\Service;

use Exchange\CacheBundle\Model\CacheData;
use Exchange\CacheBundle\Service\Provider\CacheProviderInterface;
use Exchange\CacheBundle\Service\Saver\CacheSaverInterface;
use Exchange\CacheBundle\Service\Transformer\CacheTransformerInterface;

class CacheManager
{
    /** @var CacheProviderInterface */
    private $provider;

    /** @var CacheTransformerInterface */
    private $transformer;

    /** @var CacheSaverInterface */
    private $saver;

    public function __construct(CacheProviderInterface $provider, CacheTransformerInterface $transformer, CacheSaverInterface $saver)
    {
        $this->provider = $provider;
        $this->transformer = $transformer;
        $this->saver = $saver;
    }

    public function regenerateCache()
    {
        $cacheData = $this->provider->provide();
        if (!($cacheData instanceof CacheData)) {
            throw new \LogicException('Can\'t get CacheData from CacheProvider: '.$this->provider->getLastError());
        }

        if (!$this->transformer->transform($cacheData)) {
            throw new \LogicException('Can\'t transform CacheData in CacheTransformer: '.$this->transformer->getLastError());
        }

        if (!$this->saver->save($cacheData)) {
            throw new \LogicException('Can\'t save CacheData in CacheSaver: '.$this->saver->getLastError());
        }

        return true;
    }
}