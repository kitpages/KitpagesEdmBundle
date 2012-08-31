<?php

namespace Kitpages\EdmBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Kitpages\EdmBundle\Entity\Node;
use Kitpages\EdmBundle\Form\NodeDirectoryForm;
use Kitpages\EdmBundle\Form\NodeFileForm;
use Kitpages\EdmBundle\Form\NodeFileVersionForm;
use Symfony\Component\HttpFoundation\Response;

class TreeController extends Controller
{

    public function widgetNodeTreeAction(
        $nodeId,
        $user = array(),
        $actionList = array(),
        $openTreeLevel = 0,
        $htmlTreeId = null
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


        $dataUser = array(
            'userEmail' => $user['email'],
            'userId' => $user['id'],
            'userName' => $user['name'],
            'userIp' => $this->get('request')->getClientIp()
        );
        $formFileVersion   = $this->createForm(new NodeFileVersionForm($hash, $dataUser));
        $nodeList = $treeManager->nodeInTree($node, null, $this->get('request')->getPathInfo());

        if ($htmlTreeId == null) {
            $htmlTreeId = "kit-edm-tree-".$nodeId;
        }

        $userPreferenceManager = $this->get('kitpages.edm.manager.userPreference');
        $userPreference = $userPreferenceManager->getPreference($node->getTreeId(), $user['name']);

        $data = array(
            'nodeChildren' => array($nodeList),
            'formDirectory'   => $formDirectory->createView(),
            'formFile'   => $formFile->createView(),
            'formFileVersion'   => $formFileVersion->createView(),
            'node' => $node,
            'treeId' => $htmlTreeId,
            'openTreeLevel' => $openTreeLevel,
            'kitEdmUserPreferenceTree' => $userPreference->getDataTree()
        );

        $data['test'] = 0;
        if (isset($_GET['test'])) {
           $data['test'] = $_GET['test'];
        }

        return $this->render('KitpagesEdmBundle:Tree:nodeTree.html.twig', $data);
    }

    public function saveUserPreferenceTreeAction(){
        $userPreferenceManager = $this->get('kitpages.edm.manager.userPreference');
        $request = $this->getRequest();

        $nodeId = $request->query->get("id", null);
        $actionTree = $request->query->get("action", null);
        $scopeTree = $request->query->get("scope", null);

        $userPreferenceManager->setPreferenceDataTreeState(
            $this->get('security.context')->getToken()->getUserName(),
            $nodeId,
            $actionTree,
            $scopeTree
        );

        return new Response(null);
    }

    public function saveUserPreferenceTreeScrollAction(){
        $userPreferenceManager = $this->get('kitpages.edm.manager.userPreference');
        $request = $this->getRequest();

        $scroll = $request->query->get("scroll", 0);
        $userPreferenceManager->setPreferenceDataTreeScroll(
            $this->get('security.context')->getToken()->getUserName(),
            $scroll
        );

        return new Response(null);
    }

}
