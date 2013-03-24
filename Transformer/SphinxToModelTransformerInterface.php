<?php

namespace Acts\SphinxRealTimeBundle\Transformer;

/**
 * Maps Sphinx documents with model objects
 */
interface SphinxToModelTransformerInterface
{
    /**
     * Transforms an array of Sphinx objects into an array of
     * model objects fetched from the doctrine repository
     *
     * @param array of sphinx objects
     * @return array of model objects
     **/
    function transform(array $sphinxObjects);

    /**
     * Returns the object class used by the transformer.
     *
     * @return string
     */
    function getObjectClass();

    /**
     * Returns the identifier field from the options
     *
     * @return string the identifier field
     */
    function getIdentifierField();
}
