<?php
namespace Bpi\Sdk\Item;

use Bpi\Sdk\GenericDocument;

class Subscription
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $filter;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param mixed $filter
     * @return User
     */
    public function setFilter($filter)
    {
        if (is_string($filter)) {
            $this->filter = json_decode($filter, true);
        } else {
            $this->filter = $filter;
        }
        return $this;
    }

    public function __construct(\SimpleXMLElement $el)
    {
        $title = (string)$el->title;
        $filter = (string)$el->filter;
        $this->setTitle(htmlspecialchars_decode($title, ENT_QUOTES));
        $this->setFilter(htmlspecialchars_decode($filter, ENT_QUOTES));
    }
}
