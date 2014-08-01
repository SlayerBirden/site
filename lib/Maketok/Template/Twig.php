<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Template;

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
        $loader = new \Twig_Loader_Filesystem();
        $this->_engine = new \Twig_Environment($loader, array(
            'cache' => APPLICATION_ROOT . DIRECTORY_SEPARATOR . self::CACHE_FOLDER,
            'debug' => true,
        ));
        $this->_engine->addExtension(new \Twig_Extensions_Extension_I18n());
    }

    /**
     * load the template into engine by path
     * @param string $path
     * @return mixed
     */
    public function loadTemplate($path)
    {
        $this->_engine->getLoader()->addPath(dirname($path));
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
}