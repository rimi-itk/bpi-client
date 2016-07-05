<?php
namespace Bpi\Sdk;

use Bpi\Sdk\Item\Facet;
use Symfony\Component\DomCrawler\Crawler;

class GenericDocument extends Document {
    public $total;
    public $offset;
    public $amount;

    /**
     * {@inheritdoc}
     */
    public function request($method, $uri, array $params = array())
    {
        $headers = array(
            'HTTP_Auth' => $this->authorization->toHTTPHeader(),
            'HTTP_Content_Type' => 'application/vnd.bpi.api+xml',
        );

        $this->crawler = $this->http_client->request($method, $uri, $params, array(), $headers);
        $this->crawler->rewind();

        $this->total = $this->crawler->attr('total');
        $this->offset = $this->crawler->attr('offset');
        $this->amount = $this->crawler->attr('amount');

        if ($this->status()->isError())
        {
            if ($this->status()->isClientError()) {
                throw new Exception\HTTP\ClientError($this->http_client->getResponse()->getContent(), $this->status()->getCode());
            }

            if ($this->status()->isServerError()) {
                throw new Exception\HTTP\ServerError($this->http_client->getResponse()->getContent(), $this->status()->getCode());
            }

            throw new Exception\HTTP\Error($this->http_client->getResponse()->getContent(), $this->status()->getCode());
        }

        return $this;
    }

    /**
     * Filter elements by name
     *
     * @param string $name
     * @throws \Bpi\Sdk\Exception\EmptyList
     *
     * @return \Bpi\Sdk\GenericDocument same instance
     */
    public function reduceByName($name) {
        $this->crawler = $this->crawler->filter($name);

        if (!$this->crawler->count()) {
            throw new Exception\EmptyList(sprintf('No items remain after reduce was made by name [%s]', $name));
        }

        $this->crawler->rewind();
        return $this;
    }

    /**
     * Iterates over all elements matched by selector.
     */
    public function walkElements($selector, $callback)
    {
        $crawler = new Crawler($this->crawler->current());
        return $crawler->filter($selector)->each(function($e) use($callback) {
            $sxml = simplexml_import_dom($e);
            $callback($sxml);
        });
    }

    /*
     * Filter nodes by XPath expression.
     */
    public function filterXPath($xpath) {
        return $this->crawler->filterXPath($xpath);
    }

    public function setFacets() {
        $this->facets = new Facets();
        $this->walkElements('* > facet', function(\SimpleXMLElement $el) {
            $facet = new Facet();
            $facet->setFacetName((string)$el->attributes()->name);
            foreach ($el->term as $term) {
                $facet->addFacetTerm((string)$term->attributes()->name, (string)$term->attributes()->title, (string)$term);
            }
            $this->facets->addFacets($facet);
        });
    }
}
