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
            $el->walkElements('> *', function($el) use (&$values) {
                $name = $el->getName();
                $value = (string)$el;
                switch ($name) {
                    case 'id':
                        $values['id'] = $value;
                        break;
                    case 'email':
                        $values['email'] = $value;
                        break;
                    case 'user_first_name':
                        $values['firstName'] = $value;
                        break;
                    case 'user_last_name':
                        $values['lastName'] = $value;
                        break;
                    case 'agency_id':
                        $values['agencyId'] = $value;
                        break;
                    case 'agency':
                      $values['agency'] = new Agency((string)$el->agency->id, (string)$el->agency->name);
                      break;
                }
            });

        }
        else if ($el instanceof SimpleXMLElement) {
            $values['id'] = (string)$el->id;
            $values['name'] = (string)$el->name;
        } else {
            throw new \Exception('Invalid constructor call ' . get_class($el));
        }

        $this->setId($values['id']);
        if (isset($values['name'])) {
            $this->setName($values['name']);
        }
    }

}
