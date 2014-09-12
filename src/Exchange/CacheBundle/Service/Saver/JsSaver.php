<?php

namespace Exchange\CacheBundle\Service\Saver;

use Exchange\CacheBundle\Model\CacheData;

class JsSaver implements CacheSaverInterface
{
    const DIR_PATH = 'data';
    const FILE_NAME = 'exchange.js';

    /** @var string */
    private $lastError = '';

    public function save(CacheData $cacheDate)
    {
        $fullPath = __DIR__.'/../../../../../web/'.self::DIR_PATH;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777);
        }

        if (file_exists($fullPath.'/'.self::FILE_NAME)) {
            if (!unlink($fullPath.'/'.self::FILE_NAME)) {
                $this->setLastError('delete file error');

                return false;
            }
        }

        $data = $cacheDate->getData();

        if (!file_put_contents($fullPath.'/'.self::FILE_NAME, $data)) {
            $this->setLastError('write file error');

            return false;
        }

        return true;
    }

    /**
     * @param $message
     */
    private function setLastError($message)
    {
        $this->lastError = $message;
    }

    /**
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }
}