<?php

namespace Kitpages\EdmBundle\Service;

// external service
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Routing\RouterInterface;

use Kitpages\FileSystemBundle\Service\Adapter\AdapterInterface;

use Kitpages\EdmBundle\Entity\Node;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Kitpages\EdmBundle\Event\TreeEvent;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Kitpages\EdmBundle\KitpagesEdmEvents;

class TreeManager {
    ////
    // dependency injection
    ////
    protected $fileManager = null;
    protected $doctrine = null;
    protected $dispatcher = null;
    protected $router = null;
    protected $translator = null;
    protected $treeId = null;
    protected $actionList = array();

    CONST TYPE_ACTION_ADD_DIRECTORY = "addDirectory";
    CONST TYPE_ACTION_ADD_FILE = "addFile";
    CONST TYPE_ACTION_ADD_FILE_VERSION = "addFileVersion";
    CONST TYPE_ACTION_DELETE_OLD_FILE_VERSION = "deleteOldFileVersion";
    CONST TYPE_ACTION_DOWNLOAD_FILE = "downloadFile";
    CONST TYPE_ACTION_DOWNLOAD_DIRECTORY = "downloadDirectory";
    CONST TYPE_ACTION_DISABLE_FILE = "disableFile";
    CONST TYPE_ACTION_DISABLE_DIRECTORY = "disableDirectory";
    CONST TYPE_ACTION_RETRIEVE_DIRECTORY = "retrieveDirectory";
    CONST TYPE_ACTION_RETRIEVE_FILE = "retrieveFile";
    CONST TYPE_ACTION_DELETE_DIRECTORY = "deleteDirectory";
    CONST TYPE_ACTION_DELETE_FILE = "deleteFile";

    public function __construct(
        Registry $doctrine,
        EventDispatcherInterface $dispatcher,
        RouterInterface $router,
        FileManager $fileManager,
        Translator $translator,
        $idService
    )
    {
        $this->doctrine = $doctrine;
        $this->dispatcher = $dispatcher;
        $this->router = $router;
        $this->translator = $translator;
        $this->fileManager = $fileManager;
        $this->treeId = str_replace('kitpages_edm.tree.', '', $idService);

        $em = $this->doctrine->getEntityManager();
        $query = $em->getRepository('KitpagesEdmBundle:Node')->getRootNodeByTreeId($this->treeId)->getQuery();
        $root = $query->getResult();
        if ($root == null) {
            $root = $this->createTree();
        } else {
            $root = $root[0];
        }
        $this->rootTree = $root;
    }

    public function getFileManager()
    {
        return $this->fileManager;
    }

    public function setActionList($actionList)
    {
        $this->actionList = $actionList;
    }

    public function getRoot()
    {
        return $this->rootTree;
    }

    private function createTree()
    {
        $nodeRoot = new Node();
        $nodeRoot->setTreeId($this->treeId);
        $nodeRoot->setLabel('root');
        $nodeRoot->setNodeType(Node::NODE_TYPE_ROOT);

        $em = $this->doctrine->getEntityManager();
        $em->persist($nodeRoot);
        $em->flush();
        return $nodeRoot;
    }

    public function createNodeDirectoryChildOfRoot(array $parameterList)
    {
        return $this->createNodeDirectoryChild($this->rootTree, $parameterList);
    }

    public function createNodeDirectoryChild($nodeParent, array $parameterList)
    {
        $node = new Node();
        $node->setTreeId($this->treeId);
        foreach($parameterList as $parameter => $value) {
            $method = 'set'.ucfirst($parameter);
            $node->$method($value);
        }
        $node->setParent($nodeParent);
        $node->setNodeType(Node::NODE_TYPE_DIRECTORY);

        $em = $this->doctrine->getEntityManager();
        $em->persist($node);
        $em->flush();
        return $node;
    }

