<?php

namespace Exchange\DomainBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Exchange\DomainBundle\Entity\ExchangeRate;

class ExchangeStatistic
{
    /** @var EntityManager */
    private $em;

    /** @var EntityRepository */
    private $exchangeRepo;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->exchangeRepo = $em->getRepository('ExchangeDomainBundle:ExchangeRate');
    }

    public function getExchangeData()
    {
        $exchanges = $this->exchangeRepo->findAll();
        $statistic = array();

        foreach ($exchanges as $exchange) {
            /** @var ExchangeRate $exchange */
            $exchangeSet[$exchange->getId()] = $exchange;

            $direction = $exchange->getDirection();
            $value = $exchange->getValue();

            if (!isset($statistic[$direction])) {
                $statistic[$direction] = array(
                    'direction' => $direction,
                    'min' => $value,
                    'max' => $value,
                );
            }

            if ($value < $statistic[$direction]['min']) {
                $statistic[$direction]['min'] = $value;
            }

            if ($value > $statistic[$direction]['max']) {
                $statistic[$direction]['max'] = $value;
            }
        }

        $this->resolveOptimalValue($statistic);

        return array(
            'exchanges' => $exchangeSet,
            'statistic' => $statistic
        );
    }

    private function resolveOptimalValue(array &$statisticArray)
    {
        foreach ($statisticArray as &$statistic) {
            $directionArray = explode('_', $statistic['direction']);
            switch ($directionArray[1]) {
                case 'H': $optimalField = 'min'; break;
                case 'L': $optimalField = 'max'; break;
                default: throw new \LogicException('Unexpected direction: '.$statistic['direction']);
            }

            $statistic['optimal'] = $statistic[$optimalField];
        }

        return $statistic;
    }
} 