<?php
namespace Bpi\Sdk\Item;

class Node extends BaseItem
{
    /**
     * @var array
     */
    protected $assets;

    /**
     * @var array
     */
    protected $tags;

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
                // Convert attributes to associative array.
                $properties = array();
                foreach ($asset->attributes() as $name => $value) {
                    $properties[$name] = (string)$value;
                }
                $assets[$type][] = $properties;
            }

            $this->assets = $assets;
        }

        return $this->assets;
    }

    /**
     * Get node tags.
     *
     * @return array
     */
    public function getTags()
    {
        if (!$this->tags) {
            $tags = array();

            foreach ($this->element->xpath('tags/tag') as $tag) {
                $name = (string)$tag['tag_name'];
                if (!empty($name)) {
                    $tags[] = $name;
                }
            }

            $this->tags = $tags;
        }
        return $this->tags;
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
