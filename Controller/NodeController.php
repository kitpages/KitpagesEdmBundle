<?php

namespace Kitpages\EdmBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Kitpages\EdmBundle\Entity\Node;
use Kitpages\EdmBundle\Form\NodeDirectoryForm;
use Kitpages\EdmBundle\Form\NodeFileForm;
use Kitpages\EdmBundle\Form\NodeFileVersionForm;

class NodeController extends Controller
{

    public function ViewAction($nodeId)
    {
        $em = $this->get('doctrine')->getEntityManager();

        $repositoryNode = $em->getRepository('KitpagesEdmBundle:Node');
        $node = $repositoryNode->find($nodeId);

        $nodeType = $node->getNodeType();

        $target = $this->getRequest()->query->get('kitpages_target', null);

        if ($nodeType == Node::NODE_TYPE_FILE) {
            return $this->render('KitpagesEdmBundle:Node:view.html.twig', array(
                'node' => $node
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

    public function addFileAction()
    {

        $entity = new Node();

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
            $this->get('request')->getSession()->setFlash('notice', 'File deleted');
        } elseif ($nodeType == Node::NODE_TYPE_DIRECTORY) {
            $this->get('request')->getSession()->setFlash('notice', 'Directory deleted');
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
            $this->get('request')->getSession()->setFlash('notice', 'File retrieve');
        } elseif ($nodeType == Node::NODE_TYPE_DIRECTORY) {
            $this->get('request')->getSession()->setFlash('notice', 'Directory retrieve');
        }
        $target = $this->getRequest()->query->get('kitpages_target', null);

        return $this->redirect($target);
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
            $this->get('request')->getSession()->setFlash('notice', 'File deleted definitely');
        } elseif ($nodeType == Node::NODE_TYPE_DIRECTORY) {
            $this->get('request')->getSession()->setFlash('notice', 'Directory deleted definitely');
        }
        $target = $this->getRequest()->query->get('kitpages_target', null);

        return $this->redirect($target);
    }

}
