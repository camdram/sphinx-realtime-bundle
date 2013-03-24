<?php

namespace Acts\SphinxRealTimeBundle\Transformer;

/**
 * Maps Sphinx documents with model objects
 */
interface ModelToSphinxTransformerInterface
{
    /**
     * Transforms an object into a Sphinx object having the required keys
     *
     * @param object $object the object to convert
     * @param array $fields the keys we want to have in the returned array
     * @return array
     **/
    function transform($object, array $fields);
}
