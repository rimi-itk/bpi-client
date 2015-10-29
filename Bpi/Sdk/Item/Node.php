<?php
namespace Bpi\Sdk\Item;

class Node extends BaseItem
{
    /**
     * Get node assets (images)
     *
     * @return array
     */
    public function getAssets()
    {
        $assets = array();
        $this->document->walkAssets(function($e) use(&$assets) {
            $type = $e['type'];
            if (!isset($assets[$type])) {
                $assets[$type] = array();
            }
            $assets[$type][$e['name']] = $e;
        });

        return $assets;
    }

    public function syndicate()
    {
        // @todo implementation
    }

    public function push()
    {
        // @todo implementation
    }

    public function delete()
    {
        // @todo implementation
    }
}
