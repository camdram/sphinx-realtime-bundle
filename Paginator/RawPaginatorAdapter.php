<?php

namespace Acts\SphinxRealTimeBundle\Paginator;

use Acts\SphinxRealTimeBundle\Paginator\PaginatorAdapterInterface;
use Acts\SphinxRealTimeBundle\Paginator\RawPartialResults;
use Acts\SphinxRealTimeBundle\Service\Index;
use Foolz\SphinxQL\SphinxQL;

/**
 * Allows pagination of Sphinx_Query. Does not map results
 */
class RawPaginatorAdapter implements PaginatorAdapterInterface
{
    /**
     * @var Index the object to search in
     */
    private $index = null;

    /**
     * @var SphinxQL the query to search
     */
    private $query = null;

    /**
     * @see PaginatorAdapterInterface::__construct
     *
     * @param Sphinx_SearchableInterface the object to search in
     * @param Sphinx_Query the query to search
     */
    public function __construct(Index $index, SphinxQL $query)
    {
        $this->index = $index;
        $this->query      = $query;
    }

    /**
     * Returns the paginated results.
     *
     * @return array
     */
    protected function getSphinxResults($offset, $itemCountPerPage)
    {
        $query = clone $this->query;
        $query->limit($offset, $itemCountPerPage);

        return $this->index->search($query);
    }

    /**
     * Returns the paginated results.
     *
     * @return Acts\SphinxRealTimeBundle\Paginator\RawPartialResultInterface
     */
    public function getResults($offset, $itemCountPerPage)
    {
        return new RawPartialResults($this->getSphinxResults($offset, $itemCountPerPage));
    }

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getTotalHits()
    {
        return count($this->index->search($this->query));
    }
}