    public function uploadFile(Node $node, UploadedFile $uploadedFile, $sendEmail, $versionNote = null)
    {

        // send on event
        $event = new TreeEvent();
        $event->setNode($node);
        $event->setSendMail($sendEmail);
        $event->set(
            'uploadedFile',
            array(
                'originalName' => $uploadedFile->getClientOriginalName(),
                'mimeType' => $uploadedFile->getClientMimeType()
            )
        );
        $event->set('versionNote', $versionNote);

        // send on event
        $eventMail = clone $event;
        $eventMail->set('sendEmail', $sendEmail);

        $em = $this->doctrine->getEntityManager();
        $oldVersionFile = $node->getFile();
        if ($oldVersionFile == null) {
            // upload first version
            $this->dispatcher->dispatch(KitpagesEdmEvents::onNewFileUpload, $event);
            if (! $event->isDefaultPrevented()) {
                $file = $this->fileManager->newFile($uploadedFile);
            }

            $node = $event->getNode();
            $node->setFile($file);
            $node->setLabel($uploadedFile->getClientOriginalName());
            $em->persist($node);
            $em->persist($file);
            $em->flush();

            // send after event
            $this->dispatcher->dispatch(KitpagesEdmEvents::afterNewFileUpload, $event);
        } else {
            // Upload new version
            $this->dispatcher->dispatch(KitpagesEdmEvents::onNewVersionFileUpload, $event);
            if (! $event->isDefaultPrevented()) {
                $file = $this->fileManager->newVersionFile($oldVersionFile, $uploadedFile, $versionNote);
            }

            $node = $event->getNode();
            $node->setFile($file);
            $node->setLabel($uploadedFile->getClientOriginalName());
            $em->persist($node);
            $em->flush();

            // send after event
            $this->dispatcher->dispatch(KitpagesEdmEvents::afterNewVersionFileUpload, $event);
        }

    }

