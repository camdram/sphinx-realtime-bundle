<?php
namespace Acts\SphinxRealTimeBundle\Service;

use Acts\SphinxRealTimeBundle\Manager\ClientManager,
    Acts\SphinxRealTimeBundle\Manager\IndexManager;

class ConfigBuilder
{
    /** @var \Acts\SphinxRealTimeBundle\Manager\ClientManager */
    private $clients;

    /** @var \Acts\SphinxRealTimeBundle\Manager\IndexManager */
    private $indexes;

    public function __construct(ClientManager $clients, IndexManager $indexes)
    {
        $this->clients = $clients;
        $this->indexes = $indexes;
    }

    public function buildIndex(Index $index)
    {
        $output = 'index '.$index->getId()."\r\n{\r\n";

        foreach ($index->getFields() as $field) {
            $output .= "\trt_field = $field\r\n";
        }

        foreach ($index->getAttributes() as $name => $type) {
            switch ($type) {
                case 'float': $sphinx_type = 'float'; break;
                case 'int': $sphinx_type = 'uint'; break;
                default: $sphinx_type = 'string'; break;
            }
            $output .= "\trt_attr_$sphinx_type = $name\r\n";
        }

        foreach ($index->getConfig() as $key => $val) {
            if (is_array($val)) {
                $val = implode(',',$val);
            }
            if ($val) $output .= "\t$key = $val\r\n";
        }

        $output .= "}\r\n";
        return $output;
    }

    public function buildClient(Client $client)
    {
        $output = '';
        foreach ($this->indexes->getIndexes() as $index) {
            if ($index->getClient()->getId() === $client->getId()) {
                $output .= $this->buildIndex($index);
            }
        }

        $output .= "searchd {\r\n";
        foreach ($client->getConfig() as $key => $val) {
            $output .= "\t$key = $val\r\n";
        }
        $output .= "}\r\n";
        return $output;
    }

    public function build()
    {
        $outputs = array();
        foreach ($this->clients->getClients() as $client) {
            $outputs[$client->getId()] = $this->buildClient($client);
        }
        return $outputs;
    }
}
