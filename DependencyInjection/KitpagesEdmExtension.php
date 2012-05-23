<?php

namespace Kitpages\EdmBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class KitpagesEdmExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $edmTreeFactory  = $container->get('kitpages_edm.factory.tree');

        $map = array();
        foreach ($config['tree_list'] as $treeName => $treeConf) {
                $treeId      = sprintf('kitpages_edm.tree.%s', $treeName);
                $fileId      = $treeId.'.file';

                $edmFileFactory = $container->get('kitpages_edm.factory.file');
                $edmFileFactory->create($container, $fileId, $treeConf);
                $treeConf['kitpages_edm_filemanager_id'] = $fileId;
                // create a edm service
                $edmTreeFactory->create($container, $treeId, $treeConf);
                $map[$treeName] = new Reference($treeId);
        }

        //allows calls to edm services with $this->get('kitpages_edm.tree_map')->getEdm(treeName);
        $container->getDefinition('kitpages_edm.tree_map')
            ->replaceArgument(0, $map);

    }
}