    public function nodeInTree($node, $statusParent, $kitpages_target = null)
    {
        $event = new TreeEvent();
        $event->setNode($node);
        $this->dispatcher->dispatch(KitpagesEdmEvents::onDisplayNodeInTree, $event);
        if (!$event->isDefaultPrevented()) {

            $nodeTree = array();
            $nodeTree['id'] = $node->getId();
            $nodeTree['label'] = $node->getLabel();
            $nodeTree['title'] = $node->getTitle();
            $nodeTree['nodeType'] =  $node->getNodeType();
            $nodeStatus = $node->getStatus();
            if ($nodeStatus != null) {
                $nodeTree['class'] =  'kitpages-edm-node-status-'.$node->getStatus();
            }

            if ($node->getNodeType() == Node::NODE_TYPE_FILE) {
                $nodeTree['url'] = $this->router->generate(
                    'kitpages_edm_view_node',
                    array(
                        'nodeId' => $node->getId()
                    )
                );
            }
            // actionList
            $nodeTree['actionList'] = array();
            if ($statusParent !=  Node::NODE_STATUS_DISABLE) {
                if ($node->getNodeType() == Node::NODE_TYPE_DIRECTORY) {
                    if($nodeStatus == Node::NODE_STATUS_DISABLE ) {
                        $nodeTree['actionList'][] = array(
                            'type' => self::TYPE_ACTION_RETRIEVE_DIRECTORY,
                            'label' => $this->translator->trans("undelete"),
                            'attr' => array(
                                'title' => $this->translator->trans("undelete directory")
                            ),
                            'url' => $this->router->generate(
                                'kitpages_edm_retrieve_node',
                                array(
                                    'nodeId' => $node->getId(),
                                    'kitpages_target' => $kitpages_target
                                )
                            )
                        );
                        $nodeTree['actionList'][] = array(
                            'type' => self::TYPE_ACTION_DELETE_DIRECTORY,
                            'label' => $this->translator->trans('delete definitely'),
                            'classLink' => 'kit-edm-confirm',
                            'attr' => array(
                                'title' => $this->translator->trans('delete directory definitely'),
                                "data-kitpages-edm-confirm" => $this->translator->trans("Do you confirm you want to delete directory %label% definitely?", array('%label%' => $node->getLabel()))
                            ),
                            'url' => $this->router->generate(
                                'kitpages_edm_delete_node',
                                array(
                                    'nodeId' => $node->getId(),
                                    'kitpages_target' => $kitpages_target
                                )
                            )
                        );
                    } else {
                        $nodeTree['actionList'][] = array(
                            'type' => self::TYPE_ACTION_DOWNLOAD_DIRECTORY,
                            'label' => $this->translator->trans('Download'),
                            'icon' => 'bundles/kitpagesedm/icon/download.png',
                            'url' => $this->router->generate(
                                'kitpages_edm_export_node',
                                array(
                                    'nodeId' => $node->getId()
                                )
                            )
                        );
                        $nodeTree['actionList'][] = array(
                            'type' => self::TYPE_ACTION_ADD_DIRECTORY,
                            'label' => $this->translator->trans('Add a directory'),
                            'icon' => 'bundles/kitpagesedm/icon/add-directory.png',
                            'classLink' => 'poplight',
                            'attr' => array(
                                'data-kitpages-edm-field-name' => 'kitpages_edmbundle_nodedirectoryform_parent_id',
                                'data-kitpages-edm-field-value'=> $node->getId(),
                                'rel' => "kitpages_edmbundle_nodedirectoryform"
                            )
                        );
                        $nodeTree['actionList'][] = array(
                            'type' => self::TYPE_ACTION_ADD_FILE,
                            'label' => $this->translator->trans('Add a file'),
                            'icon' => 'bundles/kitpagesedm/icon/add-file.png',
                            'classLink' => 'poplight',
                            'attr' => array(
                                'data-kitpages-edm-field-name' => 'kitpages_edmbundle_nodefileform_parent_id',
                                'data-kitpages-edm-field-value'=> $node->getId(),
                                'rel' => "kitpages_edmbundle_nodefileform"
                            )
                        );
                        $nodeTree['actionList'][] = array(
                            'type' => self::TYPE_ACTION_DISABLE_DIRECTORY,
                            'label' => $this->translator->trans('delete directory').' '.$node->getLabel(),
                            'classLink' => 'kit-edm-confirm',
                            'attr' => array(
                                "data-kitpages-edm-confirm" => $this->translator->trans("Do you confirm you want to delete directory %label%?", array('%label%' => $node->getLabel()))
                            ),
                            'icon' => 'bundles/kitpagesedm/icon/disable-directory.png',
                            'url' => $this->router->generate(
                                'kitpages_edm_disable_node',
                                array(
                                    'nodeId' => $node->getId(),
                                    'kitpages_target' => $kitpages_target
                                )
                            )
                        );
                        $nodeTree['actionList'][] = array(
                            'type' => self::TYPE_ACTION_DELETE_OLD_FILE_VERSION,
                            'label' => $this->translator->trans('delete older version'),
                            'classLink' => 'kit-edm-confirm',
                            'attr' => array(
                                'title' => $this->translator->trans('delete the older file version in the directory').' '.$node->getLabel(),
                                "data-kitpages-edm-confirm" => $this->translator->trans("Do you confirm you want to delete the older file version in the directory %label%?", array('%label%' => $node->getLabel()))
                            ),
                            'url' => $this->router->generate(
                                'kitpages_edm_delete_old_file_version',
                                array(
                                    'nodeId' => $node->getId(),
                                    'kitpages_target' => $kitpages_target
                                )
                            )
                        );
                    }
                    if (isset($this->actionList[Node::NODE_TYPE_DIRECTORY])) {
                        foreach($this->actionList[Node::NODE_TYPE_DIRECTORY] as $action) {
                            $nodeTree['actionList'][] = $this->parseAction($action, $node);
                        }
                    }
                } elseif ($node->getNodeType() == Node::NODE_TYPE_FILE) {
                    $nodeTree['actionList'] = $this->nodeFileActionList($node, $nodeTree['actionList'], $kitpages_target);
                    if (isset($this->actionList[Node::NODE_TYPE_FILE])) {
                        foreach($this->actionList[Node::NODE_TYPE_FILE] as $action) {
                            $nodeTree['actionList'][] = $this->parseAction($action, $node);
                        }
                    }
                }
            }
            $event->set("nodeTree", $nodeTree);
            $this->dispatcher->dispatch(KitpagesEdmEvents::afterDisplayNodeInTree, $event);
            $nodeTree = $event->get("nodeTree");
            $nodeTree['children'] = $this->nodeChildren($node, $kitpages_target);
            return $nodeTree;
        }
    }

