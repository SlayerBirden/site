<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template\Symfony\Form;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @codeCoverageIgnore
 */
class FormTypeCompilerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('form_builder')) {
            return;
        }

        $definition = $container->getDefinition(
            'form_builder'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'form.type'
        );
        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addType',
                array(new Reference($id))
            );
        }
    }
}
