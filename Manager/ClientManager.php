<?php
namespace Acts\SphinxRealTimeBundle\Manager;

use Psr\Log\InvalidArgumentException;

class ClientManager
{
    private $clients;

    public function __construct(array $clients)
    {
        $this->clients = $clients;
    }

    public function getById($id)
    {
        if (isset($this->clients[$id])) {
            return $this->clients[$id];
        }

        throw new InvalidArgumentException("'$id' is not a valid Sphinx client id'");
    }

    public function getClients()
    {
        return $this->clients;
    }
}