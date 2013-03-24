<?php

namespace Acts\SphinxRealTimeBundle\Persister;

/**
 * Inserts, replaces and deletes single documents in a Sphinx type
 * Accepts domain model objects and converts them to Sphinx documents
 *
 */
interface ObjectPersisterInterface
{
    /**
     * Insert one object into the type
     * The object will be transformed to an Sphinx row
     *
     * @param object $object
     */
    function insertOne($object);

    /**
     * Replaces one object in the type
     *
     * @param object $object
     **/
    function replaceOne($object);

    /**
     * Deletes one object in the type
     *
     * @param object $object
     **/
    function deleteOne($object);

    /**
     * Deletes one object in the type by id
     *
     * @param mixed $id
     *
     * @return null
     **/
    function deleteById($id);

    /**
     * Inserts an array of objects in the type
     *
     * @param array of domain model objects
     **/
    function insertMany(array $objects);
}
