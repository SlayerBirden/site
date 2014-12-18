<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Template;

use Maketok\App\Helper\ContainerTrait;

/**
 * @codeCoverageIgnore
 */
class Twig extends AbstractEngine
{
    use ContainerTrait;

    const CACHE_FOLDER = 'var/cache';

    /** @var object loaded template */
    protected $_template;

    /**
     * @var array
     */
    protected $_variables = array();

    /**
     * set loader, env
     */
    public function __construct()
    {
        $this->_engine = $this->ioc()->get('twig_env');
    }

    /**
     * @param \Twig_Extension $extension
     */
    public function addExtension(\Twig_Extension $extension)
    {
        $this->_engine->addExtension($extension);
    }

    /**
     * @param string $path
     */
    public function addPath($path)
    {
        $this->_engine->getLoader()->addPath($path);
    }

    /**
     * load the template into engine by path
     * @param string $path
     * @return mixed
     */
    public function loadTemplate($path)
    {
        $this->addPath(dirname($path));
        $this->_template = $this->_engine->loadTemplate(basename($path));
    }

    /**
     * set the array of variables to use
     * @param array $variables
     * @return mixed
     */
    public function setVariables(array $variables)
    {
        $this->_variables = array_merge($this->_variables, $variables);
    }

    /**
     * return template's content
     * @return string
     */
    public function render()
    {
        return $this->_template->render($this->_variables);
    }

    /**
     * include required paths into loader
     * @param array $paths
     * @return mixed
     */
    public function loadDependencies(array $paths)
    {
        if (isset($this->_engine)) {
            foreach ($paths as $path) {
                $this->_engine->getLoader()->addPath($path);
            }
        }
    }
}
