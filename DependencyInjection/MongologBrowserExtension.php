<?php

namespace Mongolog\Bundle\MongologBrowserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MongologBrowserExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('mongolog_browser.base_layout', $config['base_layout']);
        $container->setParameter('mongolog_browser.logs_per_page', $config['logs_per_page']);

        $container->setParameter('mongolog_browser.mongo.host', $config['mongo']['host']);
        $container->setParameter('mongolog_browser.mongo.collection', $config['mongo']['collection']);
        $container->setParameter('mongolog_browser.mongo.database', $config['mongo']['database']);
        $container->setParameter('mongolog_browser.mongo.username', $config['mongo']['username']);
        $container->setParameter('mongolog_browser.mongo.password', $config['mongo']['password']);
    }
}
