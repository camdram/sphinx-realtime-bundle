<?php

namespace Acts\SphinxRealTimeBundle\Doctrine\ORM;

use Acts\SphinxRealTimeBundle\Doctrine\AbstractSphinxToModelTransformer;
use Doctrine\ORM\Query;

/**
 * Maps Sphinx documents with Doctrine objects
 * This mapper assumes an exact match between
 * sphinx documents ids and doctrine object ids
 */
class SphinxToModelTransformer extends AbstractSphinxToModelTransformer
{
    /**
     * Fetch objects for theses identifier values
     *
     * @param array $identifierValues ids values
     * @param Boolean $hydrate whether or not to hydrate the objects, false returns arrays
     * @return array of objects or arrays
     */
    protected function findByIdentifiers(array $identifierValues, $hydrate)
    {
        if (empty($identifierValues)) {
            return array();
        }
        $hydrationMode = $hydrate ? Query::HYDRATE_OBJECT : Query::HYDRATE_ARRAY;
        $qb = $this->registry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass)
            ->createQueryBuilder('o');
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb->where($qb->expr()->in('o.'.$this->options['identifier'], ':values'))
            ->setParameter('values', $identifierValues);

        return $qb->getQuery()->setHydrationMode($hydrationMode)->execute();
    }
}
