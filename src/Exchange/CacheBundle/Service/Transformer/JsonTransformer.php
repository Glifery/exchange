<?php

namespace Exchange\CacheBundle\Service\Transformer;

use Exchange\CacheBundle\Model\CacheData;
use Exchange\DomainBundle\Entity\Bank;
use Exchange\DomainBundle\Entity\ExchangeRate;
use Exchange\DomainBundle\Entity\Office;

class JsonTransformer implements CacheTransformerInterface
{
    const DATA_WRAP = 'window.exchange = %data%;';

    private $lastError;

    /**
     * @param ExchangeRate $exchange
     * @return array
     */
    private function createExchangeArray(ExchangeRate $exchange)
    {
        $preparedExchange = array();
        $preparedExchange['id'] = $exchange->getId();
        $preparedExchange['direction'] = $exchange->getDirection();
        $preparedExchange['value'] = $exchange->getValue();

        return $preparedExchange;
    }

    /**
     * @param Office $office
     * @return array
     */
    private function createOfficeArray(Office $office)
    {
        $preparedOffice = array();
        $preparedOffice['id'] = $office->getId();
        $preparedOffice['title'] = $office->getTitle();
        $preparedOffice['address'] = $office->getAddress();
        $preparedOffice['longitude'] = $office->getLongitude();
        $preparedOffice['latitude'] = $office->getLatitude();

        return $preparedOffice;
    }

    /**
     * @param Bank $bank
     * @return array
     */
    private function createBankArray(Bank $bank)
    {
        $preparedBank = array();
        $preparedBank['id'] = $bank->getId();
        $preparedBank['title'] = $bank->getTitle();

        return $preparedBank;
    }

    /**
     * @param CacheData $cacheDate
     * @return bool
     */
    public function transform(CacheData $cacheDate)
    {
        $preparedData = array();
        $data = $cacheDate->getData();

        foreach ($data['exchanges'] as $exchange) {
            /** @var ExchangeRate $exchange */
            $preparedExchange = $this->createExchangeArray($exchange);

            /** @var Office $office */
            $office = $exchange->getOffice();
            $preparedOffice = $this->createOfficeArray($office);

            /** @var Bank $bank */
            $bank = $office->getBank();
            $preparedBank = $this->createBankArray($bank);

            $preparedOffice['bank'] = $preparedBank;
            $preparedExchange['office'] = $preparedOffice;
            $preparedData[] = $preparedExchange;
        }

        $fullPreparedData = array(
            'exchanges' => $preparedData,
            'statistic' => $data['statistic']
        );

        if (!$jsonData = json_encode($fullPreparedData, JSON_UNESCAPED_UNICODE)) {
            $this->setLastError('Transformed JSON is empty');

            return false;
        }

        $wrappedJson = str_replace('%data%', $jsonData, self::DATA_WRAP);

        $cacheDate->setData($wrappedJson);

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
     * @return mixed
     */
    public function getLastError()
    {
        return $this->lastError;
    }
}