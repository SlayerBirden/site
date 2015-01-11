<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Template\Symfony\Form;
use Maketok\App\Site;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @codeCoverageIgnore
 */
class ValidationBuilderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('validator_builder')) {
            return;
        }

        $definition = $container->getDefinition(
            'validator_builder'
        );

        $definition->addMethodCall(
            'addYamlMappings',
            array(Site::getConfig('validation_yaml_mapping_path'))
        );
    }
}
