<?php

namespace Acts\SphinxRealTimeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Acts\SphinxRealTimeBundle\DependencyInjection\Compiler\RegisterProvidersPass;
use Acts\SphinxRealTimeBundle\DependencyInjection\Compiler\TransformerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class ActsSphinxRealTimeBundle extends Bundle
{
    /**
     * @see Symfony\Component\HttpKernel\Bundle\Bundle::build()
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterProvidersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new TransformerPass());
    }
}
