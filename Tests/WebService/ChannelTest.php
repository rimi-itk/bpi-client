<?php

namespace Bpi\Sdk\Tests\WebService;

class ChannelTest extends WebServiceTestCase
{
    protected $admin;

    public function setUp()
    {
        parent::setUp();
        $this->admin = $this->client->createUser([ 'externalId' => mt_rand(), 'email' => mt_rand() . '-admin@example.com' ]);
    }

    public function testCanCreateChannel()
    {
        $channels = $this->searchChannels();
        $numberOfChannels = count($channels);

        $channel = $this->client->createChannel([ 'name' => $this->getChannelName(), 'description' => 'test', 'adminId' => $this->admin->getId() ]);

        $channels = $this->searchChannels();
        $newNumberOfChannels = count($channels);

        $this->assertEquals($numberOfChannels + 1, $newNumberOfChannels);
    }

    public function testCanLimitChannels()
    {
        $this->client->createChannel([ 'name' => $this->getChannelName(), 'description' => 'test', 'adminId' => $this->admin->getId() ]);
        $this->client->createChannel([ 'name' => $this->getChannelName(), 'description' => 'test', 'adminId' => $this->admin->getId() ]);

        $channels = $this->searchChannels([
        'amount' => 1,
        ]);

        $this->assertEquals(1, count($channels));
    }

    public function testCanGetChannel()
    {
        $newChannel = $this->client->createChannel([ 'name' => $this->getChannelName(), 'description' => 'test', 'adminId' => $this->admin->getId() ]);

        $channel = $this->client->getChannel($newChannel->getId());

        // $this->assertEquals($newChannel->getAdminId(), $this->admin->getId());
        // @FIXME
        $newChannel->setAdmin($channel->getAdmin());
        $channel->setAdminId($newChannel->getAdminId());

        $this->assertEquals($newChannel->getName(), $channel->getName());
    }

    public function testCanUpdateChannel()
    {
        $channel = $this->client->createChannel([ 'name' => $this->getChannelName(), 'description' => 'test', 'adminId' => $this->admin->getId() ]);

        $name = $channel->getName() . mt_rand();
        $description = $channel->getDescription() . mt_rand();
        $updatedChannel = $this->client->updateChannel($channel->getId(), [ 'name' => $name, 'description' => $description ]);

        $this->assertEquals($name, $updatedChannel->getName());
        $this->assertEquals($description, $updatedChannel->getDescription());
    }

    public function testCanDeleteChannel()
    {
        $channel = $this->client->createChannel([ 'name' => $this->getChannelName(), 'description' => 'test', 'adminId' => $this->admin->getId() ]);

        $channels = $this->searchChannels();
        $numberOfChannels = count($channels);

        $result = $this->client->deleteChannel($channel->getId());

        $this->assertEquals(true, $result);

        $channels = $this->searchChannels();
        $this->assertEquals($numberOfChannels - 1, count($channels));
    }

    public function testCanAddEditorToChannel()
    {
        $channel = $this->client->createChannel([ 'name' => $this->getChannelName(), 'description' => 'test', 'adminId' => $this->admin->getId() ]);
        $channel = $this->client->getChannel($channel->getId());

        $numberOfEditors = count($channel->getEditors());
        $editor = $this->client->createUser([ 'externalId' => mt_rand(), 'email' => mt_rand() . '-editor@example.com' ]);

        $result = $this->client->addEditorToChannel($channel->getId(), $channel->getAdmin()->getId(), $editor->getId());

        $this->assertEquals(true, $result);

        $channel = $this->client->getChannel($channel->getId());

        $this->assertEquals($numberOfEditors + 1, count($channel->getEditors()));
    }

    public function testCanRemoveEditorFromChannel()
    {
        $channel = $this->client->createChannel([ 'name' => $this->getChannelName(), 'description' => 'test', 'adminId' => $this->admin->getId() ]);
        $editor = $this->client->createUser([ 'externalId' => mt_rand(), 'email' => mt_rand() . '-editor@example.com' ]);

        $result = $this->client->addEditorToChannel($channel->getId(), $this->admin->getId(), $editor->getId());

        $this->assertEquals(true, $result, 'Can add editor to channel');

        $channel = $this->client->getChannel($channel->getId());
        $numberOfEditors = count($channel->getEditors());

        $this->assertEquals(1, $numberOfEditors, 'Number of editors are 1');

        $result = $this->client->removeEditorFromChannel($channel->getId(), $this->admin->getId(), $editor->getId());

        $this->assertEquals(true, $result, 'Can remove editor from channel');

        $channel = $this->client->getChannel($channel->getId());

        $this->assertEquals($numberOfEditors - 1, count($channel->getEditors()), 'Number of editors are decreased by 1');
    }

    public function testCanGetUsersChannels()
    {
        $user = $this->client->createUser([ 'externalId' => mt_rand(), 'email' => mt_rand() . '-user@example.com' ]);

        $channels = $this->client->getChannelsByUser($user->getId());
        $this->assertEquals(0, count($channels));

        $channel = $this->client->createChannel([ 'name' => $this->getChannelName(), 'description' => 'test', 'adminId' => $this->admin->getId() ]);
        $channel = $this->client->getChannel($channel->getId());

        $result = $this->client->addEditorToChannel($channel->getId(), $channel->getAdmin()->getId(), $user->getId());

        $this->assertEquals(true, $result);

        $channels = $this->client->getChannelsByUser($user->getId());
        $this->assertEquals(1, count($channels));
    }

