<?php

namespace Kitpages\EdmBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Symfony\Bundle\DoctrineBundle\Registry;

class NodeDirectoryForm extends AbstractType
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
            'label',
            'text',
            array(
                'label' => 'Label',
                'required' => true,
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

    }

    public function getName()
    {
        return 'kitpages_edmbundle_nodedirectoryform';
    }
}
