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
     * @var Agency
     */
    private $agency;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var array of Subscription
     */
    private $subscriptions;

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
    public function getAgency() {
        return $this->agency;
    }

    /**
     * @param string $agency
     * @return User
     */
    public function setAgency($agency) {
        $this->agency = $agency;
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
    public function getSubscriptions() {
        return $this->subscriptions;
    }

    /**
     * @param string $subscriptions
     * @return User
     */
    public function setSubscriptions($subscriptions) {
        $this->subscriptions = $subscriptions;
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

    /**
     * @return string
     */
    public function getName() {
        return trim($this->getFirstName() . ' ' . $this->getLastName());
    }

    public function __construct($el) {
        $values = [];

        if ($el instanceof GenericDocument) {
            $el->walkElements('user > *', function($el) use (&$values) {
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
                    case 'agency':
                        $values['agency'] = new Agency($el);
                        break;
                    case 'subscriptions':
                        foreach ($el->entry as $subscription) {
                            if (!isset($values['subscriptions'])) {
                                $values['subscriptions'] = [];
                            }
                            $values['subscriptions'][] = new Subscription($subscription);
                        }
                        break;
                }
            });

        }
        else if ($el instanceof SimpleXMLElement) {
            $values['id'] = (string)$el->id;
            $values['email'] = (string)$el->email;
            $values['firstName'] = (string)$el->user_first_name;
            $values['lastName'] = (string)$el->user_last_name;
            $values['agency'] = new Agency($el->agency);

            if (isset($el->subscriptions->entry)) {
                foreach ($el->subscriptions->entry as $subscription) {
                    if (!isset($values['subscriptions'])) {
                        $values['subscriptions'] = [];
                    }
                    $values['subscriptions'][] = new Subscription($subscription);
                }
            }
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
        if (isset($values['agency'])) {
            $this->setAgency($values['agency']);
        }
        if (isset($values['subscriptions'])) {
            $this->setSubscriptions($values['subscriptions']);
        }
    }
}
