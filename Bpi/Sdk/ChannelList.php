<?php
namespace Bpi\Sdk;

use Bpi\Sdk\GenericDocument;
use Bpi\Sdk\Exception\EmptyList;

/**
 * TODO please add a general description about the purpose of this class.
 */
class ChannelList extends NodeList
{
    /**
     * {@inheritdoc}
     */
    public function __construct(GenericDocument $document)
    {
        try
        {
            $this->document = clone $document;
            $this->document->reduceByName('channel');
            $this->total = $this->document->total;
        }
        catch (EmptyList $e)
        {
            $this->document->clear();
        }
    }

    /**
     * Returns same instance but with internal pointer to current item in collection
     *
     * @group Iterator
     * @return \Bpi\Sdk\Document will return same instance
     */
    function current()
    {
        return new \Bpi\Sdk\Item\Channel($this->document->current());
    }
}
