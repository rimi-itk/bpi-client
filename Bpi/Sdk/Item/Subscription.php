<?php
namespace Bpi\Sdk\Item;

use Bpi\Sdk\GenericDocument;
use SimpleXMLElement;

class Subscription {
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
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Title
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilter() {
        return $this->filter;
    }

    /**
     * @param mixed $filter
     * @return User
     */
    public function setFilter($filter) {
      if (is_string($filter)) {
        $this->filter = json_decode($filter, TRUE);
      }
      else {
        $this->filter = $filter;
      }
      return $this;
    }

    public function __construct($el) {
        $values = array();

        if ($el instanceof SimpleXMLElement) {
            $values['title'] = (string)$el->title;
            $values['filter'] = (string)$el->filter;
        } else {
            throw new \Exception('Invalid constructor call ' . get_class($el));
        }

        if (isset($values['title'])) {
            $this->setTitle(htmlspecialchars_decode($values['title'], ENT_QUOTES));
        }
        if (isset($values['filter'])) {
            $this->setFilter(htmlspecialchars_decode($values['filter'], ENT_QUOTES));
        }
    }
}
