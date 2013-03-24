<?php

namespace Acts\SphinxRealTimeBundle\Doctrine;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ManagerRegistry;
use Acts\SphinxRealTimeBundle\Finder\FinderInterface;
use Acts\SphinxRealTimeBundle\Manager\RepositoryManager as BaseManager;

/**
 *
 * Allows retrieval of basic or custom repository for mapped Doctrine
 * entities/documents.
 */
class RepositoryManager extends BaseManager
{
    protected $entities = array();
    protected $repositories = array();
    protected $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry, Reader $reader)
    {
        $this->managerRegistry = $managerRegistry;
        parent::__construct($reader);
    }

    /**
     * Return repository for entity
     *
     * Returns custom repository if one specified otherwise
     * returns a basic respository.
     */
    public function getRepository($entityName)
    {
        $realEntityName = $entityName;
        if (strpos($entityName, ':') !== false) {
            list($namespaceAlias, $simpleClassName) = explode(':', $entityName);
            $realEntityName = $this->managerRegistry->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }

        return parent::getRepository($realEntityName);
    }

}
