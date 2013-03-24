<?php

namespace Acts\SphinxRealTimeBundle\Paginator;

interface PaginatorAdapterInterface
{
    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     *
     * @api
     */
    function getTotalHits();

    /**
     * Returns an slice of the results.
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return Acts\SphinxRealTimeBundle\Paginator\PartialResults
     *
     * @api
     */
    function getResults($offset, $length);
}
