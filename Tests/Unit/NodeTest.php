<?php

namespace Bpi\Sdk\Tests\Unit;

class NodeTest extends UnitTestCase
{
    public function testGetNodes()
    {
        $client = $this->getClient(__DIR__ . '/Fixtures/GetNodes.response');

        $bpi = new BpiClient($client);
        $nodelist = $bpi->searchNodes([]);
        $this->assertEquals(3, count($nodelist));
        $this->assertEquals(2948, $nodelist->total);

        $nodelist->rewind();
        $node = $nodelist->current();
        $properties = $node->getProperties();
        $this->assertEquals('Vintertid er læsetid', $properties['title']);
    }

    public function testGetNodesFacets()
    {
        $client = $this->getClient(__DIR__ . '/Fixtures/GetNodes.response');

        $bpi = new BpiClient($client);
        $nodelist = $bpi->searchNodes([]);
        $facets = $nodelist->getFacets()->getFacets();
        $this->assertEquals(5, count($facets));
        /** @var \Bpi\Sdk\Item\Facet $facet */
        $facet = $facets[0];
        $this->assertEquals('category', $facet->getFacetName());

        $terms = $facet->getFacetTerms();
        $this->assertEquals(20, count($terms));
        $this->assertArrayHasKey('Film', $terms);

        /** @var \Bpi\Sdk\Item\FacetTerm $term */
        $term = $terms['Film'];
        $this->assertEquals(122, $term->getAmount());
        $this->assertEquals('Film', $term->getName());
        $this->assertEquals('Film', $term->getTitle());
    }
}
