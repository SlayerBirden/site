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

interface EngineInterface
{
    /**
     * optional method for configuring different engines
     * @param  mixed $options
     * @return mixed
     */
    public function configure($options);

    /**
     * load the template into engine by path
     * @param  string $path
     * @return mixed
     */
    public function loadTemplate($path);

    /**
     * set the array of variables to use
     * @param  array $variables
     * @return mixed
     */
    public function setVariables(array $variables);

    /**
     * include required paths into loader
     * @param  string[] $paths
     * @return mixed
     */
    public function loadDependencies(array $paths);

    /**
     * return template's content
     * @return string
     */
    public function render();
}
