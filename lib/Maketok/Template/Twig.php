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
use Maketok\App\Site;

/**
 * @codeCoverageIgnore
 */
class Twig extends AbstractEngine
{
    use ContainerTrait;

    const CACHE_FOLDER = 'var/cache';

    /** @var object loaded template */
    protected $template;

    /**
     * @var array
     */
    protected $variables = array();

    /**
     * set loader, env
     * @param object $engine
     */
    public function __construct($engine)
    {
        $this->engine = $engine;
        $this->loadDependencies(Site::getConfig('template_path'));
    }

    /**
     * @param \Twig_Extension $extension
     */
    public function addExtension(\Twig_Extension $extension)
    {
        $this->engine->addExtension($extension);
    }

    /**
     * @param string $path
     */
    public function addPath($path)
    {
        $this->engine->getLoader()->addPath($path);
    }

    /**
     * load the template into engine by path
     * @param  string $path
     * @return mixed
     */
    public function loadTemplate($path)
    {
        $this->addPath(dirname($path));
        $this->template = $this->engine->loadTemplate(basename($path));
    }

    /**
     * set the array of variables to use
     * @param  array $variables
     * @return mixed
     */
    public function setVariables(array $variables)
    {
        $this->variables = array_merge($this->variables, $variables);
    }

    /**
     * return template's content
     * @return string
     */
    public function render()
    {
        return $this->template->render($this->variables);
    }

    /**
     * include required paths into loader
     * @param  array $paths
     * @return mixed
     */
    public function loadDependencies(array $paths)
    {
        if (isset($this->engine)) {
            foreach ($paths as $path) {
                $this->engine->getLoader()->addPath($path);
            }
        }
    }
}
