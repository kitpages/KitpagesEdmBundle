<?php

namespace Kitpages\EdmBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\FormError;
use Kitpages\EdmBundle\Entity\Node;
use Kitpages\EdmBundle\Service\TreeMap;
use Kitpages\UtilBundle\Service\Hash;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class NodeFileVersionFormHandler
{
    protected $request;
    protected $doctrine;
    protected $treeMap;

    public function __construct(
        Registry $doctrine,
        Request $request,
        TreeMap $treeMap,
        Hash $kitpagesUtilHash,
        Translator $translator
    )
    {
        $this->doctrine = $doctrine;
        $this->request = $request;
        $this->treeMap = $treeMap;
        $this->kitpagesUtilHash = $kitpagesUtilHash;
        $this->translator = $translator;
    }

    public function process(Form $form)
    {
        if ($this->request->getMethod() == 'POST' && $this->request->request->get($form->getName()) !== null) {
            $form->bindRequest($this->request);

            if ($form->isValid()) {

                $encrypted = $form["tokenEncrypted"]->getData();
                $check = $this->kitpagesUtilHash->checkHash(
                    $encrypted,
                    $form['userId']->getData(),
                    $form['userName']->getData(),
                    $form['userEmail']->getData(),
                    $form['userIp']->getData(),
                    session_id(),
                    $form->getName()
                );

                if ( $check ) {
                    $em = $this->doctrine->getEntityManager();
                    $node_id = $form['node_id']->getData();
                    $versionNote = $form['versionNote']->getData();
                    $sendEmail = $form['sendEmail']->getData();

                    $repositoryNode = $em->getRepository('KitpagesEdmBundle:Node');
                    $node = $repositoryNode->find($node_id);

                    $node->setUserId($form['userId']->getData());
                    $node->setUserName($form['userName']->getData());
                    $node->setUserEmail($form['userEmail']->getData());
                    $node->setUserIp($form['userIp']->getData());

                    $em->persist($node);
                    $em->flush();

                    $treeManager = $this->treeMap->getEdm($node->getTreeId());
                    try {
                        $treeManager->uploadFile($node, $form['fileUpload']->getData(), $sendEmail, $versionNote);
                    } catch (\Exception $exc) {
                        $this->request->getSession()->setFlash("error", $this->translator->trans("technical error, not uploaded"));
                        return false;
                    }

                    $this->request->getSession()->setFlash('notice', $this->translator->trans('New version is uploaded'));

                } else {
                    $this->request->getSession()->setFlash("error", $this->translator->trans("technical error, not uploaded"));
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
