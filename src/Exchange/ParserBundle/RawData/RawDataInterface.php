<?php

namespace Exchange\ParserBundle\RawData;

interface RawDataInterface
{
    public function setBank($bank);
    public function setOffice($office);
    public function setAddress($address);
    public function setDirection($direction);
//    public function setCurrencyFrom($currencyFrom);
//    public function setCurrencyTo($currencyTo);
    public function setExchangeRate($exchangeRate);

    public function getBank();
    public function getOffice();
    public function getAddress();
    public function getDirection();
//    public function getCurrencyFrom();
//    public function getCurrencyTo();
    public function getExchangeRate();
}