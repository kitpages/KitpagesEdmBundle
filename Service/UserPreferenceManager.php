<?php
namespace Kitpages\EdmBundle\Service;

use Symfony\Bundle\DoctrineBundle\Registry;

use Kitpages\EdmBundle\Entity\UserPreference;
use Kitpages\EdmBundle\Entity\Node;

class UserPreferenceManager
{
    ////
    // dependency injection
    ////
    protected $doctrine = null;


    public function __construct(
        Registry $doctrine
    )
    {
        $this->doctrine = $doctrine;
    }

     ////
    // actions
    ////
    public function getPreference($treeId, $userName)
    {
        $em = $this->doctrine->getEntityManager();
        $userPreference = $em->getRepository('KitpagesEdmBundle:UserPreference')->findOneBy(
            array(
                'userName' => $userName,
                'treeId' => $treeId
            )
        );
        if (!($userPreference instanceof UserPreference)) {
            $userPreference = $this->setUserPreference($treeId, $userName);
        }
        return $userPreference;
    }

    public function setUserPreference($treeId, $userName) {
        $em = $this->doctrine->getEntityManager();
        $userPreference = new UserPreference();
        $userPreference->setUserName($userName);
        $userPreference->setTreeId($treeId);
        $em->persist($userPreference);
        $em->flush();
        return $userPreference;
    }

    public function setPreferenceDataTreeState($userName, $nodeId, $action, $scope){
        $em = $this->doctrine->getEntityManager();
        $node = $em->getRepository('KitpagesEdmBundle:Node')->find($nodeId);
        $userPreference = $em->getRepository('KitpagesEdmBundle:UserPreference')->findOneBy(
            array(
                'userName' => $userName,
                'treeId' => $node->getTreeId()
            )
        );



        if ($userPreference instanceof UserPreference) {
            $dataTree = $userPreference->getDataTree();
            if ($scope == 'nodeAndChildren') {
                $nodeChildList = $em->getRepository('KitpagesEdmBundle:Node')->children($node);
                if ($action == 'expand') {
                    $dataTree['stateTree'][$node->getId()] = true;
                    foreach($nodeChildList as $nodeChild) {
                        $dataTree['stateTree'][$nodeChild->getId()] = true;
                    }
                } else {
                    $dataTree['stateTree'][$node->getId()] = false;
                    foreach($nodeChildList as $nodeChild) {
                        $dataTree['stateTree'][$nodeChild->getId()] = false;
                    }
                }
            } elseif ($scope == 'node' && $nodeId != null) {
                if ($action == 'expand') {
                    $dataTree['stateTree'][$nodeId] = true;
                } else {
                    $dataTree['stateTree'][$nodeId] = false;
                }
            }
            $userPreference->setDataTree($dataTree);
            $em->persist($userPreference);
            $em->flush();
        }
    }

    public function setPreferenceDataTreeScroll($userName, $scroll){
            $em = $this->doctrine->getEntityManager();
            $userPreference = $em->getRepository('KitpagesEdmBundle:UserPreference')->findOneByUserName($userName);
            if ($userPreference instanceof UserPreference) {
                $dataTree = $userPreference->getDataTree();
                $dataTree['scrollTree'] = $scroll;
                $userPreference->setDataTree($dataTree);
                $em->persist($userPreference);
                $em->flush();
            }
        }
    ////
    // event listener
    ////




}
