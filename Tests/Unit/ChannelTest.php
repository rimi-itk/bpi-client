<?php

namespace Bpi\Sdk\Tests\Unit;

class ChannelTest extends UnitTestCase
{
    public function testGetChannels()
    {
        $client = $this->getClient(__DIR__ . '/Fixtures/GetChannels.response');

        $bpi = new BpiClient($client);
        $channels = $bpi->searchChannels([]);
        $this->assertEquals(11, $channels->total);
        $this->assertEquals(3, count($channels));

        $channels->rewind();
        $channel = $channels->current();
        $this->assertNotNull($channel);
        $this->assertEquals('Mikkels kanal', $channel->getName());

        $channels->next();
        $channel = $channels->current();
        $this->assertNotNull($channel);
        $this->assertEquals('Test kanal', $channel->getName());
    }

    public function testGetChannelsFacets()
    {
        $client = $this->getClient(__DIR__ . '/Fixtures/GetChannels.response');

        $bpi = new BpiClient($client);
        $channels = $bpi->searchChannels([]);
        $facets = $channels->getFacets()->getFacets();
        $this->assertEquals(1, count($facets));
        /** @var \Bpi\Sdk\Item\Facet $facet */
        $facet = $facets[0];
        $this->assertEquals('agency_id', $facet->getFacetName());

        $terms = $facet->getFacetTerms();
        $this->assertEquals(2, count($terms));

        $this->assertArrayHasKey('999999', $terms);
        $term = $terms['999999'];
        $this->assertEquals(2, $term->getAmount());
        $this->assertEquals('999999', $term->getName());
        $this->assertEquals('Test bibliotek', $term->getTitle());

        $this->assertArrayHasKey('775100', $terms);
        $term = $terms['775100'];
        $this->assertEquals(1, $term->getAmount());
        $this->assertEquals('775100', $term->getName());
        $this->assertEquals('Aarhus Kommunes Biblioteker', $term->getTitle());
    }
}
