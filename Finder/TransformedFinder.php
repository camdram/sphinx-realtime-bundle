<?php

namespace Acts\SphinxRealTimeBundle\Finder;

use Acts\SphinxRealTimeBundle\Finder\PaginatedFinderInterface;
use Acts\SphinxRealTimeBundle\Transformer\SphinxToModelTransformerInterface;
use Acts\SphinxRealTimeBundle\Paginator\TransformedPaginatorAdapter;
use Acts\SphinxRealTimeBundle\Paginator\FantaPaginatorAdapter;
use Pagerfanta\Pagerfanta;
use Foolz\SphinxQL\SphinxQL;
use Acts\SphinxRealTimeBundle\Service\Index;

/**
 * Finds sphinx documents and map them to persisted objects
 */
class TransformedFinder implements PaginatedFinderInterface
{
    protected $index;
    protected $transformer;

    public function __construct(Index $index, SphinxToModelTransformerInterface $transformer)
    {
        $this->index  = $index;
        $this->transformer = $transformer;
    }

    /**
     * Search for a query string
     *
     * @param string $query
     * @param integer $limit
     * @return array of model objects
     **/
    public function find(SphinxQL $query, $limit = null)
    {
        $results = $this->search($query, $limit);
        return $this->transformer->transform($results);
    }

    protected function search(SphinxQL $query, $limit = null)
    {
        if (null !== $limit) {
            $query->limit($limit);
        }
        $results = $this->index->search($query);

        return $results;
    }

    /**
     * Gets a paginator wrapping the result of a search
     *
     * @return Pagerfanta
     */
    public function findPaginated(SphinxQL $query)
    {
        $paginatorAdapter = $this->createPaginatorAdapter($query);

        return new Pagerfanta(new FantaPaginatorAdapter($paginatorAdapter));
    }

    /**
     * {@inheritdoc}
     */
    public function createPaginatorAdapter(SphinxQL $query)
    {
        return new TransformedPaginatorAdapter($this->index, $query, $this->transformer);
    }
}
