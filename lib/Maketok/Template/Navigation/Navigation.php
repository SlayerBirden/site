<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Template\Navigation;

use Maketok\Template\Navigation\Dumper\DumperInterface;
use Maketok\Util\ArrayValueTrait;

class Navigation implements NavigationInterface
{
    use ArrayValueTrait;

    /**
     * @var LinkInterface
     */
    protected $tree;

    /**
     * @var \SplStack
     */
    protected $dumpers;

    /**
     * init tree
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->tree = new Link('root');
        $this->dumpers = new \SplStack();
    }

    /**
     * {@inheritdoc}
     */
    public function addLink(LinkInterface $link, $parent = null)
    {
        if (!is_null($parent)) {
            if (is_object($parent) && ($parent instanceof LinkInterface)) {
                $parent = $this->tree->findLink($parent);
            } elseif (is_string($parent)) {
                $parent = $this->tree->find($parent);
            } else {
                throw new \InvalidArgumentException(sprintf("Invalid parent provided: %s", gettype($parent)));
            }
            if (is_null($parent)) {
                throw new Exception("Provided parent is not within current context.");
            }
        } else {
            $parent = $this->tree->getRoot();
        }
        $parent->addChild($link);
    }

    /**
     * {@inheritdoc}
     */
    public function getNavigation()
    {
        $fullTree = $this->tree->asArray();

        return $this->getIfExists(array('root', 'children'), $fullTree, []);
    }

    /**
     * {@inheritdoc}
     */
    public function addDumper(DumperInterface $dumper)
    {
        $this->dumpers->push($dumper);
    }
}
