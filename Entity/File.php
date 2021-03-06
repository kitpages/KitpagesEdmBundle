<?php
namespace Kitpages\EdmBundle\Entity;

class File {

    CONST FILE_STATUS_DISABLE = "disable";
    CONST FILE_STATUS_CURRENT = "current";
    CONST FILE_STATUS_OLD_VERSION = "old_version";

    /**
     * @var string $fileName
     */
    private $fileName;

    /**
     * @var boolean $hasUploadFailed
     */
    private $hasUploadFailed;

    /**
     * @var array $data
     */
    private $data;

    /**
     * @var integer $version
     */
    private $version;

    /**
     * @var string $versionNote
     */
    private $versionNote;

    /**
     * @var string $status
     */
    private $status;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $mimeType
     */
    private $mimeType;

    /**
     * @var datetime $createdAt
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     */
    private $updatedAt;

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var Kitpages\EdmBundle\Entity\Node
     */
    private $node;

    /**
     * @var Kitpages\EdmBundle\Entity\File
     */
    private $nextVersion;

    /**
     * @var Kitpages\EdmBundle\Entity\File
     */
    private $previousVersion;


    /**
     * Set fileName
     *
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Get fileName
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set hasUploadFailed
     *
     * @param boolean $hasUploadFailed
     */
    public function setHasUploadFailed($hasUploadFailed)
    {
        $this->hasUploadFailed = $hasUploadFailed;
    }

    /**
     * Get hasUploadFailed
     *
     * @return boolean 
     */
    public function getHasUploadFailed()
    {
        return $this->hasUploadFailed;
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
     * Set version
     *
     * @param integer $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Get version
     *
     * @return integer 
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set versionNote
     *
     * @param string $versionNote
     */
    public function setVersionNote($versionNote)
    {
        $this->versionNote = $versionNote;
    }

    /**
     * Get versionNote
     *
     * @return string 
     */
    public function getVersionNote()
    {
        return $this->versionNote;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Get mimeType
     *
     * @return string 
     */
    public function getMimeType()
    {
        return $this->mimeType;
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set node
     *
     * @param Kitpages\EdmBundle\Entity\Node $node
     */
    public function setNode(\Kitpages\EdmBundle\Entity\Node $node)
    {
        $this->node = $node;
    }

    /**
     * Get node
     *
     * @return Kitpages\EdmBundle\Entity\Node 
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Set nextVersion
     *
     * @param Kitpages\EdmBundle\Entity\File $nextVersion
     */
    public function setNextVersion(\Kitpages\EdmBundle\Entity\File $nextVersion)
    {
        $this->nextVersion = $nextVersion;
    }

    /**
     * Get nextVersion
     *
     * @return Kitpages\EdmBundle\Entity\File 
     */
    public function getNextVersion()
    {
        return $this->nextVersion;
    }

    /**
     * Set previousVersion
     *
     * @param Kitpages\EdmBundle\Entity\File $previousVersion
     */
    public function setPreviousVersion(\Kitpages\EdmBundle\Entity\File $previousVersion)
    {
        $this->previousVersion = $previousVersion;
    }

    /**
     * Get previousVersion
     *
     * @return Kitpages\EdmBundle\Entity\File 
     */
    public function getPreviousVersion()
    {
        return $this->previousVersion;
    }

    /**
     * @var Kitpages\EdmBundle\Entity\File
     */
    private $originalVersion;


    /**
     * Set originalVersion
     *
     * @param Kitpages\EdmBundle\Entity\File $originalVersion
     */
    public function setOriginalVersion(\Kitpages\EdmBundle\Entity\File $originalVersion)
    {
        $this->originalVersion = $originalVersion;
    }

    /**
     * Get originalVersion
     *
     * @return Kitpages\EdmBundle\Entity\File 
     */
    public function getOriginalVersion()
    {
        return $this->originalVersion;
    }
}