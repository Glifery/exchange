<?php

namespace Exchange\DomainBundle\Service;

use Exchange\DomainBundle\Entity\Bank;
use Exchange\EntityBagBundle\Service\BaseRepositoryBag;

class BankBag extends BaseRepositoryBag
{
    public function fillCache()
    {
        $banks = $this->repository->findAll();

        foreach ($banks as $bank) {
            /** @var Bank $bank */
            $title = $bank->getTitle();

            $this->addEntity(array('title' => $title), $bank);
        }
    }
}