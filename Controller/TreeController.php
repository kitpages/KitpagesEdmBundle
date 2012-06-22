<?php

namespace Kitpages\EdmBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Kitpages\EdmBundle\Entity\Node;
use Kitpages\EdmBundle\Form\NodeDirectoryForm;
use Kitpages\EdmBundle\Form\NodeFileForm;
use Kitpages\EdmBundle\Form\NodeFileVersionForm;

class TreeController extends Controller
{

    public function widgetNodeTreeAction(
        $nodeId,
        $user = array(),
        $actionList = array(),
        $openTreeLevel = 0,
        $treeId = null
    )
    {


        $user = array_merge(
            array(
                'email' => '',
                'id' => '',
                'name' => ''
            ),
            $user
        );
        $em = $this->getDoctrine()->getEntityManager();
        $node = $em->getRepository('KitpagesEdmBundle:Node')->find($nodeId);


        $treeManager = $this->get('kitpages_edm.tree_map')->getEdm($node->getTreeId());
        $treeManager->setActionList($actionList);

        $entity = new Node();

        $entity->setUserEmail($user['email']);
        $entity->setUserId($user['id']);
        $entity->setUserName($user['name']);
        $entity->setUserIp($this->get('request')->getClientIp());

        $hash = $this->get('kitpages_util.hash');

        // build basic form
        $formDirectory   = $this->createForm(new NodeDirectoryForm($hash), $entity);

        $formFile   = $this->createForm(new NodeFileForm($hash), $entity);

        $formFileVersion   = $this->createForm(new NodeFileVersionForm());
        $nodeList = $treeManager->nodeInTree($node, null, $this->get('request')->getPathInfo());

        if ($treeId == null) {
            $treeId = "kit-edm-tree-".$nodeId;
        }

        return $this->render('KitpagesEdmBundle:Tree:nodeTree.html.twig', array(
            'nodeChildren' => array($nodeList),
            'formDirectory'   => $formDirectory->createView(),
            'formFile'   => $formFile->createView(),
            'formFileVersion'   => $formFileVersion->createView(),
            'node' => $node,
            'treeId' => $treeId,
            'openTreeLevel' => $openTreeLevel

        ));
    }
}
