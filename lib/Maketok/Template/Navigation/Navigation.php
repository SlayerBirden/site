<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template\Navigation;

use Maketok\Template\Navigation\Dumper\DumperInterface;

class Navigation implements NavigationInterface
{

    /**
     * {@inheritdoc}
     */
    public function addLink(LinkInterface $link, $parent = null)
    {
        // TODO: Implement addLink() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getNavigation()
    {
        // TODO: Implement getNavigation() method.
    }

    /**
     * {@inheritdoc}
     */
    public function addDumper(DumperInterface $dumper)
    {
        // TODO: Implement addDumper() method.
    }
}
