<?php
namespace Bpi\Sdk\Item;

use Bpi\Sdk\GenericDocument;
use SimpleXMLElement;

class User {
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $agencyId;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $id
     * @return User
     */
    protected function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getAgencyId() {
        return $this->agencyId;
    }

    /**
     * @param string $agencyId
     * @return User
     */
    public function setAgencyId($agencyId) {
        $this->agencyId = $agencyId;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;
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
                }
            });

        }
        else if ($el instanceof SimpleXMLElement) {
            $values['id'] = (string)$el->id;
            $values['email'] = (string)$el->email;
            $values['firstName'] = (string)$el->user_first_name;
            $values['lastName'] = (string)$el->user_last_name;
            $values['agencyId'] = (string)$el->agency_id;
        }
        else if (is_string($el)) {
            $values['id'] = (string)$el;
        } else {
            throw new \Exception('Invalid constructor call ' . get_class($el));
        }

        $this->setId($values['id']);
        if (isset($values['email'])) {
            $this->setEmail($values['email']);
        }
        if (isset($values['firstName'])) {
            $this->setFirstName($values['firstName']);
        }
        if (isset($values['lastName'])) {
            $this->setLastName($values['lastName']);
        }
        if (isset($values['agencyId'])) {
            $this->setAgencyId($values['agencyId']);
        }
    }
}