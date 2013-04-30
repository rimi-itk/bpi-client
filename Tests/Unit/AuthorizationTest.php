<?php
namespace Bpi\Sdk\Tests\Unit;

use Symfony\Component\DomCrawler\Crawler;
use Bpi\Sdk\Document;
use Bpi\Sdk\Authorization;

class AuthorizationTest extends \PHPUnit_Framework_TestCase
{
    public function testAuthorization()
    {
        $agency_id = mt_rand();
        $public_key = mt_rand();
        $secret_key = mt_rand();
        $authorization = new Authorization($agency_id, $public_key, $secret_key);

        $client = $this->getMock('Goutte\Client');
        $client->expects($this->at(0))
            ->method('request')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('http://example.com'),
                $this->equalTo(array()),
                $this->equalTo(array()),
                $this->equalTo(array(
                    'HTTP_Authorization' => $authorization->toHTTPHeader(),
                    'HTTP_Content_Type' => 'application/vnd.bpi.api+xml',
                ))
            )
            ->will($this->returnValue(new Crawler(file_get_contents(__DIR__ . '/Fixtures/Node.bpi'))));

        $doc = new Document($client, $authorization);
        $doc->request('GET', 'http://example.com');
    }
}
