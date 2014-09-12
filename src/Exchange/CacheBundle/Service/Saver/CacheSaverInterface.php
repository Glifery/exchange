<?php

namespace Exchange\CacheBundle\Service\Saver;

use Exchange\CacheBundle\Model\CacheData;

interface CacheSaverInterface
{
    public function save(CacheData $cacheDate);
    public function getLastError();
}