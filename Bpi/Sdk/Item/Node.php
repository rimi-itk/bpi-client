<?php
namespace Bpi\Sdk\Item;

class Node extends BaseItem
{
    /**
     * @var array
     */
    protected $assets;

    /**
     * Get node assets (images)
     *
     * @return array
     */
    public function getAssets()
    {
        if (!$this->assets) {
            $assets = [];

            if (isset($this->element->assets)) {
                foreach ($this->element->assets->file as $file) {
                    $type = (string)$file['type'];
                    if (!isset($assets[$type])) {
                        $assets[$type] = [];
                    }
                    $assets[$type][] = [
                        'path' => (string)$file['path'],
                        'alt' => (string)$file['alt'],
                        'title' => (string)$file['title'],
                        'type' => (string)$file['type'],
                        'name' => (string)$file['name'],
                        'extension' => (string)$file['extension'],
                    ];
                }
            } else {
                foreach ($this->getProperties() as $key => $val) {
                    if (strpos($key, 'asset') !== false) {
                        $assets[$key] = $val;
                    }
                }
            }

            $this->assets = $assets;
        }

        return $this->assets;
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