    public function testCanAddNodeToChannel()
    {
        $channel = $this->client->createChannel([ 'name' => $this->getChannelName(), 'description' => 'test', 'adminId' => $this->admin->getId() ]);
        $channel = $this->client->getChannel($channel->getId());

        $numberOfNodes = count($channel->getNodes());

        $this->assertEquals(0, $numberOfNodes);

        $nodeId = $this->createNode();

        $result = $this->client->addNodeToChannel($channel->getId(), $this->admin->getId(), $nodeId);

        $this->assertEquals(true, $result);

        $channel = $this->client->getChannel($channel->getId());

        $this->assertEquals($numberOfNodes + 1, count($channel->getNodes()));
        $this->assertNotNull($channel->getNodeLastAddedAt());
    }

    private function createNode(array $data = [])
    {
        $data = array_merge([
        'title' => $this->getRandomName(),
        'body' => '',
        'teaser' => '',
        'type' => 'test',
        'creation' => date('c'),
        'category' => 'Other',
        'audience' => 'All',
        'editable' => 1,
        'authorship' => '',
        'agency_id' => $this->agencyId,
        'local_id' => 87,
        'firstname' => 'test',
        'lastname' => '',
        'assets' => [],
        'related_materials' => [],
        'tags' => 'test',
            'url' => '',
            'data' => '',
        ], $data);
        $node = $this->client->push($data);

        if (!$node) {
            throw new \Exception('Cannot create node');
        }

        $properties = $node->getProperties();
        return $properties['id'];
    }

    public function testCanRemoveNodeFromChannel()
    {
        $channel = $this->client->createChannel([ 'name' => $this->getChannelName(), 'description' => 'test', 'adminId' => $this->admin->getId() ]);

        $nodeId = $this->createNode();

        $result = $this->client->addNodeToChannel($channel->getId(), $this->admin->getId(), $nodeId);

        $this->assertEquals(true, $result);

        $channel = $this->client->getChannel($channel->getId());
        $numberOfNodes = count($channel->getNodes());

        $this->assertEquals(1, $numberOfNodes);

        $result = $this->client->removeNodeFromChannel($channel->getId(), $this->admin->getId(), $nodeId);

        $this->assertEquals(true, $result);

        $channel = $this->client->getChannel($channel->getId());

        $this->assertEquals($numberOfNodes - 1, count($channel->getNodes()));
    }

    public function testCanSearchByText()
    {
        $channels = $this->searchChannels([
            'search' => uniqid(),
        ]);

        $this->assertEquals(0, count($channels));
    }

    public function testCanSortByName()
    {
        $this->canSortBy('name');
    }

    public function testCanSortByDescription()
    {
        $this->canSortBy('description');
    }

    public function testCanSortByNodeLastAddedAt()
    {
        $this->canSortBy('nodeLastAddedAt');
    }

    private function canSortBy($name)
    {
        $channels = $this->searchChannels([
            'sort' => [
                $name => 'asc',
            ],
        ]);
        $reversedChannels = $this->searchChannels([
            'sort' => [
                $name => 'desc',
            ],
        ]);

        $channels = iterator_to_array($channels);
        $reversedChannels = iterator_to_array($reversedChannels);

        $this->assertTrue(count($channels) > 1);
        $this->assertTrue(count($reversedChannels) > 1);
        $this->assertEquals(count($channels), count($reversedChannels));
        $this->assertNotEquals($channels, $reversedChannels);

        // for ($i = 0; $i < count($channels); $i++) {
        //	$this->assertEquals($channels[$i], $reversedChannels[count($reversedChannels) - 1 - $i]);
        // }
    }

    private function getChannelName()
    {
        return 'Channel ' . mt_rand();
    }

    // public function testChannels()
    // {
    //     $channels = $this->searchChannels();
    //     $this->assertNotNull($channels);
    // }

    // public function testCanCreateChannel()
    // {
    //     $channels = $this->searchChannels();
    //     $numberOfChannels = count($channels);

    //     $data = [
    //         'name' => uniqid('test'),
    //         'description' => uniqid('test'),
    //         'adminId' => uniqid('test'),
    //     ];

    //     $channel = $this->client->createChannel($data);

    //     $this->assertEquals($data['email'], $channel->getEmail());
    //     $this->assertEquals($data['firstName'], $channel->getFirstName());

    //     $channels = $this->searchChannels();
    //     $newNumberOfChannels = count($channels);

    //     $this->assertEquals($numberOfChannels + 1, $newNumberOfChannels);
    // }

    // public function testCanDeleteChannel()
    // {
    //     $channels = $this->searchChannels();
    //     $numberOfChannels = count($channels);

    //     $this->assertTrue(count($channels) > 0);

    //     $channels->rewind();
    //     $channel = $channels->current();
    //     $result = $this->client->deleteChannel($channel->getId());
    //     $this->assertTrue($result);

    //     $result = $this->client->deleteChannel(uniqid());
    //     $this->assertFalse($result);

    //     $channels = $this->searchChannels();
    //     $newNumberOfChannels = count($channels);

    //     $this->assertEquals($numberOfChannels - 1, $newNumberOfChannels);
    // }

    private function searchChannels(array $query = [])
    {
        $query += ['amount' => 1000];
        return $this->client->searchChannels($query);
    }
}
