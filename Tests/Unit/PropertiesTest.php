<?php
namespace Bpi\Sdk\Tests\Unit;

use Symfony\Component\DomCrawler\Crawler;
use Bpi\Sdk\Document;
use Bpi\Sdk\Authorization;

class PropertiesTest extends \PHPUnit_Framework_TestCase
{
    protected function createMockDocument($fixture)
    {
        $client = $this->getMock('Goutte\Client');
        $client->expects($this->at(0))
              ->method('request')
              ->will($this->returnValue(new Crawler(file_get_contents(__DIR__ . '/Fixtures/' . $fixture . '.bpi'))));
        
        $doc = new Document($client, new Authorization(mt_rand(), mt_rand(), mt_rand()));
        $doc->loadEndpoint('http://example.com');
        return $doc;
    }

    public function testWalkProperties()
    {
        $doc = $this->createMockDocument('Node');
        $properties = array();
        $doc->walkProperties(function($e) use(&$properties) {
            $properties[] = $e;
        });

        $this->assertEquals('title', $properties[0]['name']);
        $this->assertEquals('TITLE', $properties[0]['@value']);
        $this->assertEquals('teaser', $properties[1]['name']);
        $this->assertEquals('TEASER', $properties[1]['@value']);
    }
    
    public function testGetPropertiesFromCollection()
    {
        $doc = $this->createMockDocument('Collection');

        $i = 0;
        foreach ($doc as $item)
        {
            if ($i == 0)
            {
                // collection
                $properties = array();
                $doc->walkProperties(function($e) use($properties) {
                    $properties[] = $e;
                });
                $this->assertEmpty($properties);
            }
            elseif ($i == 1)
            {
                // entity
                $properties = array();
                $doc->walkProperties(function($e) use(&$properties) {
                    $properties[] = $e;
                });

                $this->assertEquals('title', $properties[0]['name']);
                $this->assertEquals('COLLECTION_TITLE', $properties[0]['@value']);
                $this->assertEquals('teaser', $properties[1]['name']);
                $this->assertEquals('COLLECTION_TEASER', $properties[1]['@value']);
            }
            else 
                $this->fail('Unexpected');

            $i++;
        }
    }
}
