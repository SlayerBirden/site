<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class TemplateCompilerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('template_engine')) {
            return;
        }

        $definition = $container->getDefinition(
            'template_engine'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'twig.extension'
        );
        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addExtension',
                array(new Reference($id))
            );
        }
    }
}
