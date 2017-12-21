<?php

namespace Bpi\Sdk\Tests\Unit;

class UserTest extends UnitTestCase
{
    public function testGetUsers()
    {
        $client = $this->getClient(__DIR__ . '/Fixtures/GetUsers.response');

        $bpi = new BpiClient($client);
        $users = $bpi->searchUsers([]);
        $this->assertEquals(87, $users->total);
        $this->assertEquals(3, count($users));

        $users->rewind();
        $user = $users->current();
        $this->assertNotNull($user);
        $this->assertEquals('user@999999.example.com', $user->getEmail());

        $users->next();
        $user = $users->current();
        $this->assertNotNull($user);
        $this->assertEquals('another.user@999999.example.com', $user->getEmail());

        $users->next();
        $user = $users->current();
        $this->assertNotNull($user);
        $this->assertEquals('user@775100.example.com', $user->getEmail());
    }

    public function testGetUsersFacets()
    {
        $client = $this->getClient(__DIR__ . '/Fixtures/GetUsers.response');

        $bpi = new BpiClient($client);
        $users = $bpi->searchUsers([]);
        $facets = $users->getFacets()->getFacets();
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
