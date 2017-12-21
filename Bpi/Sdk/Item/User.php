<?php
namespace Bpi\Sdk\Item;

class User
{
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return User
     */
    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * @param string $agency
     * @return User
     */
    public function setAgency($agency)
    {
        $this->agency = $agency;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * @param string $subscriptions
     * @return User
     */
    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return trim($this->getFirstName() . ' ' . $this->getLastName());
    }

    public function __construct(\SimpleXMLElement $el)
    {
        $this->setId((string)$el->id);
        $this->setEmail((string)$el->email);
        $this->setFirstName((string)$el->user_first_name);
        $this->setLastName((string)$el->user_last_name);
        $this->setAgency(new Agency($el->agency));
        if (isset($el->subscriptions->entry)) {
            $subscriptions = [];
            foreach ($el->subscriptions->entry as $subscription) {
                $subscriptions[] = new Subscription($subscription);
            }
            $this->setSubscriptions($subscriptions);
        }
    }
}
