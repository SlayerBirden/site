<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\App\Helper;

use Maketok\App\ContainerFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Trait that adds IoC getter
 * @codeCoverageIgnore
 */
trait ContainerTrait
{

    /**
     * @return ContainerBuilder
     */
    public function getServiceContainer()
    {
        return ContainerFactory::getInstance()->getServiceContainer();
    }

    /**
     * alias
     * @return ContainerBuilder
     */
    public function getSC()
    {
        return $this->getServiceContainer();
    }

    /**
     * yet another alias
     * @return ContainerBuilder
     */
    public function ioc()
    {
        return $this->getServiceContainer();
    }
}
