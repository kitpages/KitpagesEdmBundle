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
        $actionList = array()
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
        $nodeChildren = $this->nodeChildren($node);


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

        return $this->render('KitpagesEdmBundle:Tree:nodeTree.html.twig', array(
            'nodeChildren' => $nodeChildren,
            'formDirectory'   => $formDirectory->createView(),
            'formFile'   => $formFile->createView(),
            'formFileVersion'   => $formFileVersion->createView(),
            'node' => $node
        ));
    }

    public function nodeChildren($nodeParent){

        $em = $this->getDoctrine()->getEntityManager();

        $nodeList = $em->getRepository('KitpagesEdmBundle:Node')->children($nodeParent, true);

        $nodeListRenderer = array();
        foreach($nodeList as $node) {
            $nodeTree = array();
            $nodeTree['id'] = $node->getId();
            $nodeTree['label'] = $node->getLabel();
            $nodeTree['nodeType'] =  $node->getNodeType();
//            $paramUrl = array(
//                'id' => $page->getId(),
//                'kitpages_target' => $_SERVER["REQUEST_URI"]
//            );
//            $paramUrlCreate = array(
//                'parent_id' => $page->getId(),
//                'kitpages_target' => $_SERVER["REQUEST_URI"]
//            );
//            $paramUrlWithChild = array(
//                'id' => $page->getId(),
//                'children' => true,
//                'kitpages_target' => $_SERVER["REQUEST_URI"]
//            );


            if ($node->getNodeType() == Node::NODE_TYPE_DIRECTORY) {
                $nodeTree['actionList'][] = array(
                    'id' => '',
                    'label' => 'add directory',
                    'icon' => 'icon/add-directory.png',
                    'dataPopup' => array(
                        'fieldName' => 'kitpages_edmbundle_nodedirectoryform_parent_id',
                        'fieldValue'=> $node->getId(),
                        'rel' => "kitpages_edmbundle_nodedirectoryform"
                    )
                );
                $nodeTree['actionList'][] = array(
                    'id' => '',
                    'label' => 'add file',
                    'icon' => 'icon/add-file.png',
                    'dataPopup' => array(
                        'fieldName' => 'kitpages_edmbundle_nodefileform_parent_id',
                        'fieldValue'=> $node->getId(),
                        'rel' => "kitpages_edmbundle_nodefileform"
                    )
                );
//                $pageTree['actionList'][] = array(
//                    'id' => '',
//                    'label' => 'confirm delete',
//                    'url' => $this->generateUrl('kitpages_cms_page_publish', $paramUrlWithChild),
//                    'class' => 'kit-cms-modal-open'
//                );

            } elseif ($node->getNodeType() == Node::NODE_TYPE_FILE) {
                $nodeTree['url'] = $this->generateUrl(
                    'kitpages_edm_view_node',
                    array(
                        'nodeId' => $node->getId()
                    )
                );
                $nodeTree['actionList'][] = array(
                    'id' => '',
                    'label' => 'add version',
                    'icon' => 'icon/add-file-version.png',
                    'dataPopup' => array(
                        'fieldName' => 'kitpages_edmbundle_nodefileversionform_node_id',
                        'fieldValue'=> $node->getId(),
                        'rel' => "kitpages_edmbundle_nodefileversionform"
                    )
                );
//                $pageTree['actionList'][] = array(
//                    'id' => 'publish',
//                    'label' => 'publish',
//                    'url'  => $this->generateUrl('kitpages_cms_page_publish', $paramUrl),
//                    'class' => ($page->getIsPublished() == '1')?'kit-cms-advanced':'',
//                    'icon' => 'icon/publish.png'
//                );
            }


            $nodeTree['children'] = $this->nodeChildren($node);
            $nodeListRenderer[] = $nodeTree;
        }
        return $nodeListRenderer;
    }

}
