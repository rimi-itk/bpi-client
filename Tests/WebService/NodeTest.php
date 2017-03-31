<?php

namespace Bpi\Sdk\Tests\WebService;

class NodeTest extends WebServiceTestCase
{
    public function testNodes()
    {
        $nodes = $this->searchNodes();
        $this->assertNotNull($nodes);
    }

    public function testCanCreateNode()
    {
        $nodes = $this->searchNodes();
        $numberOfNodes = count($nodes);

        $data = [
            'title' => uniqid(__METHOD__),
        ];

        $node = $this->createNode($data);

        $this->assertEquals($data['title'], $node->getProperties()['title']);

        $nodes = $this->searchNodes();
        $newNumberOfNodes = count($nodes);

        $this->assertEquals($numberOfNodes + 1, $newNumberOfNodes);

        $nodes = $this->searchNodes(['search' => $data['title']]);
        $this->assertEquals(1, count($nodes));
        $nodes->rewind();
        $this->assertEquals($data['title'], $nodes->current()->getProperties()['title']);
    }

    public function testCanCreateNodeWithAssets()
    {
        $nodes = $this->searchNodes();
        $numberOfNodes = count($nodes);

        $data = [
            'title' => uniqid(__METHOD__),

            'assets' => [
                [
                    'path' => 'https://placekitten.com/200/400',
                    'name' => 'Kittens',
                    'alt' => 'Kittens',
                    'title' => 'Kittens',
                    'type' => 'content_image',
                ],
                [
                    'path' => 'https://placekitten.com/400/200',
                    'name' => 'MoreKittens',
                    'alt' => 'More kittens',
                    'title' => 'More kittens',
                    'type' => 'content_image',
                ],
                [
                    'path' => 'https://placekitten.com/400/200',
                    'name' => 'Kittens',
                    'alt' => 'Kittens',
                    'title' => 'Kittens',
                    'type' => 'decorative_image',
                ],
            ],
        ];

        $node = $this->createNode($data);

        $nodes = $this->searchNodes();
        $newNumberOfNodes = count($nodes);

        $this->assertEquals($numberOfNodes + 1, $newNumberOfNodes);

        $this->assertEquals($data['title'], $node->getProperties()['title']);

        $assets = $node->getAssets();
        $this->assertEquals(2, count($assets));
        $this->assertArrayHasKey('content_image', $assets);
        $images = $assets['content_image'];
        $this->assertEquals(2, count($images));
        $this->assertEquals('Kittens', $images[0]['title']);
        $this->assertEquals('More kittens', $images[1]['title']);

        $this->assertArrayHasKey('decorative_image', $assets);
        $images = $assets['decorative_image'];
        $this->assertEquals(1, count($images));
    }

    public function testCanLimitNodes()
    {
        $this->createNode();
        $this->createNode();

        $nodes = $this->searchNodes();
        $this->assertTrue(count($nodes) > 1);

        $nodes = $this->searchNodes([
            'amount' => 1,
        ]);

        $this->assertEquals(1, count($nodes));
    }

    public function testCanGetNode()
    {
        $data = [
            'title' => uniqid(__METHOD__),
        ];
        $newNode = $this->createNode($data);
        $this->assertNotEmpty($newNode->getProperties());
        $node = $this->client->getNode($newNode->getProperties()['id']);

        $this->assertEquals($data['title'], $node->getProperties()['title']);
        $this->assertEquals($newNode->getProperties()['title'], $node->getProperties()['title']);
    }

    public function testCanGetFacets()
    {
        $nodes = $this->searchNodes();
        $facets = $nodes->getFacets()->getFacets();

        $this->createNode(['category' => 'Book']);
        $this->createNode(['category' => 'Event']);
        $this->createNode(['category' => 'Film']);

        $newFacets = $nodes->getFacets()->getFacets();

        $this->assertEquals(count($facets), count($newFacets));
    }

    public function testCanFilterOnFacet()
    {
        $nodes = $this->searchNodes();
        $facets = $nodes->getFacets()->getFacets();

        if ($facets) {
            foreach ($facets as $facet) {
                $facetName = $facet->getFacetName();
                foreach ($facet->getFacetTerms() as $term) {
                    $nodes = $this->searchNodes([
                        'filter' => [
                            $facetName => [
                                $term->getName(),
                            ],
                        ]
                    ]);
                    $this->assertEquals($nodes->total, $term->getAmount(), $facetName . ': ' . $term->getName());
                }
            }
        }
    }

    public function testCanSyndicateNode()
    {
        // $node = $this->createNode();
        // $properties = $node->getProperties();
        // $actual = $this->client->syndicateNode($properties['id']);
        // $this->assertTrue($actual);
    }

    public function testCanDeleteNode()
    {
        $node = $this->createNode();

        $nodes = $this->searchNodes();
        $numberOfNodes = count($nodes);

        $result = $this->client->deleteNode($node->getProperties()['id']);

        $this->assertEquals(true, $result);

        $nodes = $this->searchNodes();
        $this->assertEquals($numberOfNodes - 1, count($nodes));
    }

    public function testCanSearchByText()
    {
        $nodes = $this->searchNodes([
            'search' => uniqid(__METHOD__),
        ]);

        $this->assertEquals(0, count($nodes));
    }

    public function testCanSortByTitle()
    {
        $this->canSortBy('title');
    }

    public function testCanSortByPushed()
    {
        $this->canSortBy('pushed');
    }

    public function testCanSortBySyndications()
    {
        $this->canSortBy('syndications');
    }

    public function testCanGetStatistics()
    {
        $statistics = $this->client->getStatistics('2000-01-01', '2100-01-01');
        $properties = $statistics->getProperties();

        $this->assertArrayHasKey('push', $properties);
        $this->assertArrayHasKey('syndicate', $properties);
    }

    public function testCanGetDictionaries()
    {
        $dictionaries = $this->client->getDictionaries();
        $this->assertTrue(is_array($dictionaries));
        $this->assertArrayHasKey('audience', $dictionaries);
        $this->assertArrayHasKey('category', $dictionaries);
    }

    private function canSortBy($name)
    {
        $nodes = $this->searchNodes([
            'sort' => [
                $name => 'asc',
            ],
        ]);
        $reversedNodes = $this->searchNodes([
            'sort' => [
                $name => 'desc',
            ],
        ]);

        $nodes = iterator_to_array($nodes);
        $reversedNodes = iterator_to_array($reversedNodes);

        $this->assertTrue(count($nodes) > 1);
        $this->assertTrue(count($reversedNodes) > 1);
        $this->assertEquals(count($nodes), count($reversedNodes));
        // $this->assertNotEquals($nodes, $reversedNodes);
    }

    private function createNode(array $data = [])
    {
        $data += [
            'title' => uniqid(__METHOD__),
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
        ];

        $node = $this->client->push($data);

        if (!$node) {
            throw new \Exception('Cannot create node');
        }

        return $node;
    }

    private function searchNodes(array $query = [])
    {
        $query += ['amount' => 1000];
        return $this->client->searchNodes($query);
    }
}
