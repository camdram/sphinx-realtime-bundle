<?php
namespace Acts\SphinxRealTimeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ActsSphinxRealTimeExtension extends Extension
{
    protected $indexFields = array();
    protected $loadedDrivers = array();

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $clients = array();
        foreach ($config['clients'] as $id => $client) {
            $clients[$id] = $this->createClient($id, $client, $container);
        }
        $container->getDefinition('acts.sphinx_realtime.client_manager')
            ->addArgument($clients);

        $indexes = array();
        $numIndexes = count($config['indexes']);
        $offset = 0;
        foreach ($config['indexes'] as $id => $index) {
            $indexes[$id] = $this->createIndex($id, $index, $container, $config['enabled'], $offset, $numIndexes);
            $offset++;
        }
        $container->getDefinition('acts.sphinx_realtime.index_manager')
            ->addArgument($indexes);
    }

    private function createClient($id, $config, ContainerBuilder $container)
    {
        $config['listen'] = 'localhost:'.$config['mysql_port'].':mysql41';

        $def = new DefinitionDecorator('acts.sphinx_realtime.client.abstract');
        $def->addArgument($id)
            ->addArgument($config);
        $container->setDefinition('acts.sphinx_realtime.client.'.$id, $def);
        return new Reference('acts.sphinx_realtime.client.'.$id);
    }

    private function createIndex($id, $config, ContainerBuilder $container, $enabled, $offset, $numIndexes)
    {
        if (!isset($config['config']['path'])) {
            $config['config']['path'] = $container->getParameter('kernel.root_dir').'/data/sphinx/'.$id;
        }
        $path = dirname($config['config']['path']);
        if (!is_dir($path)) {
            if (false === @mkdir($path, 0777, true)) {
                throw new \RuntimeException(sprintf('Could not create sphinx data directory "%s".', $path));
            }
        }

        $def = new Definition('%acts.sphinx_realtime.index.class%');
        $def->addArgument($id)
            ->addArgument(new Reference('acts.sphinx_realtime.client.'.$config['client']))
            ->addArgument($config['fields'])
            ->addArgument($config['attributes'])
            ->addArgument($config['config']);

        $container->setDefinition('acts.sphinx_realtime.index.'.$id, $def);

        $this->indexFields[$id] = array_merge($config['fields'], array_keys($config['attributes']));
        if ($enabled && isset($config['persistence'])) {
            $this->loadIndexPersistenceIntegration($config['persistence'], $container, $def, $id, $offset, $numIndexes);
        }

        return new Reference('acts.sphinx_realtime.index.'.$id);
    }

    protected function loadIndexPersistenceIntegration(array $indexConfig, ContainerBuilder $container, Definition $typeDef, $indexId, $offset, $numIndexes)
    {
        $this->loadDriver($container, $indexConfig['driver']);

        $sphinxToModelTransformerId = $this->loadSphinxToModelTransformer($indexConfig, $container, $indexId, $numIndexes);
        $modelToSphinxTransformerId = $this->loadModelToSphinxTransformer($indexConfig, $container, $indexId, $offset, $numIndexes);
        $objectPersisterId            = $this->loadObjectPersister($indexConfig, $typeDef, $container, $indexId, $modelToSphinxTransformerId);

        if (isset($indexConfig['provider'])) {
            $this->loadIndexProvider($indexConfig, $container, $objectPersisterId, $typeDef, $indexId);
        }
        if (isset($indexConfig['finder'])) {
            $this->loadIndexFinder($indexConfig, $container, $sphinxToModelTransformerId, $typeDef, $indexId);
        }
        if (isset($indexConfig['listener'])) {
            $this->loadIndexListener($indexConfig, $container, $objectPersisterId, $typeDef, $indexId);
        }
    }

    protected function loadSphinxToModelTransformer(array $indexConfig, ContainerBuilder $container, $indexId, $numIndexes)
    {
        if (isset($indexConfig['sphinx_to_model_transformer']['service'])) {
            return $indexConfig['sphinx_to_model_transformer']['service'];
        }
        $abstractId = sprintf('acts.sphinx_realtime.sphinx_to_model_transformer.abstract.%s', $indexConfig['driver']);
        $serviceId = sprintf('acts.sphinx_realtime.sphinx_to_model_transformer.%s', $indexId);
        $serviceDef = new DefinitionDecorator($abstractId);
        $serviceDef->addTag('acts.sphinx_realtime.sphinx_to_model_transformer', array('index' => $indexId));

        // Doctrine has a mandatory service as first argument
        $argPos = ('propel' === $indexConfig['driver']) ? 0 : 1;

        $serviceDef->replaceArgument($argPos, $indexConfig['model']);
        $serviceDef->replaceArgument($argPos + 1, $numIndexes);
        $serviceDef->replaceArgument($argPos + 2, array(
            'identifier'    => $indexConfig['identifier'],
            'hydrate'       => $indexConfig['sphinx_to_model_transformer']['hydrate']
        ));
        $container->setDefinition($serviceId, $serviceDef);

        return $serviceId;
    }

    protected function loadModelToSphinxTransformer(array $indexConfig, ContainerBuilder $container, $indexId, $offset, $numIndexes)
    {
        if (isset($indexConfig['model_to_sphinx_transformer']['service'])) {
            return $indexConfig['model_to_sphinx_transformer']['service'];
        }
        $abstractId = sprintf('acts.sphinx_realtime.model_to_sphinx_transformer.abstract.auto');
        $serviceId = sprintf('acts.sphinx_realtime.model_to_sphinx_transformer.%s', $indexId);
        $serviceDef = new DefinitionDecorator($abstractId);
        $serviceDef->replaceArgument(0, array(
            'identifier' => $indexConfig['identifier']
        ));
        $serviceDef->addArgument($offset);
        $serviceDef->addArgument($numIndexes);
        $container->setDefinition($serviceId, $serviceDef);

        return $serviceId;
    }

    protected function loadObjectPersister(array $typeConfig, Definition $typeDef, ContainerBuilder $container, $indexId, $transformerId)
    {
        $abstractId = sprintf('acts.sphinx_realtime.object_persister.abstract');
        $serviceId = sprintf('acts.sphinx_realtime.object_persister.%s', $indexId);
        $serviceDef = new DefinitionDecorator($abstractId);

        $serviceDef->replaceArgument(0, $typeDef);
        $serviceDef->replaceArgument(1, new Reference($transformerId));
        $serviceDef->replaceArgument(2, $typeConfig['model']);
        $serviceDef->replaceArgument(3, $this->indexFields[$indexId]);
        $container->setDefinition($serviceId, $serviceDef);

        return $serviceId;
    }

    protected function loadIndexFinder(array $indexConfig, ContainerBuilder $container, $sphinxToModelId, $typeDef, $indexId)
    {
        if (isset($indexConfig['finder']['service'])) {
            $finderId = $indexConfig['finder']['service'];
        } else {
            $abstractFinderId = 'acts.sphinx_realtime.finder.abstract';
            $finderId = sprintf('acts.sphinx_realtime.finder.%s', $indexId);
            $finderDef = new DefinitionDecorator($abstractFinderId);
            $finderDef->replaceArgument(0, $typeDef);
            $finderDef->replaceArgument(1, new Reference($sphinxToModelId));

            $container->setDefinition($finderId, $finderDef);
        }

        $managerId = sprintf('acts.sphinx_realtime.manager.%s', $indexConfig['driver']);
        $managerDef = $container->getDefinition($managerId);
        $arguments = array($indexConfig['model'], new Reference($finderId));
        if (isset($indexConfig['repository'])) {
            $arguments[] = $indexConfig['repository'];
        }
        $managerDef->addMethodCall('addEntity', $arguments);

        return $finderId;
    }

    protected function loadIndexProvider(array $indexConfig, ContainerBuilder $container, $objectPersisterId, $typeDef, $indexId)
    {
        if (isset($indexConfig['provider']['service'])) {
            return $indexConfig['provider']['service'];
        }

        $providerId = sprintf('acts.sphinx_realtime.provider.%s', $indexId);
        $providerDef = new DefinitionDecorator('acts.sphinx_realtime.provider.abstract.' . $indexConfig['driver']);
        $providerDef->addTag('acts.sphinx_realtime.provider', array('index' => $indexId));
        $providerDef->replaceArgument(0, new Reference($objectPersisterId));
        $providerDef->replaceArgument(1, $indexConfig['model']);
        // Propel provider can simply ignore Doctrine-specific options
        $providerDef->replaceArgument(2, array_diff_key($indexConfig['provider'], array('service' => 1)));
        $container->setDefinition($providerId, $providerDef);

        return $providerId;
    }

    protected function loadIndexListener(array $indexConfig, ContainerBuilder $container, $objectPersisterId, $typeDef, $indexId)
    {
        if (isset($indexConfig['listener']['service'])) {
            return $indexConfig['listener']['service'];
        }
        $abstractListenerId = sprintf('acts.sphinx_realtime.listener.abstract.%s', $indexConfig['driver']);
        $listenerId = sprintf('acts.sphinx_realtime.listener.%s', $indexId);
        $listenerDef = new DefinitionDecorator($abstractListenerId);
        $listenerDef->replaceArgument(0, new Reference($objectPersisterId));
        $listenerDef->replaceArgument(1, $indexConfig['model']);
        $listenerDef->replaceArgument(3, $indexConfig['identifier']);
        $listenerDef->replaceArgument(2, $this->getDoctrineEvents($indexConfig));
        switch ($indexConfig['driver']) {
            case 'orm': $listenerDef->addTag('doctrine.event_subscriber'); break;
        }
        if (isset($indexConfig['listener']['is_indexable_callback'])) {
            $callback = $indexConfig['listener']['is_indexable_callback'];

            if (is_array($callback)) {
                list($class) = $callback + array(null);
                if (is_string($class) && !class_exists($class)) {
                    $callback[0] = new Reference($class);
                }
            }

            $listenerDef->addMethodCall('setIsIndexableCallback', array($callback));
        }
        $container->setDefinition($listenerId, $listenerDef);

        return $listenerId;
    }

    private function getDoctrineEvents(array $indexConfig)
    {
        $events = array();
        $eventMapping = array(
            'insert' => array('postPersist'),
            'update' => array('postUpdate'),
            'delete' => array('postRemove', 'preRemove')
        );

        foreach ($eventMapping as $event => $doctrineEvents) {
            if (isset($indexConfig['listener'][$event]) && $indexConfig['listener'][$event]) {
                $events = array_merge($events, $doctrineEvents);
            }
        }

        return $events;
    }

    protected function loadDriver(ContainerBuilder $container, $driver)
    {
        if (in_array($driver, $this->loadedDrivers)) {
            return;
        }
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load($driver.'.yml');
        $this->loadedDrivers[] = $driver;
    }

}
