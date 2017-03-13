<?php
namespace Bpi\Sdk;

use Bpi\Sdk\Item\Facet;
use Bpi\Sdk\Item\User;

/**
 * A list of users.
 */
class UserList extends ItemList
{
    /**
     * {@inheritdoc}
     */
    protected function buildItems()
    {
        $items = [];
        foreach ($this->element->xpath('/users/user') as $el) {
            $items[] = new User($el);
        }

        return $items;
    }
}
