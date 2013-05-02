<?php
namespace Bpi\Sdk;

use Bpi\Sdk\Document;

class NodeList implements \Iterator
{
    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Iterator interface implementation
     *
     * @group Iterator
     */
    function rewind()
    {
        $this->document->rewind();
    }

    /**
     * Returns same instance but with internal pointer to current item in collection
     *
     * @group Iterator
     * @return \Bpi\Sdk\Document will return same instance
     */
    function current()
    {
        return new \Bpi\Sdk\Item\Node($this->document->current());
    }

    /**
     * Key of current iteration position
     *
     * @group Iterator
     */
    function key()
    {
        return $this->document->key();
    }

    /**
     * Iterate to next item
     *
     * @group Iterator
     */
    function next()
    {
        $this->document->next();
    }

    /**
     * Checks if is ready for iteration
     *
     * @group Iterator
     * @return boolean
     */
    function valid()
    {
        return $this->document->valid();
    }
}
