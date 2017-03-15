<?php
namespace Bpi\Sdk\Item;

use Bpi\Sdk\GenericDocument;

class Agency
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    private function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Channel
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function __construct(\SimpleXMLElement $el)
    {
        $this->setId((string)$el->id);
        $this->setName((string)$el->name);
    }
}
