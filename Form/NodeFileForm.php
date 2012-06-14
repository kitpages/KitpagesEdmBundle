<?php

namespace Kitpages\EdmBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Symfony\Bundle\DoctrineBundle\Registry;

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
                'required' => false,
                'property_path' => false
            )
        );

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
