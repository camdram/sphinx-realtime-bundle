<?php

namespace Acts\SphinxRealTimeBundle\Paginator;

use Acts\SphinxRealTimeBundle\Paginator\PartialResultsInterface;

/**
 * Raw partial results transforms to a simple array
 */
class RawPartialResults implements PartialResultsInterface
{
    protected $resultSet;

    /**
     * @param array $resultSet
     */
    public function __construct(array $resultSet)
    {
        $this->resultSet = $resultSet;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->resultSet;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalHits()
    {
        return count($this->resultSet);
    }

}