<?php

namespace Exchange\EntityBagBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Exchange\EntityBagBundle\Bag\BagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaseRepositoryBag implements BagInterface
{
    /** @var EntityManager */
    private $em;

    /** @var EntityRepository */
    protected $repository;

    /** @var array */
    protected $bag;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->bag = array();
    }

    /**
     * @param string $repositoryName
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function initRepository($repositoryName)
    {
        $this->repository = $this->em->getRepository($repositoryName);

        if (!$this->repository) {
            throw new NotFoundHttpException('Repository \'' . $repositoryName .'\' not found');
        }
    }

    /**
     * @param array $criteria
     * @return string
     */
    private function getHash(array $criteria)
    {
        return md5(serialize($criteria));
    }

    /**
     * @param array $criteria
     * @return null|object
     */
    public function findEntity(array $criteria)
    {
        $hash = $this->getHash($criteria);

        if (isset($this->bag[$hash])) {
            return $this->bag[$hash];
        }

        if (($this->repository) && ($entity = $this->repository->findOneBy($criteria))) {
            $this->bag[$hash] = $entity;

            return $entity;
        }

        return null;
    }

    /**
     * @param array $criteria
     * @param object $entity
     * @throws \LogicException
     */
    public function addEntity(array $criteria, $entity)
    {
        $hash = $this->getHash($criteria);

        if (isset($this->bag[$hash])) {
            throw new \LogicException('Entity with hash \'' . $hash . '\' already exists in bag');
        }

        $this->bag[$hash] = $entity;

        $this->em->persist($entity);
    }

    public function clearCache()
    {
        $this->bag = array();
    }

    public function flush()
    {
        $this->em->flush();
        $this->clearCache();
    }
}