<?php

namespace Acts\SphinxRealTimeBundle\Finder;
use Foolz\SphinxQL\SphinxQL;

interface FinderInterface
{
    /**
     * Searches for query results within a given limit
     *
     * @param mixed $query  Can be a string, an array or an Sphinx object
     * @param int $limit How many results to get
     * @return array results
     */
    function find(SphinxQL $query, $limit = null);
}
