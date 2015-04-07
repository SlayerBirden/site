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

use Maketok\App\Site;

/**
 * Trait that adds IoC Container getter
 * @codeCoverageIgnore
 */
trait ContainerTrait
{
    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getServiceContainer()
    {
        return Site::getContainerFactory()->getServiceContainer();
    }

    /**
     * alias
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getSC()
    {
        return $this->getServiceContainer();
    }

    /**
     * yet another alias
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function ioc()
    {
        return $this->getServiceContainer();
    }
}
