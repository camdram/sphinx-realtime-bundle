<?php

namespace Acts\SphinxRealTimeBundle;
use Acts\SphinxRealTimeBundle\Manager\IndexManager;

/**
 * Truncates indexes
 */
class Resetter
{
    protected $indexManager;

    /**
     * Constructor.
     *
     * @param IndexManager $indexManager
     */
    public function __construct(IndexManager $indexManager)
    {
        $this->indexManager = $indexManager;
    }

    /**
     * Deletes and recreates all indexes
     */
    public function resetAllIndexes()
    {
        foreach ($this->indexManager->getIndexes() as $index) {
            $index->truncate();
        }
    }

    /**
     * Deletes and recreates the named index
     *
     * @param string $indexName
     * @throws \InvalidArgumentException if no index exists for the given name
     */
    public function resetIndex($indexName)
    {
        $this->indexManager->getById($indexName)->truncate();
    }

}