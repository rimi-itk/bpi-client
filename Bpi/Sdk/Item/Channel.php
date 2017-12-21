<?php
namespace Bpi\Sdk\Item;

class Channel
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
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $adminId;

    /**
     * @var User
     */
    private $admin;

    /**
     * @var array
     */
    private $editors;

    /**
     * @var array
     */
    private $nodes;

    /**
     * @var DateTime
     */
    private $nodeLastAddedAt;

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

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Channel
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdminId()
    {
        return $this->adminId;
    }

    /**
     * @param string $adminId
     * @return Channel
     */
    public function setAdminId($adminId)
    {
        $this->adminId = $adminId;
        return $this;
    }

    /**
     * @return User
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @param User $admin
     * @return Channel
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
        return $this;
    }

    /**
     * @return array
     */
    public function getEditors()
    {
        return $this->editors;
    }

    /**
     * @return array
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @return DateTime
     */
    public function getNodeLastAddedAt()
    {
        return $this->nodeLastAddedAt;
    }

    public function __construct(\SimpleXMLElement $el)
    {
        $this->setId((string)$el->id);
        $this->setName((string)$el->channel_name);
        $this->setDescription((string)$el->channel_description);

        $admin = new User($el->channel_admin);
        $this->setAdminId($admin->getId());
        $this->setAdmin($admin);

        if (isset($el->users)) {
            $users = [];
            foreach ($el->users->user as $user) {
                $users[] = new User($user);
            }
            $this->editors = $users;
        }

        if (isset($el->nodes)) {
            $nodes = [];
            foreach ($el->nodes->node as $node) {
                $nodes[] = (string)$node;
            }
            $this->nodes = $nodes;
        }

        $this->nodeLastAddedAt = (string)$el->node_last_added_at ? new \DateTime((string)$el->node_last_added_at) : null;
    }
}
