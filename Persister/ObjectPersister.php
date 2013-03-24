<?php

namespace Acts\SphinxRealTimeBundle\Persister;

use Acts\SphinxRealTimeBundle\Provider\ProviderInterface;
use Acts\SphinxRealTimeBundle\Transformer\ModelToSphinxTransformerInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Exception;
use Acts\SphinxRealTimeBundle\Service\Index;

/**
 * Inserts, replaces and deletes single documents in an Sphinx type
 * Accepts domain model objects and converts them to Sphinx documents
 *
 */
class ObjectPersister implements ObjectPersisterInterface
{
    /** @var \Acts\SphinxRealTimeBundle\Service\Index */
    protected $index;
    protected $transformer;
    protected $objectClass;
    protected $fields;

    public function __construct(Index $index, ModelToSphinxTransformerInterface $transformer, $objectClass, array $fields)
    {
        $this->index            = $index;
        $this->transformer     = $transformer;
        $this->objectClass     = $objectClass;
        $this->fields          = $fields;
    }

    /**
     * Insert one object into the type
     * The object will be transformed to an sphinx document
     *
     * @param object $object
     */
    public function insertOne($object)
    {
        $document = $this->transformToSphinxDocument($object);
        $this->index->insert($document);
    }

    /**
     * Replaces one object in the type
     *
     * @param object $object
     * @return null
     **/
    public function replaceOne($object)
    {
        $document = $this->transformToSphinxDocument($object);
        $this->index->deleteById($document['id']);
        $this->index->insert($document);
    }

    /**
     * Deletes one object in the type
     *
     * @param object $object
     * @return null
     **/
    public function deleteOne($object)
    {
        $document = $this->transformToSphinxDocument($object);
        $this->index->deleteById($document['id']);
    }

    /**
     * Deletes one object in the type by id
     *
     * @param mixed $id
     *
     * @return null
     **/
    public function deleteById($id)
    {
        $this->index->deleteById($id);
    }


    /**
     * Inserts an array of objects in the type
     *
     * @param array of domain model objects
     **/
    public function insertMany(array $objects)
    {
        $documents = array();
        foreach ($objects as $object) {
            $documents[] = $this->transformToSphinxDocument($object);
        }
        $this->index->insertMany($documents);
    }

    /**
     * Transforms an object to an sphinx document
     *
     * @param object $object
     * @return array the sphinx document
     */
    public function transformToSphinxDocument($object)
    {
        return $this->transformer->transform($object, $this->fields);
    }
}
