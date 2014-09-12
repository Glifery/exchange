<?php

namespace Exchange\CacheBundle\Service\Transformer;

use Exchange\CacheBundle\Model\CacheData;

interface CacheTransformerInterface
{
    public function transform(CacheData $cacheDate);
    public function getLastError();
}