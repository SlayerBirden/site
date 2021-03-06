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

/**
 * @codeCoverageIgnore
 */
class Phptal extends AbstractEngine
{
    /**
     * load the template into engine by path
     * @param  string $path
     * @return mixed
     */
    public function loadTemplate($path)
    {
        $this->engine = new \PHPTAL($path);
    }

    /**
     * set the array of variables to use
     * @param  array $variables
     * @return mixed
     */
    public function setVariables(array $variables)
    {
        foreach ($variables as $key => $value) {
            $this->engine->$key = $value;
        }
    }

    /**
     * return template's content
     * @return string
     */
    public function render()
    {
        return $this->engine->execute();
    }

    /**
     * include required paths into loader
     * @param  array $paths
     * @return mixed
     */
    public function loadDependencies(array $paths)
    {
        return;
    }
}
