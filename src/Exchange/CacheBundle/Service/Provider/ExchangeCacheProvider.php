<?php

namespace Exchange\CacheBundle\Service\Provider;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Exchange\CacheBundle\Model\CacheData;
use Exchange\DomainBundle\Entity\ExchangeRate;

class ExchangeCacheProvider implements CacheProviderInterface
{
    /** @var EntityRepository */
//    private $bankRepo;

    /** @var EntityRepository */
//    private $officeRepo;

    /** @var EntityRepository */
    private $exchangeRepo;

    /** @var string */
    private $lastError = '';

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
//        $this->bankRepo = $em->getRepository('ExchangeDomainBundle:Bank');
//        $this->officeRepo = $em->getRepository('ExchangeDomainBundle:Office');
        $this->exchangeRepo = $em->getRepository('ExchangeDomainBundle:ExchangeRate');
    }

    /**
     * @param array $exchangeSet
     * @param array $officeSet
     * @param array $bankSet
     * @return bool
     */
    private function checkResults(array $exchangeSet, array $officeSet, array $bankSet)
    {
        if (!count($exchangeSet)) {
            $this->setLastError('exchange repository is empty');

            return false;
        }
        if (!count($officeSet)) {
            $this->setLastError('office repository is empty');

            return false;
        }
        if (!count($bankSet)) {
            $this->setLastError('bank repository is empty');

            return false;
        }

        return true;
    }

    /**
     * @param array $exchangeSet
     * @param array $officeSet
     * @param array $bankSet
     * @return CacheData
     */
    private function generateCacheData(array $exchangeSet, array $officeSet, array $bankSet)
    {
        $data = array(
            'exchanges' => $exchangeSet,
            'offices' => $officeSet,
            'banks' => $bankSet
        );

        $cacheData = new CacheData();
        $cacheData->setData($data);

        return $cacheData;
    }

    /**
     * @return CacheData|null
     */
    public function provide()
    {
        $exchangeSet = array();
        $officeSet = array();
        $bankSet = array();

        $exchanges = $this->exchangeRepo->findAll();
        foreach ($exchanges as $exchange) {
            /** @var ExchangeRate $exchange */
            $exchangeSet[$exchange->getId()] = $exchange;
            $officeSet[$exchange->getOffice()->getId()] = $exchange->getOffice();
            $bankSet[$exchange->getOffice()->getBank()->getId()] = $exchange->getOffice()->getBank();
        }

        if (!$this->checkResults($exchangeSet, $officeSet, $bankSet)) {
            return null;
        }

        return $this->generateCacheData($exchangeSet, $officeSet, $bankSet);
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