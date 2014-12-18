<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Template;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @codeCoverageIgnore
 */
class TemplateCompilerPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
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
