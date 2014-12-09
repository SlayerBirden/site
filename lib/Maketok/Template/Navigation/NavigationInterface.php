<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template\Navigation;

use Maketok\Template\Navigation\Dumper\DumperInterface;

interface NavigationInterface
{

    /**
     * @param LinkInterface $link
     * @param string $parent
     * @return mixed
     */
    public function addLink(LinkInterface $link, $parent = null);

    /**
     * @return array
     */
    public function getNavigation();

    /**
     * @param DumperInterface $dumper
     * @return self
     */
    public function addDumper(DumperInterface $dumper);
}
