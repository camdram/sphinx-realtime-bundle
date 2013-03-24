<?php

namespace Acts\SphinxRealTimeBundle\Finder;

use Acts\SphinxRealTimeBundle\Paginator\PaginatorAdapterInterface;
use Pagerfanta\Pagerfanta;
use Foolz\SphinxQL\SphinxQL;

interface PaginatedFinderInterface extends FinderInterface
{
    /**
     * Searches for query results and returns them wrapped in a paginator
     *
     * @param mixed $query  Can be a string, an array
     * @return Pagerfanta paginated results
     */
    function findPaginated(SphinxQL $query);

    /**
     * Creates a paginator adapter for this query
     *
     * @param mixed $query
     * @return PaginatorAdapterInterface
     */
    function createPaginatorAdapter(SphinxQL $query);
}
