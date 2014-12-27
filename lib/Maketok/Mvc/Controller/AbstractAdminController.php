<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Mvc\Controller;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractAdminController extends AbstractController
{
    /**
     * init
     * add base template path
     */
    public function __construct()
    {
        $this->addTemplatePath(AR . '/src/admin/view');
        // load view under admin
        $rc = new \ReflectionClass($this);
        $parent = dirname($rc->getFileName());
        if ('admin' === basename($parent)) {
            $root = dirname(dirname($parent));
            $view = $root . "/view/admin";
        } else {
            // try to load it normally
            $root = dirname($parent);
            $view = $root . "/view";
        }
        if (file_exists($view) && is_dir($view)) {
            $this->addTemplatePath($view);
        }
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        $defaults = parent::getDefaults();
        $defaults['links'] = $this->ioc()->get('topmenu')->getNavigation();
        return $defaults;
    }
}
