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

class NodeFileForm extends AbstractType
{

    protected $kitpagesUtilHash = null;

    public function __construct($kitpages_util_hash = null)
    {
        $this->kitpagesUtilHash = $kitpages_util_hash;
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
                        $data->getUserId(),
                        $data->getUserName(),
                        $data->getUserEmail(),
                        $data->getUserIp(),
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
                "data" => $data->getUserEmail()
            )
        );
        $builder->add(
            "userName",
            "hidden",
            array(
                "data" => $data->getUserName()
            )
        );
        $builder->add(
            "userId",
            "hidden",
            array(
                "data" => $data->getUserId()
            )
        );
        $builder->add(
            "userIp",
            "hidden",
            array(
                "data" => $data->getUserIp()
            )
        );
        $builder->add(
            'fileUpload',
            'file',
            array(
                "label" => "File",
                'property_path' => false
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
            'Title',
            'text',
            array(
                'label' => 'title',
                'required' => false,
            )
        );

        $builder->add(
            'Comment',
            'textarea',
            array(
                'label' => 'comment',
                'required' => false,
            )
        );

        $builder->add(
            'parent_id',
            'hidden',
            array(
                'required' => true,
                'property_path' => false
            )
        );

        $builder->add(
            'sendEmail',
            "choice",
            array(
                "label" => " ",
                'property_path' => false,
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
        return 'kitpages_edmbundle_nodefileform';
    }
}
