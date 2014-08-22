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
    private $currencyFrom;

    /** @var string */
    private $currencyTo;

    /** @var string */
    private $exchangeRate;

    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    public function setOffice($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function setCurrencyFrom($currencyFrom)
    {
        $this->currencyFrom = $currencyFrom;

        return $this;
    }

    public function setCurrencyTo($currencyTo)
    {
        $this->currencyTo = $currencyTo;

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

    public function getCurrencyFrom()
    {
        return $this->currencyFrom;
    }

    public function getCurrencyTo()
    {
        return $this->currencyTo;
    }

    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }
}