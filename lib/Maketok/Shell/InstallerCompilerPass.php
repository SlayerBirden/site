<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Shell;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class InstallerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('setup_installer')) {
            return;
        }

        $definition = $container->getDefinition(
            'setup_installer'
        );

        $taggedProviders = $container->findTaggedServiceIds(
            'setup.provider'
        );
        foreach ($taggedProviders as $id => $attributes) {
            $definition->addMethodCall(
                'addProvider',
                array(new Reference($id))
            );
        }
        $taggedWorkers = $container->findTaggedServiceIds(
            'setup.worker'
        );
        foreach ($taggedWorkers as $id => $attributes) {
            $definition->addMethodCall(
                'addWorker',
                array(new Reference($id))
            );
        }
    }
}
