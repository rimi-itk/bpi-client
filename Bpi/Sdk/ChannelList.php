<?php
namespace Bpi\Sdk;

use Bpi\Sdk\Item\Channel;

/**
 * A list of channels.
 */
class ChannelList extends ItemList
{
    /**
     * {@inheritdoc}
     */
    protected function buildItems()
    {
        $items = [];
        foreach ($this->element->xpath('/channels/channel') as $el) {
            $items[] = new Channel($el);
        }

        return $items;
    }
}
