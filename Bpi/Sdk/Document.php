<?php
namespace Bpi\Sdk;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class Document implements \Iterator, \Countable
{
    /**
     *
     * @var \Goutte\Client
     */
    protected $http_client;
    
    /**
     *
     * @var \Bpi\Sdk\Authorization
     */
    protected $authorization;

    /**
     *
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $crawler;
    
    /**
     *
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $iterator;
    
    /**
     *
     * @param \Goutte\Client $client
     * @param \Bpi\Sdk\Authorization $authorization
     */
    public function __construct(Client $client, Authorization $authorization)
    {
        $this->http_client = $client;
        $this->authorization = $authorization;
    }
    
    /**
     * @param string $endpoint API URL
     * @return \Bpi\Sdk\Document same instance
     */
    public function loadEndpoint($endpoint)
    {
        $this->request('GET', $endpoint);
        return $this;
    }

    /**
     * Gateway to make direct requests to API
     *
     * @param string $method
     * @param string $uri
     * @param array $params
     *
     * @return \Bpi\Sdk\Document same instance
     */
    public function request($method, $uri, array $params = array())
    {
        $headers = array(
            'HTTP_Authorization' => $this->authorization->toHTTPHeader(),
            'HTTP_Content_Type' => 'application/vnd.bpi.api+xml',
        );

        $this->crawler = $this->http_client->request($method, $uri, $params, array(), $headers);
        $this->rewind();

        return $this;
    }
    
    /**
     * Dump latest raw response data
     *
     * @return string
     */
    public function dumpRawResponse()
    {
        return $this->http_client->getResponse();
    }

    /**
     *
     * @return \Symfony\Component\DomCrawler\Crawler crawler copy
     */
    public function getCrawler()
    {
        return clone $this->crawler;
    }

    /**
     * Access hypermedia link.
     *
     * @throws Exception\UndefinedHypermedia
     * @param string $rel
     * @return \Bpi\Sdk\Link
     */
    public function link($rel)
    {
        try {
            $crawler = $this->crawler
                ->filter("hypermedia > link[rel='{$rel}']")
                ->first()
            ;

            return new Link($crawler);
        }
        catch (\InvalidArgumentException $e)
        {
            throw new Exception\UndefinedHypermedia();
        }
    }
    
    /**
     * Click on link.
     *
     * @param \Bpi\Sdk\Link $link
     */
    public function followLink(Link $link)
    {
        $link->follow($this);
    }
    
    /**
     * Access hypermedia query.
     *
     * @throws Exception\UndefinedHypermedia
     * @param string $rel
     * @return \Bpi\Sdk\Query
     */
    public function query($rel)
    {
        try
        {
            $query = $this->crawler
                  ->filter("hypermedia > query[rel='{$rel}']")
                  ->first()
            ;

            return new Query($query);
        }
        catch (\InvalidArgumentException $e)
        {
            throw new Exception\UndefinedHypermedia();
        }
    }

    /**
     * Send query.
     * 
     * @param \Bpi\Sdk\Query $query
     * @param array $params
     */
    public function sendQuery(Query $query, $params)
    {
        $query->send($this, $params);
    }

    /**
     * Access hypermedia template.
     *
     * @throws Exception\UndefinedHypermedia
     * @param string $rel
     * @return \Bpi\Sdk\Template
     */
    public function template($rel)
    {
        try
        {
            $query = $this->crawler
                  ->filter("hypermedia > template[rel='{$rel}']")
                  ->first()
            ;

            return new Template($query);
        }
        catch (\InvalidArgumentException $e)
        {
            throw new Exception\UndefinedHypermedia();
        }
    }

    /**
     * Post rendered template.
     *
     * @param \Bpi\Sdk\Template $template
     */
    public function postTemplate(Template $template)
    {
        $template->post($this);
    }

    /**
     * Checks current item type
     * 
     * @param string $type
     * @return bool
     */
    public function isTypeOf($type)
    {
        return $this->iterator->current()->getAttribute('type') == $type;
    }

    /**
     * Iterates over all properties of current item
     */
    public function walkProperties($callback)
    {
        $crawler = new Crawler($this->iterator->current());
        return $crawler->filter('item property')->each(function($e) use($callback) {
            $sxml = simplexml_import_dom($e);
            $callback(current($sxml->attributes()) + array('@value' => (string) $sxml));
        });
    }

    /**
     * Finds first matched item by attribute value
     *
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException
     *
     * @return \Bpi\Sdk\Document same instance
     */
    public function firstItem($attr, $value) {
        $this->iterator = $this->crawler
            ->filter("item[$attr='{$value}']")
            ->first()
        ;

        if (!$this->iterator->count())
            throw new \InvalidArgumentException(sprintf('Item with attribute %s and value %s was not found', $attr, $value));

        $this->iterator->rewind();
        return $this;
    }

    /**
     * Filter items (<item> tags) by attribute values
     *
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException
     *
     * @return \Bpi\Sdk\Document same instance
     */
    public function reduceItemsByAttr($attr, $value) {
        $this->iterator = $this->crawler
            ->filter("item[$attr='{$value}']")
        ;

        if (!$this->iterator->count())
            throw new \InvalidArgumentException();

        $this->iterator->rewind();
        return $this;
    }

    /**
     * Iterator interface implementation
     * 
     * @group Iterator
     */
    function rewind() 
    {
        $this->iterator = $this->crawler->filter('bpi > item');
        $this->iterator->rewind();
    }

    /**
     * Returns same instance but with internal pointer to current item in collection
     * 
     * @group Iterator
     * @return \Bpi\Sdk\Document will return same instance
     */
    function current() 
    {
        return $this;
    }

    /**
     * Key of current iteration position
     * 
     * @group Iterator
     */
    function key() 
    {
        return $this->iterator->key();
    }

    /**
     * Iterate to next item
     * 
     * @group Iterator
     */
    function next() 
    {
        $this->iterator->next();
    }

    /**
     * Checks if is ready for iteration
     * 
     * @group Iterator
     * @return boolean
     */
    function valid() 
    {
        return $this->iterator->valid();
    }
    
    /**
     * Length of items in document
     * 
     * @group Iterator
     */
    public function count()
    {
        return $this->iterator->count();
    }
}
