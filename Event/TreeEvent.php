<?php
namespace Kitpages\EdmBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Kitpages\EdmBundle\Entity\File;
use Kitpages\EdmBundle\Entity\Node;

class TreeEvent extends Event
{
    protected $data = array();
    protected $node = null;
    protected $isPrevented = false;

    public function __construct()
    {
    }

    /**
     * @Param Node $node
     */
    public function setNode(Node $node)
    {
        $this->node = $node;
    }

    /**
     * return Node
     */
    public function getNode()
    {
        return $this->node;
    }
    /**
     * return boolean
     */
    public function getSendMail()
    {
        return $this->sendMail;
    }

    public function setSendMail($sendMail)
    {
        $this->sendMail = $sendMail;
    }

    public function set($key, $val)
    {
        $this->data[$key] = $val;
    }

    public function get($key)
    {
        if (!array_key_exists($key, $this->data)) {
            return null;
        }
        return $this->data[$key];
    }

    public function preventDefault()
    {
        $this->isPrevented = true;
    }

    public function isDefaultPrevented()
    {
        return $this->isPrevented;
    }
}
