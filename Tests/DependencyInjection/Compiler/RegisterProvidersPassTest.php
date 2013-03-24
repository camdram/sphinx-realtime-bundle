<?php

namespace Acts\SphinxRealTimeBundle\Tests\DependencyInjection\Compiler;

use Acts\SphinxRealTimeBundle\DependencyInjection\Compiler\RegisterProvidersPass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterProvidersPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessShouldRegisterTaggedProviders()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterProvidersPass();

        $registryDefinition = new Definition();

        $container->setDefinition('acts.sphinx_realtime.provider_registry', $registryDefinition);
        $container->setAlias('acts.sphinx_realtime.index', 'acts.sphinx_realtime.index.foo');

        $container->setDefinition('provider.a', $this->createProviderDefinition(array('index' => 'a')));
        $container->setDefinition('provider.foo', $this->createProviderDefinition(array('index' => 'foo')));
        $container->setDefinition('provider.bar', $this->createProviderDefinition(array('index' => 'bar')));

        $pass->process($container);

        $calls = $registryDefinition->getMethodCalls();

        $this->assertEquals(array('addProvider', array('a', 'provider.a')), $calls[0]);
        $this->assertEquals(array('addProvider', array('foo', 'provider.foo')), $calls[1]);
        $this->assertEquals(array('addProvider', array('bar', 'provider.bar')), $calls[2]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testProcessShouldRequireProviderImplementation()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterProvidersPass();

        $container->setDefinition('acts.sphinx_realtime.provider_registry', new Definition());
        $container->setAlias('acts.sphinx_realtime.index', 'acts.sphinx_realtime.index.foo');

        $providerDef = $this->createProviderDefinition();
        $providerDef->setClass('stdClass');

        $container->setDefinition('provider.foo.a', $providerDef);

        $pass->process($container);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testProcessShouldRequireTypeAttribute()
    {
        $container = new ContainerBuilder();
        $pass = new RegisterProvidersPass();

        $container->setDefinition('acts.sphinx_realtime.provider_registry', new Definition());
        $container->setAlias('acts.sphinx_realtime.index', 'acts.sphinx_realtime.index.foo');

        $container->setDefinition('provider.foo.a', $this->createProviderDefinition());

        $pass->process($container);
    }

    private function createProviderDefinition(array $attributes = array())
    {
        $provider = $this->getMock('Acts\SphinxRealTimeBundle\Provider\ProviderInterface');

        $definition = new Definition(get_class($provider));
        $definition->addTag('acts.sphinx_realtime.provider', $attributes);

        return $definition;
    }
}
