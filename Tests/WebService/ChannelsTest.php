<?php

namespace Bpi\Sdk\Tests\WebService;

require_once __DIR__ . '/WebServiceTestCase.php';

class ChannelsTest extends WebServiceTestCase
{
    // public function testChannels()
    // {
    //     $channels = $this->client->searchChannels([]);
    //     $this->assertEquals(0, count($channels));
    // }

    public function testCanCreateChannel()
    {
        $channels = $this->client->searchChannels();
        $numberOfChannels = count($channels);

        $data = [
            'name' => uniqid('test'),
            'description' => uniqid('test'),
            'adminId' => uniqid('test'),
        ];

        $channel = $this->client->createChannel($data);

        $this->assertEquals($data['email'], $channel->getEmail());
        $this->assertEquals($data['firstName'], $channel->getFirstName());

        $channels = $this->client->searchChannels();
        $newNumberOfChannels = count($channels);

        $this->assertEquals($numberOfChannels + 1, $newNumberOfChannels);
    }

    public function testCanDeleteChannel()
    {
        $channels = $this->client->searchChannels();
        $numberOfChannels = count($channels);

        $this->assertTrue(count($channels) > 0);

        $channels->rewind();
        $channel = $channels->current();
        $result = $this->client->deleteChannel($channel->getId());
        $this->assertTrue($result);

        $result = $this->client->deleteChannel(uniqid());
        $this->assertFalse($result);

        $channels = $this->client->searchChannels();
        $newNumberOfChannels = count($channels);

        $this->assertEquals($numberOfChannels - 1, $newNumberOfChannels);
    }
}
