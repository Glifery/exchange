<?php

namespace Exchange\EntityBagBundle\Bag;

interface BagInterface
{
    public function findEntity(array $criteria);
    public function addEntity(array $criteria, $entity);
}