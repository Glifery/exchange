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
            $address = $office->getAddress();

            $this->addEntity(array('address' => $address), $office);
        }
    }
}