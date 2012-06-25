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

    public function buildForm(FormBuilder $builder, array $options)
    {

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
                'required' => false,
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
