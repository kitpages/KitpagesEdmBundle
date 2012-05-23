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



}
