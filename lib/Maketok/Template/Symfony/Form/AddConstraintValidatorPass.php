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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @codeCoverageIgnore
 */
class AddConstraintValidatorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('validator.validator_factory')) {
            return;
        }
        $validators = array();
        foreach ($container->findTaggedServiceIds('validator.constraint_validator') as $id => $attributes) {
            if (isset($attributes[0]['alias'])) {
                $validators[$attributes[0]['alias']] = $id;
            }
        }
        $container->getDefinition('validator.validator_factory')->replaceArgument(1, $validators);
    }
}
