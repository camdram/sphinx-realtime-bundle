<?php
namespace Acts\SphinxRealTimeBundle\Service;

use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Connection;

use Acts\SphinxRealTimeBundle\Service\SphinxClient;
use Acts\SphinxRealTimeBundle\Manager\IndexManager;

class Client
{
    private $id;

    private $mysql_host;

    private $mysql_port;

    private $config;

    /** @var \mysqli */
    private $connection;

    public function __construct($id, $config)
    {
        $this->id = $id;

        $this->mysql_host = $config['mysql_host'];
        $this->mysql_port = $config['mysql_port'];
        unset($config['mysql_host']);
        unset($config['mysql_port']);

        unset($config['host']);
        $this->config = $config;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getConfig()
    {
        return $this->config;
    }

    private function getConnection()
    {
        if (!$this->connection) {
            $this->connection = new Connection();
            $this->connection->setConnectionParams($this->mysql_host, $this->mysql_port);
        }
        return $this->connection;
    }

    public function query(SphinxQL $query)
    {
        SphinxQL::forge($this->getConnection());
        return $query->execute();
        if ($result === false) {
            throw new \RuntimeException($this->connection->error);
        }
        return $result;
    }

    public function insert($index, array $document)
    {
        $sq = SphinxQL::forge($this->getConnection())->insert()->into('`'.$index.'`');
        $sq->columns(array_keys($document))->values($document);
        return $sq->execute();
    }

    public function insertMany($index, array $keys, array $documents)
    {
        $sq = SphinxQL::forge($this->getConnection())->insert()->into('`'.$index.'`');
        $sq->columns($keys);
        foreach ($documents as $document) {
            foreach ($document as $key => &$value) {
                if (is_null($value)) $value = '';
            }
            $sq->values($document);
        }
        return $sq->execute();
    }

    public function deleteById($index, $id)
    {
        return SphinxQL::forge($this->getConnection())->delete()
            ->from($index)
            ->where('id', $id)
            ->execute();
    }

}