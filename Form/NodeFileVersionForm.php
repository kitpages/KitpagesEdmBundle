<?php

namespace Kitpages\EdmBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotNullValidator;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\FileValidator;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

class NodeFileVersionForm extends AbstractType
{

    protected $kitpagesUtilHash = null;

    public function __construct($kitpages_util_hash = null, $dataUser = array())
    {
        $this->kitpagesUtilHash = $kitpages_util_hash;
        $dataUserInit = array(
            'userEmail' => '',
            'userId' => '',
            'userName' => '',
            'userIp' => ''
        );

        $this->dataUser = array_merge($dataUserInit, $dataUser);
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $data = $builder->getData();
        if ($this->kitpagesUtilHash != null) {
            $builder->add(
                "tokenEncrypted",
                "hidden",
                array(
                    "data" => $this->kitpagesUtilHash->getHash(
                        $this->dataUser['userId'],
                        $this->dataUser['userName'],
                        $this->dataUser['userEmail'],
                        $this->dataUser['userIp'],
                        session_id(),
                        $this->getName()
                    ),
                    'property_path' => false
                )
            );
        } else {
            $builder->add("tokenEncrypted", "hidden", array('property_path' => false));
        }

        $builder->add(
            "userEmail",
            "hidden",
            array(
                "data" => $this->dataUser['userEmail']
            )
        );
        $builder->add(
            "userName",
            "hidden",
            array(
                "data" => $this->dataUser['userName']
            )
        );
        $builder->add(
            "userId",
            "hidden",
            array(
                "data" => $this->dataUser['userId']
            )
        );
        $builder->add(
            "userIp",
            "hidden",
            array(
                "data" => $this->dataUser['userIp']
            )
        );

        $builder->add(
            'fileUpload',
            'file',
            array(
                "label" => "File"
            )
        );
        $builder->addValidator(new CallbackValidator(function(FormInterface $form) {
            $fieldFileUpload = $form->get('fileUpload');
            $validator      = new NotNullValidator();
            $constraint     = new NotNull();
            $isValid = $validator->isValid( $fieldFileUpload->getData(), $constraint );
            if ( ! $isValid ) {
                $fieldFileUpload->addError( new FormError( "You did not select a file." ) );
            }
        }));
        $builder->add(
            'versionNote',
            'textarea',
            array(
                'label' => 'Note version',
                'required' => false
            )
        );

        $builder->add(
            'node_id',
            'hidden',
            array(
                'required' => true
            )
        );

        $builder->add(
            'sendEmail',
            "choice",
            array(
                "label" => " ",
                "choices" => array(
                    '1' => 'Send a mail'
                ),
                "expanded" => true,
                "multiple" => true
            )
        );

    }

    public function getName()
    {
        return 'kitpages_edmbundle_nodefileversionform';
    }
}
