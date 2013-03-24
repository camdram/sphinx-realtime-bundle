<?php
namespace Acts\SphinxRealTimeBundle\Paginator;

use Acts\SphinxRealTimeBundle\Transformer\SphinxToModelTransformerInterface;
use Acts\SphinxRealTimeBundle\Paginator\RawPartialResults;

/**
 * Partial transformed result set
 */
class TransformedPartialResults extends RawPartialResults
{
    protected $transformer;

    /**
     * @param array $resultSet
     * @param \Acts\SphinxRealTimeBundle\Transformer\SphinxToModelTransformerInterface $transformer
     */
    public function __construct(array $resultSet, SphinxToModelTransformerInterface $transformer)
    {
        parent::__construct($resultSet);

        $this->transformer = $transformer;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->transformer->transform($this->resultSet);
    }
}