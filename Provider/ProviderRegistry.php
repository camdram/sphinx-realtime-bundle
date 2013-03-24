<?php

namespace Acts\SphinxRealTimeBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * References persistence providers for each index and type.
 */
class ProviderRegistry implements ContainerAwareInterface
{
    private $container;
    private $providers = array();

    /**
     * Registers a provider for the specified index and type.
     *
     * @param string $index
     * @param string $type
     * @param string $providerId
     */
    public function addProvider($index, $providerId)
    {
        $this->providers[$index] = $providerId;
    }

    /**
     * Gets all registered providers.
     *
     * Providers will be indexed by "index/type" strings in the returned array.
     *
     * @return array of ProviderInterface instances
     */
    public function getAllProviders()
    {
        $providers = array();

        foreach ($this->providers as $index => $providerId) {
            $providers[$index] = $this->container->get($providerId);
        }

        return $providers;
    }

    /**
     * Gets all providers for an index.
     *
     * Providers will be indexed by "type" strings in the returned array.
     *
     * @param string  $index
     * @return array of ProviderInterface instances
     * @throws InvalidArgumentException if no providers were registered for the index
     */
    public function getIndexProvider($index)
    {
        if (!isset($this->providers[$index])) {
            throw new \InvalidArgumentException(sprintf('No provider was registered for index "%s".', $index));
        }

        return $this->container->get($this->providers[$index]);
    }

    /**
     * Gets the provider for an index and type.
     *
     * @param string $index
     * @param string $type
     * @return ProviderInterface
     * @throws InvalidArgumentException if no provider was registered for the index and type
     */
    public function getProvider($index)
    {
        if (!isset($this->providers[$index])) {
            throw new \InvalidArgumentException(sprintf('No provider was registered for index "%s".', $index));
        }

        return $this->container->get($this->providers[$index]);
    }

    /**
     * @see Symfony\Component\DependencyInjection\ContainerAwareInterface::setContainer()
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
