<?php

namespace Kitpages\EdmBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Kitpages\EdmBundle\Entity\File;
use Kitpages\EdmBundle\Entity\Node;
use Kitpages\EdmBundle\Form\NodeDirectoryEditForm;
use Kitpages\EdmBundle\Form\NodeDirectoryForm;
use Kitpages\EdmBundle\Form\NodeFileForm;
use Kitpages\EdmBundle\Form\NodeFileVersionForm;
use Kitpages\FileSystemBundle\Model\AdapterFile;
use Symfony\Component\HttpFoundation\Response;

class NodeController extends Controller
{

    public function widgetNodeAction(
        $nodeId,
        $user = array(),
        $actionList = array()
    )
    {
        $em = $this->get('doctrine')->getEntityManager();

        $repositoryNode = $em->getRepository('KitpagesEdmBundle:Node');
        $node = $repositoryNode->find($nodeId);
        $treeManager = $this->get('kitpages_edm.tree_map')->getEdm($node->getTreeId());
        $nodeType = $node->getNodeType();

        $target = $this->getRequest()->query->get('kitpages_target', null);

        $hash = $this->get('kitpages_util.hash');
        $dataUser = array(
            'userEmail' => $user['email'],
            'userId' => $user['id'],
            'userName' => $user['name'],
            'userIp' => $this->get('request')->getClientIp()
        );

        if ($nodeType == Node::NODE_TYPE_FILE) {

            $formFileVersion   = $this->createForm(new NodeFileVersionForm($hash, $dataUser));

            $kitpages_target = $this->get('router')->generate(
                'kitpages_edm_view_node',
                array(
                    'nodeId' => $node->getId(),
                    'kitpages_target' => $target
                )
            );

            return $this->render('KitpagesEdmBundle:Node:widgetNode.html.twig', array(
                'node' => $node,
                'formFileVersion'   => $formFileVersion->createView(),
                'actionList' => $treeManager->nodeFileDetailActionList($node, array(), $kitpages_target)
            ));
        } else {
            return new Response(null);
        }
    }


    public function ViewAction($nodeId)
    {
        $em = $this->get('doctrine')->getEntityManager();

        $repositoryNode = $em->getRepository('KitpagesEdmBundle:Node');
        $node = $repositoryNode->find($nodeId);
        $treeManager = $this->get('kitpages_edm.tree_map')->getEdm($node->getTreeId());
        $nodeType = $node->getNodeType();

        $target = $this->getRequest()->query->get('kitpages_target', null);
        $hash = $this->get('kitpages_util.hash');
        $dataUser = array();
        if ($nodeType == Node::NODE_TYPE_FILE) {

            $formFileVersion   = $this->createForm(new NodeFileVersionForm($hash, $dataUser));

            $kitpages_target = $this->get('router')->generate(
                'kitpages_edm_view_node',
                array(
                    'nodeId' => $node->getId(),
                    'kitpages_target' => $target
                )
            );

            return $this->render('KitpagesEdmBundle:Node:view.html.twig', array(
                'node' => $node,
                'formFileVersion'   => $formFileVersion->createView(),
                'actionList' => $treeManager->nodeFileDetailActionList($node, array(), $kitpages_target)
            ));
        } else {
            return $this->redirect($target);
        }
    }

    public function addDirectoryAction()
    {

        $entity = new Node();

        // build basic form
        $form   = $this->createForm(new NodeDirectoryForm(), $entity);

        $formHandler = $this->container->get('kitpages_edm.node.directory.form.handler');
        $process = $formHandler->process($form, $entity);

        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
    }

    public function editDirectoryAction($nodeId)
    {
        $em = $this->get('doctrine')->getEntityManager();

        $repositoryNode = $em->getRepository('KitpagesEdmBundle:Node');
        $entity = $repositoryNode->find($nodeId);

        // build basic form
        $form   = $this->createForm(new NodeDirectoryEditForm(), $entity);

        $formHandler = $this->container->get('kitpages_edm.node.directory.edit.form.handler');
        $process = $formHandler->process($form, $entity);

        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
    }

