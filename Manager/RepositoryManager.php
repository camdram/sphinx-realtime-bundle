<?php

namespace Acts\SphinxRealTimeBundle\Manager;

use Doctrine\Common\Annotations\Reader;
use Acts\SphinxRealTimeBundle\Finder\FinderInterface;
use RuntimeException;
/**
 *
 * Allows retrieval of basic or custom repository for mapped Doctrine
 * entities/documents.
 */
class RepositoryManager implements RepositoryManagerInterface
{
    protected $entities = array();
    protected $repositories = array();
    protected $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function addEntity($entityName, FinderInterface $finder, $repositoryName = null)
    {
        $this->entities[$entityName]= array();
        $this->entities[$entityName]['finder'] = $finder;
        $this->entities[$entityName]['repositoryName'] = $repositoryName;
    }

    /**
     * Return repository for entity
     *
     * Returns custom repository if one specified otherwise
     * returns a basic respository.
     */
    public function getRepository($entityName)
    {
        if (isset($this->repositories[$entityName])) {
            return $this->repositories[$entityName];
        }

        if (!isset($this->entities[$entityName])) {
            throw new RuntimeException(sprintf('No search finder configured for %s', $entityName));
        }

        $repository = $this->createRepository($entityName);
        $this->repositories[$entityName] = $repository;

        return $repository;
    }

    protected function getRepositoryName($entityName)
    {
        if (isset($this->entities[$entityName]['repositoryName'])) {
            return $this->entities[$entityName]['repositoryName'];
        }

        $refClass   = new \ReflectionClass($entityName);
        $annotation = $this->reader->getClassAnnotation($refClass, 'Acts\\SphinxRealTimeBundle\\Configuration\\Search');
        if ($annotation) {
            $this->entities[$entityName]['repositoryName']
                = $annotation->repositoryClass;
            return $annotation->repositoryClass;
        }

        return 'Acts\SphinxRealTimeBundle\Repository';
    }

    private function createRepository($entityName)
    {
        $repositoryName = $this->getRepositoryName($entityName);
        if (!class_exists($repositoryName)) {
            throw new RuntimeException(sprintf('%s repository for %s does not exist', $repositoryName, $entityName));
        }
        return new $repositoryName($this->entities[$entityName]['finder']);
    }

}
