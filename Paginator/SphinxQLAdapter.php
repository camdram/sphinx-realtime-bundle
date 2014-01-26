<?php
namespace Acts\SphinxRealTimeBundle\Paginator;

use Acts\SphinxRealTimeBundle\Service\Client;
use Foolz\SphinxQL\SphinxQL;

class SphinxQLAdapter implements PaginatorAdapterInterface
{
    private $query;
    private $client;

    public function __construct(Client $client, SphinxQL $query)
    {
        $this->query = $query;
        $this->client = $client;
    }

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     *
     * @api
     */
    function getTotalHits()
    {
        return count($this->client->query($this->query));
    }

    /**
     * Returns an slice of the results.
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return Acts\SphinxRealTimeBundle\Paginator\PartialResults
     *
     * @api
     */
    function getResults($offset, $length)
    {
        $query = clone $this->query;
        $query->offset($offset)->limit($length);
        return new RawPartialResults($this->client->query($query));
    }

}