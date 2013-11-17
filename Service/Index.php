<?php
namespace Acts\SphinxRealTimeBundle\Service;

use Acts\SphinxRealTimeBundle\Service\Client;
use Foolz\SphinxQL\SphinxQL;

class Index
{
    private $id;

    /** @var \Acts\SphinxRealTimeBundle\Service\Client */
    private $client;

    private $fields;

    private $attributes;

    private $keys;

    private $config;

    public function __construct($id, $client, $fields, $attributes, $config)
    {
        $this->id = $id;
        $this->client = $client;
        $this->fields = $fields;
        $this->attributes = $attributes;
        $this->config = $config;

        $this->keys = array_unique(array_merge(array('id'), $this->fields, array_keys($this->attributes)));
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getKeys()
    {
        return $this->keys;
    }

    public function insertMany(array $documents)
    {
        $this->getClient()->insertMany($this->getId(), $this->getKeys(), $documents);
    }

    public function deleteById($id)
    {
        $this->getClient()->deleteById($this->getId(), $id);
    }

    public function query(SphinxQL $query)
    {
        return $this->getClient()->query($query);
    }

    public function insert($document)
    {
        return $this->getClient()->insert($this->getId(), $document);
    }

    public function truncate()
    {
        return $this->getClient()->truncate($this->getId());
    }

    public function search(SphinxQL $query)
    {
        $query->from($this->getId());
        return $this->getClient()->query($query);
    }
}