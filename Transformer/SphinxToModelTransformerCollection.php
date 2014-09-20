<?php

namespace Acts\SphinxRealTimeBundle\Transformer;

use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * Holds a collection of transformers for an index wide transformation.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class SphinxToModelTransformerCollection implements SphinxToModelTransformerInterface
{
    protected $transformers = array();

    private $offset;

    private $num_indexes;

    public function __construct(array $transformers, $offset)
    {
        $this->transformers = $transformers;
        $this->offset = $offset;
    }

    public function getObjectClass()
    {
        return array_map(function ($transformer) {
            return $transformer->getObjectClass();
        }, $this->transformers);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierField()
    {
        return array_map(function ($transformer) {
            return $transformer->getIdentifierField();
        }, $this->transformers);
    }

    public function transform(array $sphinxObjects)
    {
        $sorted = array();
        foreach ($sphinxObjects as $object) {
            $sorted[] = $object;
        }

        $transformed = array();
        foreach ($sorted as $type => $objects) {
            $transformedObjects = $this->transformers[$type]->transform($objects);
            $identifierGetter = 'get' . ucfirst($this->transformers[$type]->getIdentifierField());
            $transformed[$type] = array_combine(
                array_map(
                    function($o) use ($identifierGetter) {
                        return $o->$identifierGetter();
                    },
                    $transformedObjects
                ),
                $transformedObjects
            );
        }

        $result = array();
        foreach ($sphinxObjects as $object) {
            $result[] = $transformed[$object->getType()][$object->getId()];
        }

        return $result;
    }

    public function hybridTransform(array $sphinxObjects)
    {
        $objects = $this->transform($sphinxObjects);

        $result = array();
        for ($i = 0; $i < count($sphinxObjects); $i++) {
            $result[] = new HybridResult($sphinxObjects[$i], $objects[$i]);
        }

        return $result;
    }

    protected function getTypeToClassMap()
    {
        return array_map(function ($transformer) {
            return $transformer->getObjectClass();
        }, $this->transformers);
    }
}
