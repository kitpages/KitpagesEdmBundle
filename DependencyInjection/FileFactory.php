<?php

namespace Kitpages\EdmBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Tree factory
 *
 */
class FileFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $id, array $config)
    {
        $container
            ->setDefinition($id, new DefinitionDecorator('kitpages_edm.file.manager'))
            ->addArgument(new Reference('doctrine'))
            ->addArgument(new Reference('event_dispatcher'))
            ->addArgument(new Reference('router'))
            ->addArgument(new Reference('kitpages_file_system.file_system.'.$config['kitpages_file_system_id']))
            ->addArgument($config['tmp_dir'])
            ->addArgument($config['version_number_to_keep'])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'file';
    }
}