    public function nodeFileActionList($node, $actionList, $kitpages_target)
    {
        $nodeStatus = $node->getStatus();
        if($nodeStatus == Node::NODE_STATUS_DISABLE ) {
            $actionList[] = array(
                'type' => self::TYPE_ACTION_RETRIEVE_FILE,
                'label' => $this->translator->trans('undelete file'),
                'url' => $this->router->generate(
                    'kitpages_edm_retrieve_node',
                    array(
                        'nodeId' => $node->getId(),
                        'kitpages_target' => $kitpages_target
                    )
                )
            );
            $actionList[] = array(
                'type' => self::TYPE_ACTION_DELETE_FILE,
                'label' => $this->translator->trans('delete definitely'),
                'attr' => array(
                    'title' => $this->translator->trans('delete file definitely'),
                    "data-kitpages-edm-confirm" => $this->translator->trans("Do you confirm you want to delete file %label% definitely?", array('%label%' => $node->getLabel()))
                ),
                'classLink' => 'kit-edm-confirm',
                'url' => $this->router->generate(
                    'kitpages_edm_delete_node',
                    array(
                        'nodeId' => $node->getId(),
                        'kitpages_target' => $kitpages_target
                    )
                )
            );
        } else {
            if ($node->getFile() != null) {
                $actionList[] = array(
                    'type' => self::TYPE_ACTION_DOWNLOAD_FILE,
                    'label' => $this->translator->trans('Download'),
                    'icon' => 'bundles/kitpagesedm/icon/download.png',
                    'url' => $this->router->generate(
                        'kitpages_edm_render',
                        array(
                            'id' => $node->getFile()->getId()
                        )
                    )
                );
            }
            $actionList[] = array(
                'type' => self::TYPE_ACTION_ADD_FILE_VERSION,
                'label' => $this->translator->trans('Add a new version'),
                'icon' => 'bundles/kitpagesedm/icon/add-file-version.png',
                'classLink' => 'poplight',
                'attr' => array(
                    'data-kitpages-edm-field-name' => 'kitpages_edmbundle_nodefileversionform_node_id',
                    'data-kitpages-edm-field-value'=> $node->getId(),
                    'rel' => "kitpages_edmbundle_nodefileversionform"
                )
            );
            $actionList[] = array(
                'type' => self::TYPE_ACTION_DISABLE_FILE,
                'label' => $this->translator->trans('delete file').' '.$node->getLabel(),
                'classLink' => 'kit-edm-confirm',
                'attr' => array(
                    "data-kitpages-edm-confirm" => $this->translator->trans("Do you confirm you want to delete file %label%?", array('%label%' => $node->getLabel()))
                ),
                'icon' => 'bundles/kitpagesedm/icon/disable-file.png',
                'url' => $this->router->generate(
                    'kitpages_edm_disable_node',
                    array(
                        'nodeId' => $node->getId(),
                        'kitpages_target' => $kitpages_target
                    )
                )
            );
            $actionList[] = array(
                'type' => self::TYPE_ACTION_DELETE_OLD_FILE_VERSION,
                'label' => $this->translator->trans('delete older version'),
                'classLink' => 'kit-edm-confirm',
                'attr' => array(
                    'title' => $this->translator->trans('delete the older file version').' '.$node->getLabel(),
                    "data-kitpages-edm-confirm" => $this->translator->trans("Do you confirm you want to delete the older file version %label%?", array('%label%' => $node->getLabel()))
                ),
                'url' => $this->router->generate(
                    'kitpages_edm_delete_old_file_version',
                    array(
                        'nodeId' => $node->getId(),
                        'kitpages_target' => $kitpages_target
                    )
                )
            );
        }
        return $actionList;
    }

    public function nodeChildren($nodeParent, $kitpages_target = null)
    {
        $em = $this->doctrine->getEntityManager();

        $nodeList = $em->getRepository('KitpagesEdmBundle:Node')->children($nodeParent, true);

        $nodeListRenderer = array();

        foreach($nodeList as $node) {
            if (
                null != $nodeRenderer = $this->nodeInTree($node, $nodeParent->getStatus(), $kitpages_target)) {
                $nodeListRenderer[] = $nodeRenderer;
            }
        }

        return $nodeListRenderer;
    }


    public function deleteOldFileVersion($node, $recursive)
        {
            $event = new TreeEvent();
            $event->setNode($node);
            $this->dispatcher->dispatch(KitpagesEdmEvents::onDeleteOldFileVersion, $event);
            if (!$event->isDefaultPrevented()) {
                $nodeType = $node->getNodeType();
                if ($nodeType == Node::NODE_TYPE_FILE) {
                    $this->fileManager->deleteOldFileVersion($node->getFile());
                }
                if ($recursive && $nodeType == Node::NODE_TYPE_DIRECTORY) {
                    $em = $this->doctrine->getEntityManager();
                    $nodeChildList = $em->getRepository('KitpagesEdmBundle:Node')->children($node, true);
                    foreach($nodeChildList as $nodeChild) {
                        $this->deleteOldFileVersion($nodeChild, $recursive);
                    }
                }
            }
            $this->dispatcher->dispatch(KitpagesEdmEvents::afterDeleteOldFileVersion, $event);
        }

