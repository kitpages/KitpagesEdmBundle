<?php

namespace Kitpages\EdmBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Symfony\Bundle\DoctrineBundle\Registry;

class NodeFileVersionForm extends AbstractType
{

    public function buildForm(FormBuilder $builder, array $options)
    {

        $builder->add(
            'fileUpload',
            'file',
            array(
                "label" => "File",
                'required' => false
            )
        );

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