    public function addFileAction()
    {

        $entity = new Node();
        $entity->setLabel('label temp');
        // build basic form
        $form   = $this->createForm(new NodeFileForm(), $entity);

        $formHandler = $this->container->get('kitpages_edm.node.file.form.handler');
        $process = $formHandler->process($form, $entity);

        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }

    }

    public function addFileVersionAction()
    {
        // build basic form
        $form   = $this->createForm(new NodeFileVersionForm());

        $formHandler = $this->container->get('kitpages_edm.node.fileversion.form.handler');
        $process = $formHandler->process($form);

        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
    }

    public function disableNodeAction($nodeId)
    {
        $em = $this->get('doctrine')->getEntityManager();

        $repositoryNode = $em->getRepository('KitpagesEdmBundle:Node');
        $node = $repositoryNode->find($nodeId);
        $treeManager = $this->get('kitpages_edm.tree_map')->getEdm($node->getTreeId());

        $nodeType = $node->getNodeType();

        $treeManager->modifyStatusNode($node, Node::NODE_STATUS_DISABLE, true);
        if ($nodeType == Node::NODE_TYPE_FILE) {
            $this->get('request')->getSession()->setFlash('notice', $this->get('translator')->trans("File deleted"));
        } elseif ($nodeType == Node::NODE_TYPE_DIRECTORY) {
            $this->get('request')->getSession()->setFlash('notice', $this->get('translator')->trans("Directory deleted"));
        }
        $target = $this->getRequest()->query->get('kitpages_target', null);

        return $this->redirect($target);
    }

    public function retrieveNodeAction($nodeId)
    {
        $em = $this->get('doctrine')->getEntityManager();

        $repositoryNode = $em->getRepository('KitpagesEdmBundle:Node');
        $node = $repositoryNode->find($nodeId);
        $treeManager = $this->get('kitpages_edm.tree_map')->getEdm($node->getTreeId());

        $nodeType = $node->getNodeType();

        $treeManager->modifyStatusNode($node, null, true);
        if ($nodeType == Node::NODE_TYPE_FILE) {
            $this->get('request')->getSession()->setFlash('notice', $this->get('translator')->trans("File retrieve"));
        } elseif ($nodeType == Node::NODE_TYPE_DIRECTORY) {
            $this->get('request')->getSession()->setFlash('notice', $this->get('translator')->trans("Directory retrieve"));
        }
        $target = $this->getRequest()->query->get('kitpages_target', null);

        return $this->redirect($target);
    }

    public function exportAction($nodeId)
    {
        $em = $this->get('doctrine')->getEntityManager();

        $repositoryNode = $em->getRepository('KitpagesEdmBundle:Node');
        $node = $repositoryNode->find($nodeId);
        $treeManager = $this->get('kitpages_edm.tree_map')->getEdm($node->getTreeId());

        try {
            $filePathExport = $treeManager->export($node, null, true);
            $this->get('kitpages.util')->getFile($filePathExport, 0, null, $node->getLabel().'-'.date('Y-m-d').'.zip');
        } catch (\Exception $e) {
            $this->getRequest()->getSession()->setFlash("error", $this->get('translator')->trans("technical error, ".$e->getMessage()));
            $target = $this->getRequest()->query->get('kitpages_target', null);
            return $this->redirect($target);
        }
        return null;
    }

    public function deleteNodeAction($nodeId)
    {
        $em = $this->get('doctrine')->getEntityManager();

        $repositoryNode = $em->getRepository('KitpagesEdmBundle:Node');
        $node = $repositoryNode->find($nodeId);
        $treeManager = $this->get('kitpages_edm.tree_map')->getEdm($node->getTreeId());

        $nodeType = $node->getNodeType();

        $treeManager->deleteNode($node, true);
        if ($nodeType == Node::NODE_TYPE_FILE) {
            $this->get('request')->getSession()->setFlash('notice', $this->get('translator')->trans("File deleted definitely"));
        } elseif ($nodeType == Node::NODE_TYPE_DIRECTORY) {
            $this->get('request')->getSession()->setFlash('notice', $this->get('translator')->trans("Directory deleted definitely"));
        }
        $target = $this->getRequest()->query->get('kitpages_target', null);

        return $this->redirect($target);
    }

    public function renderFileAction(){
        ini_set('memory_limit', '512M');
        $em = $this->getDoctrine()->getEntityManager();
        $fileId = $this->getRequest()->query->get('id', null);

        if (!is_null($fileId)) {
            $file = $em->getRepository('KitpagesEdmBundle:File')->find($fileId);
            $node = $em->getRepository('KitpagesEdmBundle:Node')->getNodeByFileId($fileId);
            $treeManager = $this->get('kitpages_edm.tree_map')->getEdm($node->getTreeId());
            $fileManager = $treeManager->getFileManager();
            if ($file != null) {
                $fileManager->getFileSystem()->sendFileToBrowser(
                    new AdapterFile($fileManager->getFilePath($file)),
                    $file->getFileName()
                );
            }
        }
        return null;
    }

    public function deleteOldFileVersionAction($nodeId){
        $em = $this->get('doctrine')->getEntityManager();

        $repositoryNode = $em->getRepository('KitpagesEdmBundle:Node');
        $node = $repositoryNode->find($nodeId);
        $treeManager = $this->get('kitpages_edm.tree_map')->getEdm($node->getTreeId());

        $nodeType = $node->getNodeType();

        $treeManager->deleteOldFileVersion($node, true);
        if ($nodeType == Node::NODE_TYPE_FILE) {
            $this->get('request')->getSession()->setFlash('notice', $this->get('translator')->trans("Older versions of the file are deleted"));
        } elseif ($nodeType == Node::NODE_TYPE_DIRECTORY) {
            $this->get('request')->getSession()->setFlash('notice', $this->get('translator')->trans("Older versions of files in the directory are deleted"));
        }
        $target = $this->getRequest()->query->get('kitpages_target', null);

        return $this->redirect($target);
    }

    public function moveUpAction($nodeId){

        $em = $this->get('doctrine')->getEntityManager();

        $repositoryNode = $em->getRepository('KitpagesEdmBundle:Node');
        $node = $repositoryNode->find($nodeId);
        $treeManager = $this->get('kitpages_edm.tree_map')->getEdm($node->getTreeId());
        $treeManager->moveUp($node, 1);
        $nodeType = $node->getNodeType();
        if ($nodeType == Node::NODE_TYPE_FILE) {
            $this->get('request')->getSession()->setFlash('notice', $this->get('translator')->trans("File moved"));
        } elseif ($nodeType == Node::NODE_TYPE_DIRECTORY) {
            $this->get('request')->getSession()->setFlash('notice', $this->get('translator')->trans("Directory moved"));
        }
        $target = $this->getRequest()->query->get('kitpages_target', null);
        return $this->redirect($target);
    }

    public function moveDownAction($nodeId){

        $em = $this->get('doctrine')->getEntityManager();

        $repositoryNode = $em->getRepository('KitpagesEdmBundle:Node');
        $node = $repositoryNode->find($nodeId);
        $treeManager = $this->get('kitpages_edm.tree_map')->getEdm($node->getTreeId());
        $treeManager->moveDown($node, 1);
        $nodeType = $node->getNodeType();
        if ($nodeType == Node::NODE_TYPE_FILE) {
            $this->get('request')->getSession()->setFlash('notice', $this->get('translator')->trans("File moved"));
        } elseif ($nodeType == Node::NODE_TYPE_DIRECTORY) {
            $this->get('request')->getSession()->setFlash('notice', $this->get('translator')->trans("Directory moved"));
        }
        $target = $this->getRequest()->query->get('kitpages_target', null);
        return $this->redirect($target);

    }

    public function uploadStatusAction($uploadProgressName){
        $this->get('request')->getSession()->setFlash('notice', $this->get('request')->getSession()->getFlash('notice'));
        $this->get('request')->getSession()->setFlash('error', $this->get('request')->getSession()->getFlash('error'));

        if (isset( $_SESSION['upload_progress_'.$uploadProgressName])) {
            return new Response(json_encode( $_SESSION['upload_progress_'.$uploadProgressName]));
        } else {
            return new Response(null);
        }
    }



}
