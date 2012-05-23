<?php

namespace Kitpages\EdmBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\FormError;
use Kitpages\EdmBundle\Entity\Node;
use Kitpages\EdmBundle\Service\TreeMap;
use Kitpages\UtilBundle\Service\Hash;

class NodeDirectoryFormHandler
{
    protected $request;
    protected $doctrine;
    protected $treeMap;
    protected $kitpagesUtilHash;

    public function __construct(Registry $doctrine, Request $request, TreeMap $treeMap, Hash $kitpagesUtilHash)
    {
        $this->doctrine = $doctrine;
        $this->request = $request;
        $this->treeMap = $treeMap;
        $this->kitpagesUtilHash = $kitpagesUtilHash;
    }

    public function process(Form $form, $entity)
    {
        if ($this->request->getMethod() == 'POST' && $this->request->request->get($form->getName()) !== null) {
            $form->bindRequest($this->request);

            if ($form->isValid()) {

                $encrypted = $form["tokenEncrypted"]->getData();
                $check = $this->kitpagesUtilHash->checkHash(
                    $encrypted,
                    $entity->getUserId(),
                    $entity->getUserName(),
                    $entity->getUserEmail(),
                    $entity->getUserIp(),
                    session_id(),
                    $form->getName()
                );

                if ( $check ) {

                    $em = $this->doctrine->getEntityManager();

                    $parent_id = $form['parent_id']->getData();
                    $repositoryNode = $em->getRepository('KitpagesEdmBundle:Node');
                    $nodeParent = $repositoryNode->find($parent_id);

                    $entity->setParent($nodeParent);
                    $entity->setNodeType(Node::NODE_TYPE_DIRECTORY);
                    $em->persist($entity);
                    $em->flush();
                    $this->request->getSession()->setFlash('notice', 'Your directory is created');
                } else {
                    $this->request->getSession()->setFlash("error", "technical error, not uploaded");
                }
                return true;
            }
            $this->request->getSession()->setFlash('error', $this->getRenderErrorMessages($form));
        }
        return false;
    }

    private function getRenderErrorMessages(\Symfony\Component\Form\Form $form) {
        $errorFieldList = $this->getErrorMessages($form);
        $errorHtml = '<ul>';
        foreach($errorFieldList as $errorList) {
            if (is_array($errorList)) {
                foreach($errorList as $error) {
                    $errorHtml .= '<li>'.$error.'</li>';
                }
            } else {
                $errorHtml .= '<li>'.$errorList.'</li>';
            }
        }
        $errorHtml .= '</ul>';

        return $errorHtml;
    }

    private function getErrorMessages(\Symfony\Component\Form\Form $form) {
        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            $errors[] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
        }
        if ($form->hasChildren()) {
            foreach ($form->getChildren() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getErrorMessages($child);

                }
            }
        }
        return $errors;
    }

}
