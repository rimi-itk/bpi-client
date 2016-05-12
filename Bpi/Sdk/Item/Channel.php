<?php
namespace Bpi\Sdk\Item;

use Bpi\Sdk\GenericDocument;
use SimpleXMLElement;

class Channel {
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

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Channel
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdminId() {
        return $this->adminId;
    }

    /**
     * @param string $adminId
     * @return Channel
     */
    public function setAdminId($adminId) {
        $this->adminId = $adminId;
        return $this;
    }

    /**
     * @return User
     */
    public function getAdmin() {
        return $this->admin;
    }

    /**
     * @param User $admin
     * @return Channel
     */
    public function setAdmin($admin) {
        $this->admin = $admin;
        return $this;
    }

    /**
     * @return array
     */
    public function getEditors() {
        return $this->editors;
    }

    /**
     * @return array
     */
    public function getNodes() {
        return $this->nodes;
    }

    /**
     * @return DateTime
     */
    public function getNodeLastAddedAt() {
        return $this->nodeLastAddedAt;
    }

    public function __construct(GenericDocument $document) {
        $values = [];

        $document->walkProperties(function($el) use (&$values) {
            $name = $el['name'];
            $value = $el['@value'];

            switch ($name) {
                case 'id':
                    $values['id'] = $value;
                    break;
                case 'channelName':
                    $values['name'] = $value;
                    break;
                case 'channelDescription':
                    $values['description'] = $value;
                    break;
                case 'channelAdmin':
                    $values['adminId'] = $value;
                    break;
                case 'nodeLastAddedAt':
                    $values['nodeLastAddedAt'] = $value;
                    break;
            }
        });

        $document->walkElements('channel > *', function(SimpleXMLElement $el) use (&$values) {
            $name = $el->getName();
            $value = (string)$el;

            switch ($name) {
                case 'id':
                    $values['id'] = $value;
                    break;
                case 'channel_name':
                    $values['name'] = $value;
                    break;
                case 'channel_description':
                    $values['description'] = $value;
                    break;
                case 'channel_admin':
                    $values['admin'] = new User($el);
                    break;
                case 'users':
                    foreach ($el->user as $user) {
                        if (!isset($values['editors'])) {
                            $values['editors'] = [];
                        }
                        $values['editors'][] = new User($user);
                    }
                    break;
                case 'nodes':
                    foreach ($el->node as $node) {
                        if (!isset($values['nodes'])) {
                            $values['nodes'] = [];
                        }
                        $values['nodes'][] = (string)$node;
                    }
                    break;
                case 'node_last_added_at':
                    $values['nodeLastAddedAt'] = $value;
                    break;
            }
        });

        $this->setId($values['id']);
        if (isset($values['name'])) {
            $this->setName($values['name']);
        }
        if (isset($values['description'])) {
            $this->setDescription($values['description']);
        }
        if (isset($values['adminId'])) {
            $this->setAdminId($values['adminId']);
        }
        if (isset($values['admin'])) {
            $this->setAdmin($values['admin']);
        }
        if (isset($values['editors'])) {
            $this->editors = $values['editors'];
        }
        if (isset($values['nodes'])) {
            $this->nodes = $values['nodes'];
        }
        if (isset($values['nodeLastAddedAt'])) {
            $this->nodeLastAddedAt = new \DateTime($values['nodeLastAddedAt']);
        }
    }
}