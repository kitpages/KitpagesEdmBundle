<?php
namespace Kitpages\EdmBundle\Entity;

class Node {

    CONST NODE_TYPE_ROOT = "root";
    CONST NODE_TYPE_FILE = "file";
    CONST NODE_TYPE_DIRECTORY = "directory";

    /**
     * @var string $treeId
     */
    private $treeId;

    /**
     * @var integer $userId
     */
    private $userId;

    /**
     * @var string $userName
     */
    private $userName;

    /**
     * @var string $userEmail
     */
    private $userEmail;

    /**
     * @var string $userIp
     */
    private $userIp;

    /**
     * @var string $label
     */
    private $label;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var text $comment
     */
    private $comment;

    /**
     * @var string $nodeType
     */
    private $nodeType;

    /**
     * @var array $data
     */
    private $data;

    /**
     * @var datetime $createdAt
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     */
    private $updatedAt;

    /**
     * @var integer $left
     */
    private $left;

    /**
     * @var integer $right
     */
    private $right;

    /**
     * @var integer $root
     */
    private $root;

    /**
     * @var integer $level
     */
    private $level;

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var Kitpages\EdmBundle\Entity\File
     */
    private $file;

    /**
     * @var Kitpages\EdmBundle\Entity\Node
     */
    private $parent;


    /**
     * Set treeId
     *
     * @param string $treeId
     */
    public function setTreeId($treeId)
    {
        $this->treeId = $treeId;
    }

    /**
     * Get treeId
     *
     * @return string 
     */
    public function getTreeId()
    {
        return $this->treeId;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set userName
     *
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * Get userName
     *
     * @return string 
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set userEmail
     *
     * @param string $userEmail
     */
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;
    }

    /**
     * Get userEmail
     *
     * @return string 
     */
    public function getUserEmail()
    {
        return $this->userEmail;
    }

    /**
     * Set userIp
     *
     * @param string $userIp
     */
    public function setUserIp($userIp)
    {
        $this->userIp = $userIp;
    }

    /**
     * Get userIp
     *
     * @return string 
     */
    public function getUserIp()
    {
        return $this->userIp;
    }

    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set comment
     *
     * @param text $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get comment
     *
     * @return text 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set nodeType
     *
     * @param string $nodeType
     */
    public function setNodeType($nodeType)
    {
        $this->nodeType = $nodeType;
    }

    /**
     * Get nodeType
     *
     * @return string 
     */
    public function getNodeType()
    {
        return $this->nodeType;
    }

    /**
     * Set data
     *
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @return array 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updatedAt
     *
     * @return datetime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set left
     *
     * @param integer $left
     */
    public function setLeft($left)
    {
        $this->left = $left;
    }

    /**
     * Get left
     *
     * @return integer 
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * Set right
     *
     * @param integer $right
     */
    public function setRight($right)
    {
        $this->right = $right;
    }

    /**
     * Get right
     *
     * @return integer 
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * Set root
     *
     * @param integer $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * Get root
     *
     * @return integer 
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set level
     *
     * @param integer $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set file
     *
     * @param Kitpages\EdmBundle\Entity\File $file
     */
    public function setFile(\Kitpages\EdmBundle\Entity\File $file)
    {
        $this->file = $file;
    }

    /**
     * Get file
     *
     * @return Kitpages\EdmBundle\Entity\File 
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set parent
     *
     * @param Kitpages\EdmBundle\Entity\Node $parent
     */
    public function setParent(\Kitpages\EdmBundle\Entity\Node $parent)
    {
        $this->setTreeId($parent->getTreeId());
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Kitpages\EdmBundle\Entity\Node 
     */
    public function getParent()
    {
        return $this->parent;
    }
}