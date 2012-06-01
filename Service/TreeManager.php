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

use Kitpages\EdmBundle\KitpagesEdmEvents;

class TreeManager {
    ////
    // dependency injection
    ////
    protected $fileManager = null;
    protected $doctrine = null;
    protected $dispatcher = null;
    protected $router = null;
    protected $treeId = null;
    protected $actionList = array();

    public function __construct(
        Registry $doctrine,
        EventDispatcherInterface $dispatcher,
        RouterInterface $router,
        FileManager $fileManager,
        $idService
    )
    {
        $this->doctrine = $doctrine;
        $this->dispatcher = $dispatcher;
        $this->router = $router;
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
            $em->flush();

            // send after event
            $this->dispatcher->dispatch(KitpagesEdmEvents::afterNewVersionFileUpload, $event);
        }

    }

    public function nodeChildren($nodeParent)
    {
        $em = $this->doctrine->getEntityManager();

        $nodeList = $em->getRepository('KitpagesEdmBundle:Node')->children($nodeParent, true);

        $nodeListRenderer = array();

        foreach($nodeList as $node) {
            $event = new TreeEvent();
            $event->setNode($nodeParent);
            $this->getDispatcher()->dispatch(KitpagesEdmEvents::onDisplayNodeInTree, $event);
            if (!$event->isDefaultPrevented()) {

                $nodeTree = array();
                $nodeTree['id'] = $node->getId();
                $nodeTree['label'] = $node->getLabel();
                $nodeTree['nodeType'] =  $node->getNodeType();

                if ($node->getNodeType() == Node::NODE_TYPE_DIRECTORY) {
                    $nodeTree['actionList'][] = array(
                        'id' => '',
                        'label' => 'add directory',
                        'icon' => 'bundles/kitpagesedm/icon/add-directory.png',
                        'dataPopup' => array(
                            'fieldName' => 'kitpages_edmbundle_nodedirectoryform_parent_id',
                            'fieldValue'=> $node->getId(),
                            'rel' => "kitpages_edmbundle_nodedirectoryform"
                        )
                    );
                    $nodeTree['actionList'][] = array(
                        'id' => '',
                        'label' => 'add file',
                        'icon' => 'bundles/kitpagesedm/icon/add-file.png',
                        'dataPopup' => array(
                            'fieldName' => 'kitpages_edmbundle_nodefileform_parent_id',
                            'fieldValue'=> $node->getId(),
                            'rel' => "kitpages_edmbundle_nodefileform"
                        )
                    );
                    if (isset($this->actionList[Node::NODE_TYPE_DIRECTORY])) {
                        foreach($this->actionList[Node::NODE_TYPE_DIRECTORY] as $action) {
                            $nodeTree['actionList'][] = $this->parseAction($action, $node);
                        }
                    }
                } elseif ($node->getNodeType() == Node::NODE_TYPE_FILE) {
                    $nodeTree['url'] = $this->router->generate(
                        'kitpages_edm_view_node',
                        array(
                            'nodeId' => $node->getId()
                        )
                    );
                    $nodeTree['actionList'][] = array(
                        'id' => '',
                        'label' => 'add version',
                        'icon' => 'bundles/kitpagesedm/icon/add-file-version.png',
                        'dataPopup' => array(
                            'fieldName' => 'kitpages_edmbundle_nodefileversionform_node_id',
                            'fieldValue'=> $node->getId(),
                            'rel' => "kitpages_edmbundle_nodefileversionform"
                        )
                    );
                    if (isset($this->actionList[Node::NODE_TYPE_FILE])) {
                        foreach($this->actionList[Node::NODE_TYPE_FILE] as $action) {
                            $nodeTree['actionList'][] = $this->parseAction($action, $node);
                        }
                    }
                }

            }
            $event->set("nodeTree", $nodeTree);
            $this->getDispatcher()->dispatch(KitpagesEdmEvents::afterDisplayNodeInTree, $event);
            $nodeTree = $event->get("nodeTree");
            $nodeTree['children'] = $this->nodeChildren($node);
            $nodeListRenderer[] = $nodeTree;

        }

        return $nodeListRenderer;
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
                echo var_dump($matches);
                foreach($matches[1] as $key => $method) {
                   $action['url'] = str_replace($matches[0][$key], $node->$method(), $action['url']);
                }
            }
        }
        return $action;
    }




}
