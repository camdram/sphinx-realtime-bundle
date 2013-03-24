<?php

namespace Acts\SphinxRealTimeBundle\Tests\Provider;

use Acts\SphinxRealTimeBundle\Provider\ProviderRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProviderRegistryTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $registry;

    public function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        // Mock ContainerInterface::get() to return the service ID
        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnArgument(0));

        $this->registry = new ProviderRegistry();
        $this->registry->setContainer($this->container);

        $this->registry->addProvider('fooa', 'provider.foo.a');
        $this->registry->addProvider('foob', 'provider.foo.b');
        $this->registry->addProvider('fooc', 'provider.foo.c');
        $this->registry->addProvider('bara', 'provider.bar.a');
        $this->registry->addProvider('barb', 'provider.bar.b');
    }

    public function testGetAllProviders()
    {
        $allProviders = array(
            'fooa' => 'provider.foo.a',
            'foob' => 'provider.foo.b',
            'fooc' => 'provider.foo.c',
            'bara' => 'provider.bar.a',
            'barb' => 'provider.bar.b',
        );

        $this->assertEquals($allProviders, $this->registry->getAllProviders());
    }

    public function testGetIndexProviders()
    {
        $fooProviders = 'provider.foo.a';

        $barProviders = 'provider.bar.b';

        $this->assertEquals($fooProviders, $this->registry->getIndexProvider('fooa'));
        $this->assertEquals($barProviders, $this->registry->getIndexProvider('barb'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetIndexProvidersWithInvalidIndex()
    {
        $this->registry->getIndexProvider('baz');
    }

    public function testGetProvider()
    {
        $this->assertEquals('provider.foo.a', $this->registry->getProvider('fooa'));
        $this->assertEquals('provider.foo.b', $this->registry->getProvider('foob'));
        $this->assertEquals('provider.foo.c', $this->registry->getProvider('fooc'));
        $this->assertEquals('provider.bar.a', $this->registry->getProvider('bara'));
        $this->assertEquals('provider.bar.b', $this->registry->getProvider('barb'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetProviderWithInvalidIndexAndType()
    {
        $this->registry->getProvider('barc');
    }
}
