<?php
namespace Acts\SphinxRealTimeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    private $supportedDrivers = array('orm');

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('acts_sphinx_real_time');
        $rootNode->isRequired()
            ->children()
            ->scalarNode('enabled')->defaultTrue()->end()
            ->arrayNode('clients')
            ->prototype('array')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('log')->defaultValue('/var/log/searchd/searchd.log')->end()
	            ->scalarNode('query_log')->defaultValue('/var/log/searchd/query.log')->end()
	            ->scalarNode('pid_file')->defaultValue('/var/log/searchd/searchd.pid')->end()
                ->scalarNode('workers')->defaultValue('threads')->end()
                ->scalarNode('compat_sphinxql_magics')->defaultValue(0)->end()
                ->scalarNode('thread_stack')->defaultValue('2048K')->end()
                ->scalarNode('mysql_host')->defaultValue('127.0.0.1')->end()
                ->scalarNode('mysql_port')->defaultValue(9306)->end()
            ->end()
            ->end()
            ->end()
            ->arrayNode('indexes')
            ->prototype('array')
            ->children()
                ->arrayNode('fields')
                    ->prototype('scalar')
                    ->treatNullLike(array())
                    ->end()
                    ->end()
                ->arrayNode('attributes')
                    ->prototype('scalar')
                    ->treatNullLike('string')
                    ->end()
                    ->end()
                ->arrayNode('persistence')
                ->children()
                    ->scalarNode('model')->end()
                    ->scalarNode('identifier')->defaultValue('id')->end()
                    ->scalarNode('driver')
                        ->validate()
                            ->ifNotInArray($this->supportedDrivers)
                            ->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode($this->supportedDrivers))
                        ->end()
                    ->end()
                    ->arrayNode('provider')
                        ->children()
                            ->scalarNode('query_builder_method')->defaultValue('createQueryBuilder')->end()
                            ->scalarNode('batch_size')->defaultValue(100)->end()
                            ->scalarNode('clear_object_manager')->defaultTrue()->end()
                            ->scalarNode('service')->end()
                        ->end()
                    ->end()
                    ->arrayNode('listener')
                        ->children()
                            ->scalarNode('insert')->defaultTrue()->end()
                            ->scalarNode('update')->defaultTrue()->end()
                            ->scalarNode('delete')->defaultTrue()->end()
                            ->scalarNode('service')->end()
                            ->variableNode('is_indexable_callback')->defaultNull()->end()
                        ->end()
                    ->end()
                    ->arrayNode('finder')
                        ->children()
                           ->scalarNode('service')->end()
                        ->end()
                    ->end()
                    ->arrayNode('sphinx_to_model_transformer')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('hydrate')->defaultTrue()->end()
                            ->scalarNode('service')->end()
                        ->end()
                    ->end()
                    ->arrayNode('model_to_sphinx_transformer')
                        ->addDefaultsIfNotSet()
                            ->children()
                            ->scalarNode('service')->end()
                        ->end()
                    ->end()
                ->end()
                ->end()
                ->scalarNode('client')->defaultValue('default')->end()
                ->arrayNode('config')
                ->children()
                    ->scalarNode('type')->defaultValue('rt')->end()
                    ->scalarNode('path')->end()
                    ->scalarNode('morphology')->end()
                    ->scalarNode('min_prefix_len')->end()
                    ->scalarNode('min_infix_len')->end()
                    ->scalarNode('min_word_length')->end()
                    ->arrayNode('prefix_fields')->prototype('scalar')->end()->end()
                    ->arrayNode('infix_fields')->prototype('scalar')->end()->end()
                    ->booleanNode('enable_star')->end()
                    ->scalarNode('ngram_len')->end()
                    ->booleanNode('html_strip')->end()
                    ->scalarNode('dict')->end()
                ->end()
                ->end()
            ->end()
            ->end()
            ->end()
            ->end();
        return $treeBuilder;
    }

}
