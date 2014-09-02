<?php

namespace Exchange\DomainBundle\Service;

use Exchange\DomainBundle\Entity\Bank;
use Exchange\DomainBundle\Entity\ExchangeRate;
use Exchange\EntityBagBundle\Service\BaseRepositoryBag;

class ExchangeRateBag extends BaseRepositoryBag
{
    public function fillCache()
    {
        $exchangeRates = $this->repository->findAll();

        foreach ($exchangeRates as $exchangeRate) {
            /** @var ExchangeRate $exchangeRate */
            $office = $exchangeRate->getOffice();
            $direction = $exchangeRate->getDirection();

            $this->addEntity(array(
                    'title' => $office,
                    'direction' => $direction,
                ), $exchangeRate);
        }
    }
}