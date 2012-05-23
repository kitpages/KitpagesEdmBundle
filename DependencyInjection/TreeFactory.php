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
class TreeFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $id, array $config)
    {
        $container
            ->setDefinition($id, new DefinitionDecorator('kitpages_edm.tree.manager'))
            ->addArgument(new Reference('doctrine'))
            ->addArgument(new Reference('event_dispatcher'))
            ->addArgument(new Reference('router'))
            ->addArgument(new Reference($config['kitpages_edm_filemanager_id']))
            ->addArgument($id)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'tree';
    }
}
