<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App\Helper;

use Maketok\App\ContainerFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

trait ContainerTrait
{

    /**
     * @codeCoverageIgnore
     * @return ContainerBuilder
     */
    public function getServiceContainer()
    {
        return ContainerFactory::getServiceContainer();
    }

    /**
     * alias
     * @codeCoverageIgnore
     * @return ContainerBuilder
     */
    public function getSC()
    {
        return $this->getServiceContainer();
    }

    /**
     * yet another alias
     * @codeCoverageIgnore
     * @return ContainerBuilder
     */
    public function ioc()
    {
        return $this->getServiceContainer();
    }
}
