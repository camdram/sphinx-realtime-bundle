<?php

namespace Acts\SphinxRealTimeBundle;
use Acts\SphinxRealTimeBundle\Manager\IndexManager;
use Foolz\SphinxQL\DatabaseException;

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
            try {
                $index->truncate();
            }
            catch (DatabaseException $ex)
            {
            }
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
        try {
            $this->indexManager->getById($indexName)->truncate();
        }
        catch (DatabaseException $ex)
        {
        }
    }

}