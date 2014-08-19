<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Template;

use Maketok\App\Site;

class Twig extends AbstractEngine
{

    const CACHE_FOLDER = 'var/cache';

    /** @var object loaded template */
    protected $_template;

    /**
     * @var array
     */
    protected $_variables = array();

    /**
     * set loader, env
     * @param bool $debug
     */
    public function __construct($debug = false)
    {
        $this->_engine = Site::getServiceContainer()->get('twig_env');
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
        $this->_variables = $variables;
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