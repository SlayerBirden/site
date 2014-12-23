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

use Maketok\App\Helper\ContainerTrait;
use Maketok\App\Site;
use Maketok\Navigation\Dumper\DumperInterface;
use Maketok\Util\ArrayValueTrait;

class Navigation implements NavigationInterface
{
    use ArrayValueTrait;
    use ContainerTrait;

    /**
     * @var LinkInterface
     */
    protected $tree;

    /**
     * @var \SplStack
     */
    protected $dumpers;
    /**
     * @var string
     */
    protected $code;

    /**
     * init tree
     * @codeCoverageIgnore
     * @param string $code
     */
    public function __construct($code)
    {
        $this->tree = new Link('root');
        $this->dumpers = new \SplStack();
        $this->code = $code;
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

    /**
     * {@inheritdoc}
     */
    public function initConfig()
    {
        $tree = [];
        foreach ($this->ioc()->get('config_getter')
                     ->getConfig(Site::getConfig('navigation_config_path'), 'navigation', ENV) as $config) {
            $tree = array_replace_recursive($tree, $config);
        }
        $config = $this->getIfExists($this->code, $tree, []);
        if ($config) {
            $this->parseConfig($config, $this->tree);
        }
    }

    /**
     * @param array $config
     * @param LinkInterface $parent
     * @throws Exception
     */
    protected function parseConfig($config, LinkInterface $parent)
    {
        foreach ($config as $code => $link) {
            if (is_array($link)) {
                $href = $this->getIfExists('href', $link);
                $order = $this->getIfExists('order', $link);
                $title = $this->getIfExists('title', $link);
                $children = $this->getIfExists('children', $link);
                $node = $parent->addChild(new Link($code, $href, $order, $title));
                if ($children) {
                    $this->parseConfig($children, $node);
                }
            } else {
                throw new Exception(sprintf("Invalid link type given: %s", gettype($link)));
            }
        }
    }
}
