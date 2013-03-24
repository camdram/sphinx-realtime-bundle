<?php

namespace Acts\SphinxRealTimeBundle\Paginator;

use Acts\SphinxRealTimeBundle\Transformer\SphinxToModelTransformerInterface;
use Acts\SphinxRealTimeBundle\Paginator\TransformedPartialResults;
use Acts\SphinxRealTimeBundle\Service\Index;
use Foolz\SphinxQL\SphinxQL;

/**
 * Allows pagination of SphinxQL
 */
class TransformedPaginatorAdapter extends RawPaginatorAdapter
{
    private $transformer;

    /**
     * @param Index the object to search in
     * @param SphinxQL the query to search
     * @param SphinxToModelTransformerInterface the transformer for fetching the results
     */
    public function __construct(Index $index, SphinxQL $query, SphinxToModelTransformerInterface $transformer)
    {
        parent::__construct($index, $query);

        $this->transformer = $transformer;
    }

    /**
     * {@inheritDoc}
     */
    public function getResults($offset, $length)
    {
        return new TransformedPartialResults($this->getSphinxResults($offset, $length), $this->transformer);
    }
}
