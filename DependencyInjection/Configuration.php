<?php

namespace Kitpages\EdmBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kitpages_edm');

        $this->addEdmSection($rootNode);

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }

    private function addEdmSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('tree_list')
                    ->useAttributeAsKey('edm')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('kitpages_file_system_id')
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('tmp_dir')
                                    ->defaultValue('%kernel.root_dir%/data/tmp/bundle/kitpagesedm')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
        ;
    }

}
