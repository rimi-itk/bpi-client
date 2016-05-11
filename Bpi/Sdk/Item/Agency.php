<?php
namespace Bpi\Sdk\Item;

use Bpi\Sdk\GenericDocument;
use SimpleXMLElement;

class Agency {
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
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    private function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Channel
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function __construct($el) {
        $values = [];

        if ($el instanceof GenericDocument) {
            $el->walkElements('agency > *', function($el) use (&$values) {
                $name = $el->getName();
                $value = (string)$el;
                switch ($name) {
                    case 'id':
                      $values['id'] = $value;
                    break;
                  case 'name':
                    $values['name'] = $value;
                    break;
                }
            });

        }
        else if ($el instanceof SimpleXMLElement) {
            $values['id'] = (string)$el->id;
            $values['name'] = (string)$el->name;
        }
        else if (is_string($el)) {
            $values['id'] = (string)$el;
        } else {
            throw new \Exception('Invalid constructor call ' . get_class($el));
        }

        $this->setId($values['id']);
        if (isset($values['name'])) {
            $this->setName($values['name']);
        }
    }

}
