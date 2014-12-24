<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Navigation;

use Maketok\Navigation\Dumper\DumperInterface;

interface NavigationInterface
{
    /**
     * @param  LinkInterface $link
     * @param  mixed         $parent
     * @return mixed
     */
    public function addLink(LinkInterface $link, $parent = null);

    /**
     * @return array
     */
    public function getNavigation();

    /**
     * @param  DumperInterface $dumper
     * @return self
     */
    public function addDumper(DumperInterface $dumper);

    /**
     * load configuration
     * @return void
     */
    public function initConfig();
}
