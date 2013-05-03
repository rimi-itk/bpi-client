<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class Bpi
{
    protected $client;
    protected $authorization;
    protected $endpoint;

    /**
     * Create Bpi Client
     * @param string $endpoint URL
     * @param string $agency_id Agency ID
     * @param string $api_key App key
     * @param string $secret_key
     */
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

    /**
     * Get statistics
     * Parameterformat: Y-m-d
     * @param string $dateFrom
     * @param string $dateTo
     */
    public function getStatistics($dateFrom, $dateTo)
    {
        $result = $this->createDocument();
        $endpoint = clone $this->endpoint;
        $endpoint->firstItem('name', 'node')
            ->query('statistics')
            ->send($result, array('dateFrom'=>$dateFrom, 'dateTo'=>$dateTo));

        $item = new \Bpi\Sdk\Item\BaseItem($result);

        return $item;
    }
}
