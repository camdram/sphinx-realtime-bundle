<?php

namespace Acts\SphinxRealTimeBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Acts\SphinxRealTimeBundle\Doctrine\AbstractProvider;

class Provider extends AbstractProvider
{
    /**
     * @see Acts\SphinxRealTimeBundle\Doctrine\AbstractProvider::countObjects()
     */
    protected function countObjects($queryBuilder)
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new \InvalidArgumentException($queryBuilder, 'Doctrine\ORM\QueryBuilder');
        }

        /* Clone the query builder before altering its field selection and DQL,
         * lest we leave the query builder in a bad state for fetchSlice().
         */
        $qb = clone $queryBuilder;

        return $qb
            ->select($qb->expr()->count($queryBuilder->getRootAlias()))
            // Remove ordering for efficiency; it doesn't affect the count
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @see Acts\SphinxRealTimeBundle\Doctrine\AbstractProvider::fetchSlice()
     */
    protected function fetchSlice($queryBuilder, $limit, $offset)
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new \InvalidArgumentException($queryBuilder, 'Doctrine\ORM\QueryBuilder');
        }

        return $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @see Acts\SphinxRealTimeBundle\Doctrine\AbstractProvider::createQueryBuilder()
     */
    protected function createQueryBuilder()
    {
        return $this->managerRegistry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass)
            // ORM query builders require an alias argument
            ->{$this->options['query_builder_method']}('a');
    }
}
