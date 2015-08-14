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

        $container->setParameter('mongolog_browser.mongo.collection_name', $config['mongo']['collection_name']);
        $container->setParameter('mongolog_browser.mongo.database_name', $config['mongo']['database_name']);
        $container->setDefinition('mongolog_browser.mongo.connection', new Definition('MongoClient'));

//        if (isset($config['doctrine']['connection_name'])) {
//            $container->setAlias('mongolog_browser.doctrine_dbal.connection', sprintf('doctrine.dbal.%s_connection', $config['doctrine']['connection_name']));
//        }

//        if (isset($config['doctrine']['connection'])) {
//            $connectionDefinition = new Definition('Doctrine\DBAL\Connection', array($config['doctrine']['connection']));
//            $connectionDefinition->setFactoryClass('Doctrine\DBAL\DriverManager');
//            $connectionDefinition->setFactoryMethod('getConnection');
//            $container->setDefinition('mongolog_browser.doctrine_dbal.connection', $connectionDefinition);
//        }
    }
}
