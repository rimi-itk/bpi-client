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
     * Get list of node based on some conditions
     *
     * @param array $queries available keys are: amount, offset, filter, sort
     *   filter and sort requires nested arrays
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
     * Push new node to BPI
     *
     * @param array $data
     * @throws \InvalidArgumentException
     * @return \Bpi\Sdk\Item\Node
     */
    public function push(array $data)
    {
        $node = $this->createDocument();
        $nodes = clone $this->endpoint;
        $nodes->firstItem('name', 'node')
            ->template('push')
            ->eachField(function ($field) use ($data) {
                if (!isset($data[(string)$field]))
                    throw new \InvalidArgumentException(sprintf('Field [%s] is required', (string) $field));

                $field->setValue($data[(string) $field]);
            })->post($node);

        return new \Bpi\Sdk\Item\Node($node);
    }
}
