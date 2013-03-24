<?php
namespace Acts\SphinxRealTimeBundle\Manager;

use Psr\Log\InvalidArgumentException;

class IndexManager
{
    private $indexes;

    public function __construct(array $indexes)
    {
        $this->indexes = $indexes;
    }

    public function hasId($id)
    {
        return isset($this->indexes[$id]);
    }

    /**
     * @param $id
     * @return \Acts\SphinxRealTimeBundle\Service\Index
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function getById($id)
    {
        if (isset($this->indexes[$id])) {
            return $this->indexes[$id];
        }

        throw new InvalidArgumentException("'$id' is not a valid Sphinx index id'");
    }

    public function getIndexes()
    {
        return $this->indexes;
    }
}