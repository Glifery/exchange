<?php

namespace Exchange\ParserBundle\RawData;

class RawData implements RawDataInterface
{
    /** @var string */
    private $bank;

    /** @var string */
    private $office;

    /** @var string */
    private $address;

    /** @var string */
    private $direction;

    /** @var string */
    private $exchangeRate;

    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    public function setOffice($office)
    {
        $this->office = $office;

        return $this;
    }

    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    public function setExchangeRate($exchangeRate)
    {
        $this->exchangeRate = $exchangeRate;

        return $this;
    }

    public function getBank()
    {
        return $this->bank;
    }

    public function getOffice()
    {
        return $this->office;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }
}