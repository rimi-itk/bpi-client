<?php
namespace Bpi\Sdk\Item;

use Bpi\Sdk\Document;

class BaseItem
{
    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     *
     * @return array of node properties
     */
    public function getProperties()
    {
        $properties = array();
        $this->document->walkProperties(function($e) use(&$properties) {
            $properties[$e['name']] = $e['@value'];
        });
        return $properties;
    }
}
