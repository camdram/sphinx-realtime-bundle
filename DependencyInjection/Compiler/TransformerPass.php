<?php

namespace Acts\SphinxRealTimeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use InvalidArgumentException;

/**
 * Registers Transformer implementations into the TransformerCollection.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class TransformerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('acts.sphinx_realtime.sphinx_to_model_transformer.collection.prototype')) {
            return;
        }

        $transformers = array();

        foreach ($container->findTaggedServiceIds('acts.sphinx_realtime.sphinx_to_model_transformer') as $id => $tags) {
            foreach ($tags as $tag) {
                if (empty($tag['index']) || empty($tag['type'])) {
                    throw new InvalidArgumentException('The Transformer must have both a type and an index defined.');
                }

                $transformers[$tag['index']][$tag['type']]= new Reference($id);
            }
        }

        foreach ($transformers as $index => $indexTransformers) {
            if (!$container->hasDefinition(sprintf('acts.sphinx_realtime.sphinx_to_model_transformer.collection.%s', $index))) {
                continue;
            }

            $index = $container->getDefinition(sprintf('acts.sphinx_realtime.sphinx_to_model_transformer.collection.%s', $index));
            $index->replaceArgument(0, $indexTransformers);
        }
    }
}