<?php

namespace Exchange\DomainBundle\Service;

use Exchange\DomainBundle\Entity\Office;
use Exchange\EntityBagBundle\Service\BaseRepositoryBag;

class OfficeBag extends BaseRepositoryBag
{
    public function fillCache()
    {
        $offices = $this->repository->findAll();

        foreach ($offices as $office) {
            /** @var Office $office */
            $bank = $office->getBank();
            $title = $office->getTitle();
            $address = $office->getAddress();

            $this->addEntity(array(
                    'bank' => $bank,
                    'title' => $title,
                    'address' => $address
                ), $office);
        }
    }
}