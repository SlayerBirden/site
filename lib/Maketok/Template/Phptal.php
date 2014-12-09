<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template;

class Phptal extends AbstractEngine
{

    /**
     * load the template into engine by path
     * @param string $path
     * @return mixed
     */
    public function loadTemplate($path)
    {
        $this->_engine = new \PHPTAL($path);
    }

    /**
     * set the array of variables to use
     * @param array $variables
     * @return mixed
     */
    public function setVariables(array $variables)
    {
        foreach ($variables as $key => $value) {
            $this->_engine->$key = $value;
        }
    }

    /**
     * return template's content
     * @return string
     */
    public function render()
    {
        return $this->_engine->execute();
    }

    /**
     * include required paths into loader
     * @param array $paths
     * @return mixed
     */
    public function loadDependencies(array $paths)
    {
        return;
    }
}
