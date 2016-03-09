<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * TODO please add a general description about the purpose of this class.
 */
class Bpi
{
    /**
     *
     * @var \Goutte\Client
     */
    protected $client;

    /**
     *
     * @var \Bpi\Sdk\Authorization
     */
    protected $authorization;

    /**
     *
     * @var \Bpi\Sdk\Document
     */
    protected $endpoint;

    /**
     *
     * @var string
     */
    protected $endpoint_url;

    /**
     *
     * @var \Bpi\Sdk\Document
     */
    protected $current_document;

    /**
     * Create Bpi Client
     *
     * @param string $endpoint URL
     * @param string $agency_id Agency ID
     * @param string $api_key App key
     * @param string $secret_key
     */
    public function __construct($endpoint, $agency_id, $api_key, $secret_key)
    {
        $this->client = new \Goutte\Client();
        $this->authorization = new \Bpi\Sdk\Authorization($agency_id, $api_key, $secret_key);
        $this->current_document = $this->endpoint = $this->createDocument();
        $this->endpoint->loadEndpoint($endpoint);
        $this->endpoint_url = $endpoint;
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
        $nodes = $this->createDocument();
        $endpoint = clone $this->endpoint;
        $endpoint->firstItem('name', 'node')
            ->link('collection')
            ->get($nodes);

        $nodes->firstItem('type', 'collection')
            ->query('refinement')
            ->send($nodes, $queries);
        $nodes->setFacets();
        $this->current_document = $nodes;

        return new \Bpi\Sdk\NodeList($nodes);
    }

    /**
     * Push new node to BPI
     *
     * @param array $data TODO please add some documentation of this property.
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
                // nb: variable $data[(string)$field] may be empty.
                if (!isset($data[(string)$field])) {
                    throw new \InvalidArgumentException(sprintf('Field [%s] is required', (string) $field));
                }
                $field->setValue($data[(string) $field]);
            })->post($node);

        $this->current_document = $node;

        return new \Bpi\Sdk\Item\Node($node);
    }

    /**
     * Mark node as syndicated
     *
     * @param string $id BPI node ID
     * @return boolean operation status
     */
    public function syndicateNode($id)
    {
        $result = $this->createDocument();

        $endpoint = clone $this->endpoint;
        $endpoint->firstItem('name', 'node')
            ->query('syndicated')
            ->send($result, array('id' => $id));

        $this->current_document = $result;

        return $result->status()->isSuccess();
    }

    /**
     * Mark node as deleted
     *
     * @param string $id BPI node ID
     * @return boolean operation status
     */
    public function deleteNode($id)
    {
        $result = $this->createDocument();

        $endpoint = clone $this->endpoint;
        $endpoint->firstItem('name', 'node')
            ->query('delete')
            ->send($result, array('id' => $id));

        $this->current_document = $result;

        return $result->status()->isSuccess();
    }

    /**
     * Get statistics
     * Parameterformat: Y-m-d
     *
     * TODO How about using DateTimes here and convert to string when calling the
     * API?
     *
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

        $this->current_document = $result;

        return new \Bpi\Sdk\Item\BaseItem($result);
    }

    /**
     * Get single Node by ID
     *
     * @param string $id BPI node ID
     * @return \Bpi\Sdk\Item\Node
     */
    public function getNode($id)
    {
        $result = $this->createDocument();

        $endpoint = clone $this->endpoint;
        $endpoint->firstItem('name', 'node')
            ->query('item')
            ->send($result, array('id' => $id));

        $this->current_document = $result;

        return new \Bpi\Sdk\Item\Node($result);
    }

    /**
     * Get list of dictionaries
     *
     * @return array
     */
    public function getDictionaries()
    {
        $result = $this->createDocument();

        $endpoint = clone $this->endpoint;
        $endpoint->firstItem('name', 'profile')
            ->link('dictionary')
            ->get($result);

        $this->current_document = $result;

        $dictionary = array();
        foreach ($result as $item)
        {
            $properties = array();
            $item->walkProperties(function($property) use (&$properties){
                $properties[$property['name']] = $property['@value'];
            });

            $dictionary[$properties['group']][] = $properties['name'];
        }

        return $dictionary;
    }