    public function deleteNode($node, $recursive)
    {
        $event = new TreeEvent();
        $event->setNode($node);
        $this->dispatcher->dispatch(KitpagesEdmEvents::onDeleteNode, $event);
        if (!$event->isDefaultPrevented()) {
            $nodeType = $node->getNodeType();
            $em = $this->doctrine->getEntityManager();
            if ($nodeType == Node::NODE_TYPE_FILE) {
                $this->fileManager->deleteFile($node->getFile());
            }
            if ($recursive && $nodeType == Node::NODE_TYPE_DIRECTORY) {
                $nodeChildList = $em->getRepository('KitpagesEdmBundle:Node')->children($node, true);
                foreach($nodeChildList as $nodeChild) {
                    $this->deleteNode($nodeChild, $recursive);
                }
            }
            $em->remove($node);
            $em->flush();
        }
        $this->dispatcher->dispatch(KitpagesEdmEvents::afterDeleteNode, $event);
    }

    public function modifyStatusNode($node, $status, $recursive)
    {
        $event = new TreeEvent();
        $event->setNode($node);
        $this->dispatcher->dispatch(KitpagesEdmEvents::onModifyStatusNode, $event);
        if (!$event->isDefaultPrevented()) {
            $nodeType = $node->getNodeType();
            $em = $this->doctrine->getEntityManager();
            $node->setStatus($status);
            $em->persist($node);

            if ($nodeType == Node::NODE_TYPE_FILE) {
                $this->fileManager->modifyStatus($node->getFile(), $status);
            }

            $em->flush();

            if ($recursive && $nodeType == Node::NODE_TYPE_DIRECTORY) {
                $nodeChildList = $em->getRepository('KitpagesEdmBundle:Node')->children($node, true);
                foreach($nodeChildList as $nodeChild) {
                    $this->modifyStatusNode($nodeChild, $status, false);
                }
            }
        }
        $this->dispatcher->dispatch(KitpagesEdmEvents::afterModifyStatusNode, $event);
    }

    public function parseAction(array $action, $node)
    {
        if (isset($action['route'])) {
            foreach($action['route']['parameterList'] as $parameter => $parameterValue) {
                $findMethod = preg_match(
                    '/\$\$([a-zA-Z0-9_\.]+)\$\$/',
                    $parameterValue,
                    $matches
                );
                if ($findMethod) {
                    $method = $matches[1];
                    $action['route']['parameterList'][$parameter] = $node->$method();
                }
            }
            $action['url'] = $this->router->generate(
                $action['route']['name'],
                $action['route']['parameterList']
            );
        } elseif (isset($action['url'])) {
            $findMethod = preg_match_all(
                '/\$\$([a-zA-Z0-9_\.]+)\$\$/',
                $action['url'],
                $matches
            );
            if ($findMethod > 0) {
                foreach($matches[1] as $key => $method) {
                   $action['url'] = str_replace($matches[0][$key], $node->$method(), $action['url']);
                }
            }
        }
        return $action;
    }

    public function export($node) {
        $zip = new \ZipArchive();
        $filename = tempnam($this->fileManager->tmpDir, $node->getId());
        if ($zip->open($filename, \ZIPARCHIVE::OVERWRITE )!==TRUE) {
            //error
        }
        $this->addNodeExport($zip, $node, true);
        $zip->close();
        if ($zip->status != 0) {
            throw new \Exception('Zip no create');
        }
        return $filename;
    }

    public function addNodeExport($zip, $node, $recursive, $dir = '')
    {

        $nodeType = $node->getNodeType();
        $em = $this->doctrine->getEntityManager();
        if ($nodeType == Node::NODE_TYPE_FILE) {
            $zip->addFromString($dir . $node->getLabel(), $this->fileManager->getFileContent($node->getFile()));
        }
        if ($recursive && $nodeType == Node::NODE_TYPE_DIRECTORY) {
            $zip->addEmptyDir($node->getLabel());
            $dir = $dir . $node->getLabel().'/';
            $nodeChildList = $em->getRepository('KitpagesEdmBundle:Node')->getChildrenNoDisable($node);
            foreach($nodeChildList as $nodeChild) {
                $this->addNodeExport($zip, $nodeChild, $recursive, $dir);
            }
        }


    }

}
