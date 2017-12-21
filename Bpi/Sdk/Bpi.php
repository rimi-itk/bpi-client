<?php

use Bpi\Sdk\Authorization;
use Bpi\Sdk\ChannelList;
use Bpi\Sdk\Exception\SDKException;
use Bpi\Sdk\GroupOperationResult;
use Bpi\Sdk\Item\BaseItem;
use Bpi\Sdk\Item\Channel;
use Bpi\Sdk\Item\Node;
use Bpi\Sdk\Item\User;
use Bpi\Sdk\NodeList;
use Bpi\Sdk\UserList;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\ClientException as HttpClientException;

/**
 * TODO please add a general description about the purpose of this class.
 */
// @codingStandardsIgnoreLine
class Bpi
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     *
     * @var \Bpi\Sdk\Authorization
     */
    protected $authorization;

    /**
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Create Bpi Client
     *
     * @param string $endpoint URL
     * @param string $agencyId Agency ID
     * @param string $publicKey App key
     * @param string $secret
     */
    public function __construct($endpoint, $agencyId, $publicKey, $secret)
    {
        $this->endpoint = $endpoint;
        $this->authorization = new Authorization($agencyId, $publicKey, $secret);
    }

    protected function request($method, $url, array $data = [])
    {
        try {
            $this->client = new GuzzleHttpClient([
                'base_uri' => $this->endpoint,
                'headers' => [
                    'Auth' => $this->authorization->toHTTPHeader(),
                ],
            ]);
            $result = $this->client->request($method, $url, $data);
            return $result;
        } catch (GuzzleClientException $e) {
            throw new SDKException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get list of node based on some conditions
     *
     * @param array $queries available keys are: amount, offset, filter, sort
     *   filter and sort requires nested arrays
     * @return \Bpi\Sdk\NodeList
     */
    public function searchNodes(array $query = array())
    {
        $result = $this->request('GET', 'node/collection', [
            'query' => $query,
        ]);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new NodeList($element);
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
        $result = $this->request('POST', 'node', ['form_params' => $data]);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new Node($element->item[0]);
    }

    /**
     * Mark node as syndicated
     *
     * @param string $id BPI node ID
     * @return boolean operation status
     */
    public function syndicateNode($id)
    {
        $result = $this->request('GET', 'node/syndicated', ['query' => ['id' => $id]]);

        return $result->getStatusCode() === 200;
    }

    /**
     * Mark node as deleted
     *
     * @param string $id BPI node ID
     * @return boolean operation status
     */
    public function deleteNode($id)
    {
        $result = $this->request('GET', 'node/delete', ['query' => ['id' => $id]]);

        return $result->getStatusCode() === 200;
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
        $result = $this->request('GET', 'statistics', ['query' => ['dateFrom' => $dateFrom, 'dateTo' => $dateTo]]);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new BaseItem($element->item[0]);
    }

    /**
     * Get single Node by ID
     *
     * @param string $id BPI node ID
     * @return \Bpi\Sdk\Item\Node
     */
    public function getNode($id)
    {
        $result = $this->request('GET', 'node/item/' . $id);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new Node($element->item[0]);
    }

    /**
     * Get list of dictionaries
     *
     * @return array
     */
    public function getDictionaries()
    {
        $result = $this->request('GET', 'profile/dictionary');
        $element = new \SimpleXMLElement((string)$result->getBody());

        $dictionary = [];
        foreach ($element->xpath('/bpi/item') as $item) {
            $group = (string)$item->xpath('properties/property[@name = "group"]')[0];
            $name = (string)$item->xpath('properties/property[@name = "name"]')[0];
            if (!isset($dictionary[$group])) {
                $dictionary[$group] = [];
            }
            $dictionary[$group][] = $name;
        }

        return $dictionary;
    }

    // -----------------------------------------------------------------------------

    /**
     * Get list of channels.
     *
     * @param array $query available keys are: amount, offset, filter, sort
     *   filter and sort requires nested arrays
     * @return \Bpi\Sdk\NodeList
     */
    public function searchChannels($query = [])
    {
        $result = $this->request('GET', 'channel/', [
            'query' => $query,
        ]);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new ChannelList($element);
    }

    /**
     * Create a new channel.
     *
     * @param string $name
     * @param string $description
     * @param string $adminId
     */
    public function createChannel(array $data)
    {
        $this->checkRequired($data, ['name', 'description', 'adminId']);
        $values = $this->apiRename($data, [
            'name' => 'name',
            'description' => 'channelDescription',
            'adminId' => 'editorId',
        ]);

        $result = $this->request('POST', 'channel/', ['form_params' => $values]);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new Channel($element->channel[0]);
    }

    /**
     * Get a channel by Id.
     *
     * @param string $id
     *   The channel id.
     *
     * @return Channel
     *   The channel if found.
     */
    public function getChannel($id)
    {
        $result = $this->request('GET', 'channel/' . $id);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new Channel($element->channel[0]);
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $adminId
     */
    public function updateChannel($channelId, array $data)
    {
        $this->checkRequired($data, [ 'name', 'description' ]);

        $values = $this->apiRename($data, [
            'name' => 'channelName',
            'description' => 'channelDescription',
        ]);

        $result = $this->request('POST', 'channel/edit/' . $channelId, ['form_params' => $values]);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new Channel($element->channel[0]);
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $adminId
     */
    public function deleteChannel($channelId)
    {
        $result = $this->request('DELETE', 'channel/remove/' . $channelId);

        return $result->getStatusCode() === 200;
    }

    public function addEditorToChannel($channelId, $adminId, $editorIds)
    {
        if (!is_array($editorIds)) {
            $editorIds = [ $editorIds ];
        }
        $values = [
            'channelId' => $channelId,
            'adminId' => $adminId,
            'users' => array_map(function ($editorId) {
                return [ 'editorId' => $editorId ];
            }, $editorIds),
        ];

        $result = $this->request('POST', 'channel/add/editor', ['form_params' => $values]);

        if ($result->getStatusCode() !== 200) {
            return false;
        }

        $element = new \SimpleXMLElement((string)$result->getBody());
        $result = new GroupOperationResult($element);

        return count($result->getSuccessIds()) === count($editorIds);
    }

    public function removeEditorFromChannel($channelId, $adminId, $editorIds)
    {
        if (!is_array($editorIds)) {
            $editorIds = [ $editorIds ];
        }
        $values = [
            'channelId' => $channelId,
            'adminId' => $adminId,
            'users' => array_map(function ($editorId) {
                return [ 'editorId' => $editorId ];
            }, $editorIds),
        ];

        $result = $this->request('POST', 'channel/remove/editor', ['form_params' => $values]);

        if ($result->getStatusCode() !== 200) {
            return false;
        }

        $element = new \SimpleXMLElement((string)$result->getBody());
        $result = new GroupOperationResult($element);

        return count($result->getSuccessIds()) === count($editorIds);
    }

    public function getChannelsByUser($userId)
    {
        $result = $this->request('GET', 'channel/user/' . $userId);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new ChannelList($element);
    }

    public function addNodeToChannel($channelId, $editorId, $nodeIds)
    {
        if (!is_array($nodeIds)) {
            $nodeIds = [ $nodeIds ];
        }
        $values = [
            'channelId' => $channelId,
            'editorId' => $editorId,
            'nodes' => array_map(function ($nodeId) {
                return [ 'nodeId' => $nodeId ];
            }, $nodeIds),
        ];

        $result = $this->request('POST', 'channel/add/node', ['form_params' => $values]);

        if ($result->getStatusCode() !== 200) {
            return false;
        }

        $element = new \SimpleXMLElement((string)$result->getBody());
        $result = new GroupOperationResult($element);

        return count($result->getSuccessIds()) === count($nodeIds);
    }

    public function removeNodeFromChannel($channelId, $editorId, $nodeIds)
    {
        if (!is_array($nodeIds)) {
            $nodeIds = [ $nodeIds ];
        }
        $values = [
            'channelId' => $channelId,
            'editorId' => $editorId,
            'nodes' => array_map(function ($nodeId) {
                return [ 'nodeId' => $nodeId ];
            }, $nodeIds),
        ];

        $result = $this->request('POST', 'channel/remove/node', ['form_params' => $values]);

        if ($result->getStatusCode() !== 200) {
            return false;
        }

        $element = new \SimpleXMLElement((string)$result->getBody());
        $result = new GroupOperationResult($element);

        return count($result->getSuccessIds()) === count($nodeIds);
    }

    // -----------------------------------------------------------------------------

    /**
     * Get list of users based on conditions
     *
     * @param array $queries available keys are: search, amount, offset, filter, sort
     *   filter and sort requires nested arrays
     * @return \Bpi\Sdk\UserList
     */
    public function searchUsers($query = [])
    {
        $result = $this->request('GET', 'user/', [
            'query' => $query,
        ]);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new UserList($element);
    }

    public function createUser(array $data)
    {
        $this->checkRequired($data, [ 'externalId', 'email' ]);

        $values = $this->apiRename($data, [
            'externalId' => 'externalId',
            'email' => 'email',
            'firstName' => 'userFirstName',
            'lastName' => 'userLastName',
        ]);

        $result = $this->request('POST', 'user/', ['form_params' => $values]);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new User($element->user[0]);
    }

    public function getUser($id)
    {
        $result = $this->request('GET', 'user/' . $id);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new User($element->user[0]);
    }

    /**
     * @param string $userId
     * @param array $data
     */
    public function updateUser($userId, array $data)
    {
        $this->checkRequired($data, []);

        $values = $this->apiRename($data, [
            'externalId' => 'externalId',
            'email' => 'email',
            'firstName' => 'userFirstName',
            'lastName' => 'userLastName',
        ]);

        $result = $this->request('POST', 'user/edit/' . $userId, ['form_params' => $values]);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new User($element->user[0]);
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $adminId
     */
    public function deleteUser($userId)
    {
        throw new \Exception(__METHOD__ . ' not supported');
    }

    /**
     * @param string $userId
     * @param array $data
     *
     * @return Subscription
     */
    public function createSubscription($userId, array $data)
    {
        $this->checkRequired($data, [ 'title', 'filter' ]);

        $values = $data;
        $values['filter'] = json_encode($values['filter']);
        $values['userId'] = $userId;

        $result = $this->request('POST', 'user/subscription', ['form_params' => $values]);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new User($element->user[0]);
    }

    /**
     * @param string $userId
     * @param string $title
     */
    public function deleteSubscription($userId, $title)
    {
        $values = [
            'userId' => $userId,
            'subscriptionTitle' => $title,
        ];

        $result = $this->request('POST', 'user/subscription/remove', ['form_params' => $values]);
        $element = new \SimpleXMLElement((string)$result->getBody());

        return new User($element);
    }

    protected function checkRequired(array $data, array $required)
    {
        foreach ($required as $name) {
            if (!isset($data[$name])) {
                throw new \InvalidArgumentException(sprintf('Field [%s] is required', (string)$name));
            }
        }
    }

    protected function apiRename(array $data, array $apiNames)
    {
        $values = [];
        foreach ($data as $name => $value) {
            if (isset($apiNames[$name])) {
                $values[$apiNames[$name]] = $value;
            }
        }
        return $values;
    }
}
