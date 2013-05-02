<?php

require_once __DIR__ . '/../../vendor/autoload.php';

class Bpi
{
  protected $client;
  protected $authorization;
  protected $endpoint;

    public function __construct($endpoint, $agency_id, $api_key, $secret_key)
    {
        $this->client = new \Goutte\Client();
        $this->authorization = new \Bpi\Sdk\Authorization($agency_id, $api_key, $secret_key);
        $this->endpoint = $this->createDocument();
        $this->endpoint->loadEndpoint($endpoint);
    }

    /**
     * Create new document
     *
     * @return \Bpi\Sdk\Document
     */
    protected function createDocument()
    {
        return new \Bpi\Sdk\Document($this->client, $this->authorization);
    }

    /**
     *
     * @param array $queries
     * @return \Bpi\Sdk\NodeList
     */
    public function searchNodes(array $queries = array())
    {
        $nodes = clone $this->endpoint;
        $nodes->firstItem('name', 'node')
            ->link('collection')
            ->follow($nodes);

        $nodes->firstItem('type', 'collection')
            ->query('refinement')
            ->send($nodes, $queries);

        $nodes->reduceItemsByAttr('type', 'entity');
        return new \Bpi\Sdk\NodeList($nodes);
    }
}
