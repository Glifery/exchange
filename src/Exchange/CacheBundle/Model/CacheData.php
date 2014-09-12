<?php

namespace Exchange\CacheBundle\Model;

class CacheData
{
    /** @var mixed */
    private $data = null;

    /** @var bool */
    private $isEmpty = true;

    /**
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;
        $this->isEmpty = false;
    }

    /**
     * @return mixed
     * @throws \LogicException
     */
    public function getData()
    {
        if ($this->isEmpty()) {
            throw new \LogicException('Can\'t get data from cache: CacheData is empty');
        }
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->isEmpty;
    }
}