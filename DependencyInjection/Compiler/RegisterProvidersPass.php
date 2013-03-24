<?php

namespace Acts\SphinxRealTimeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RegisterProvidersPass implements CompilerPassInterface
{
    /**
     * Mapping of class names to booleans indicating whether the class
     * implements ProviderInterface.
     *
     * @var array
     */
    private $implementations = array();

    /**
     * @see Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface::process()
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('acts.sphinx_realtime.provider_registry')) {
            return;
        }

        $registry = $container->getDefinition('acts.sphinx_realtime.provider_registry');
        $providers = $container->findTaggedServiceIds('acts.sphinx_realtime.provider');

        foreach ($providers as $providerId => $tags) {
            $index = $type = null;
            $class = $container->getDefinition($providerId)->getClass();

            if (!$class || !$this->isProviderImplementation($class)) {
                throw new \InvalidArgumentException(sprintf('Sphinx provider "%s" with class "%s" must implement ProviderInterface.', $providerId, $class));
            }

            foreach ($tags as $tag) {
                if (!isset($tag['index'])) {
                    throw new \InvalidArgumentException(sprintf('Sphinx provider "%s" must specify the "index" attribute.', $providerId));
                }

                $index = $tag['index'];
            }

            $registry->addMethodCall('addProvider', array($index, $providerId));
        }
    }

    /**
     * Returns whether the class implements ProviderInterface.
     *
     * @param string $class
     * @return boolean
     */
    private function isProviderImplementation($class)
    {
        if (!isset($this->implementations[$class])) {
            $refl = new \ReflectionClass($class);
            $this->implementations[$class] = $refl->implementsInterface('Acts\SphinxRealTimeBundle\Provider\ProviderInterface');
        }

        return $this->implementations[$class];
    }
}
