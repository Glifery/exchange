<?php

namespace Exchange\CacheBundle\Service\Provider;

interface CacheProviderInterface
{
    public function provide();
    public function getLastError();
}