<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Template;

interface EngineInterface
{

    /**
     * optional method for configuring different engines
     * @param mixed $options
     * @return mixed
     */
    public function configure($options);

    /**
     * load the template into engine by path
     * @param string $path
     * @return mixed
     */
    public function loadTemplate($path);

    /**
     * set the array of variables to use
     * @param array $variables
     * @return mixed
     */
    public function setVariables(array $variables);

    /**
     * include required paths into loader
     * @param array $paths
     * @return mixed
     */
    public function loadDependencies(array $paths);

    /**
     * return template's content
     * @return string
     */
    public function render();
}