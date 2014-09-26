<?php

namespace Exchange\CacheBundle\Service\Provider;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Exchange\CacheBundle\Model\CacheData;
use Exchange\DomainBundle\Entity\ExchangeRate;
use Exchange\DomainBundle\Service\ExchangeStatistic;

class ExchangeCacheProvider implements CacheProviderInterface
{
    /** @var ExchangeStatistic */
    private $exchangeStatistic;

    /** @var EntityRepository */
    private $exchangeRepo;

    /** @var string */
    private $lastError = '';

    /**
     * @param EntityManager $em
     * @param \Exchange\DomainBundle\Service\ExchangeStatistic $exchangeStatistic
     */
    public function __construct(EntityManager $em, ExchangeStatistic $exchangeStatistic)
    {
        $this->exchangeRepo = $em->getRepository('ExchangeDomainBundle:ExchangeRate');
        $this->exchangeStatistic = $exchangeStatistic;
    }

    /**
     * @param array $exchangeSet
     * @param array $statistic
     * @internal param array $officeSet
     * @internal param array $bankSet
     * @return bool
     */
    private function checkResults(array $exchangeSet, array $statistic)
    {
        if (!count($exchangeSet)) {
            $this->setLastError('exchange repository is empty');

            return false;
        }

        if (!count($statistic)) {
            $this->setLastError('statistic array is empty');

            return false;
        }

        return true;
    }

    /**
     * @param array $data
     * @return CacheData
     */
    private function generateCacheData(array $data)
    {
        $cacheData = new CacheData();
        $cacheData->setData($data);

        return $cacheData;
    }

    /**
     * @return CacheData|null
     */
    public function provide()
    {
        $exchangeData = $this->exchangeStatistic->getExchangeData();

        if (!$this->checkResults($exchangeData['exchanges'], $exchangeData['statistic'])) {
            return null;
        }

        return $this->generateCacheData($exchangeData);
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