    /**
     * TODO This is a public function prefixed with an _ signalling that it is
     * not to be used for public consumption. Why is this necessary?
     *
     * @return \Bpi\Sdk\Document
     */
    public function _getCurrentDocument()
    {
        return $this->current_document;
    }


    // -----------------------------------------------------------------------------

    public function searchChannels($query = array()) {
        $channels = $this->createGenericDocument();
        $channels->request('GET', $this->endpoint_url . '/channel/');

        return new \Bpi\Sdk\ChannelList($channels);
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $adminId
     */
    public function createChannel(array $data) {
        $this->checkRequired($data, [ 'name', 'description', 'adminId' ]);
        $values = $this->apiRename($data, [
            'name' => 'name',
            'description' => 'channelDescription',
            'adminId' => 'editorId',
        ]);

        $channels = $this->createGenericDocument();
        $channels->request('POST', $this->endpoint_url . '/channel/', $values);

        return new \Bpi\Sdk\Item\Channel($channels);
    }

    /**
     * @param string $id
     */
    public function getChannel($id) {
        $channels = $this->createGenericDocument();
        $channels->request('GET', $this->endpoint_url . '/channel/' . $id);

        return new \Bpi\Sdk\Item\Channel($channels);
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $adminId
     */
    public function updateChannel($channelId, array $data) {
        $this->checkRequired($data, [ 'name', 'description' ]);

        $values = $this->apiRename($data, [
            'name' => 'channelName',
            'description' => 'channelDescription',
        ]);

        $channels = $this->createGenericDocument();
        $channels->request('POST', $this->endpoint_url . '/channel/edit/' . $channelId, $values);

        return new \Bpi\Sdk\Item\Channel($channels);
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $adminId
     */
    public function deleteChannel($channelId) {
        $channels = $this->createGenericDocument();
        $channels->request('DELETE', $this->endpoint_url . '/channel/remove/' . $channelId);

        return !$channels->status()->isError();
    }

    public function addEditorToChannel($channelId, $adminId, $editorIds) {
        if (!is_array($editorIds)) {
            $editorIds = [ $editorIds ];
        }
        $values = [
            'channelId' => $channelId,
            'adminId' => $adminId,
            'users' => array_map(function($editorId) {
                return [ 'editorId' => $editorId ];
            }, $editorIds),
        ];

        $channels = $this->createGenericDocument();
        $channels->request('POST', $this->endpoint_url . '/channel/add/editor', $values);

        $successes = [];
        // @see http://api.symfony.com/3.0/Symfony/Component/DomCrawler/Crawler.html
        $channels->filterXPath('result/success_list/item')->each(function($el) use (&$successes) {
            $successes[] = $el->textContent;
        });

        return count($successes) == count($editorIds);
    }

    public function removeEditorFromChannel($channelId, $adminId, $editorIds) {
        if (!is_array($editorIds)) {
            $editorIds = [ $editorIds ];
        }
        $values = [
            'channelId' => $channelId,
            'adminId' => $adminId,
            'users' => array_map(function($editorId) {
                return [ 'editorId' => $editorId ];
            }, $editorIds),
        ];

        $channels = $this->createGenericDocument();
        $channels->request('POST', $this->endpoint_url . '/channel/remove/editor', $values);

        $successes = [];
        $channels->filterXPath('result/success_list/item')->each(function($el) use (&$successes) {
            $successes[] = $el->textContent;
        });

        return count($successes) == count($editorIds);
    }

    public function getChannelsByUser($userId) {
        $channels = $this->createGenericDocument();
        $channels->request('GET', $this->endpoint_url . '/channel/user/' . $userId);

        return new \Bpi\Sdk\ChannelList($channels);
    }

    public function addNodeToChannel($channelId, $editorId, $nodeIds) {
        if (!is_array($nodeIds)) {
            $nodeIds = [ $nodeIds ];
        }
        $values = [
            'channelId' => $channelId,
            'editorId' => $editorId,
            'nodes' => array_map(function($nodeId) {
                return [ 'nodeId' => $nodeId ];
            }, $nodeIds),
        ];

        $channels = $this->createGenericDocument();
        $channels->request('POST', $this->endpoint_url . '/channel/add/node', $values);

        $successes = [];
        $channels->filterXPath('result/success_list/item')->each(function($el) use (&$successes) {
            $successes[] = $el->textContent;
        });

        return count($successes) == count($nodeIds);
    }

    public function removeNodeFromChannel($channelId, $editorId, $nodeIds) {
        if (!is_array($nodeIds)) {
            $nodeIds = [ $nodeIds ];
        }
        $values = [
            'channelId' => $channelId,
            'editorId' => $editorId,
            'nodes' => array_map(function($nodeId) {
                return [ 'nodeId' => $nodeId ];
            }, $nodeIds),
        ];

        $channels = $this->createGenericDocument();
        $channels->request('POST', $this->endpoint_url . '/channel/remove/node', $values);

        $successes = [];
        $channels->filterXPath('result/success_list/item')->each(function($el) use (&$successes) {
            $successes[] = $el->textContent;
        });

        return count($successes) == count($nodeIds);
    }

    protected function createGenericDocument()
    {
        return new \Bpi\Sdk\GenericDocument($this->client, $this->authorization);
    }

    // -----------------------------------------------------------------------------

    public function searchUsers($query = array()) {
        $users = $this->createGenericDocument();
        $users->request('GET', $this->endpoint_url . '/user/');

        // var_export([ $users->getRawResponse() ]); die(__FILE__);

        return new \Bpi\Sdk\UserList($users);
    }

    public function createUser(array $data) {
        $this->checkRequired($data, [ 'externalId', 'email' ]);

        $values = $this->apiRename($data, [
            'externalId' => 'externalId',
            'email' => 'email',
            'firstName' => 'userFirstName',
            'lastName' => 'userLastName',
        ]);

        $users = $this->createGenericDocument();
        $users->request('POST', $this->endpoint_url . '/user/', $values);

        return new \Bpi\Sdk\Item\User($users);
    }

    public function getUser($id) {
        $users = $this->createGenericDocument();
        $users->request('GET', $this->endpoint_url . '/user/' . $id);

        return new \Bpi\Sdk\Item\User($users);
    }

    /**
     * @param string $userId
     * @param array $data
     */
    public function updateUser($userId, array $data) {
        $this->checkRequired($data, []);

        $values = $this->apiRename($data, [
            'externalId' => 'externalId',
            'email' => 'email',
            'firstName' => 'userFirstName',
            'lastName' => 'userLastName',
        ]);

        $users = $this->createGenericDocument();
        $users->request('POST', $this->endpoint_url . '/user/edit/' . $userId, $values);

        return new \Bpi\Sdk\Item\User($users);
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $adminId
     */
    public function deleteUser($userId) {
        $users = $this->createGenericDocument();
        $users->request('DELETE', $this->endpoint_url . '/user/remove/' . $userId);

        return !$users->status()->isError();
    }

    protected function checkRequired(array $data, array $required) {
        foreach ($required as $name) {
            if (!isset($data[$name])) {
                throw new \InvalidArgumentException(sprintf('Field [%s] is required', (string)$name));
            }
        }
    }

    protected function apiRename(array $data, array $apiNames) {
        $values = [];
        foreach ($data as $name => $value) {
            if (isset($apiNames[$name])) {
                $values[$apiNames[$name]] = $value;
            }
        }
        return $values;
    }
}
