<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Loader;

/**
 * @codeCoverageIgnore
 * @deprecated due to usage of composer autoload
 */
class Autoload
{
    const NS_SEPARATOR = '\\';

    /**
     * register autoloader
     */
    public function register()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * @param  string $class
     * @return mixed|string
     */
    public function autoload($class)
    {
        // get base dir
        $dir = dirname(dirname(dirname(__DIR__)));
        $paths = explode(PATH_SEPARATOR, get_include_path());
        array_push($paths, $dir);
        set_include_path(implode(PATH_SEPARATOR, $paths));

        $filename = $this->getRealClassName($class);
        $resolvedName = stream_resolve_include_path($filename);
        if (false !== $resolvedName) {
            require $resolvedName;
        }

        return $resolvedName;
    }

    /**
     * @param string $class
     * @return string
     */
    public function getRealClassName($class)
    {
        return str_replace(self::NS_SEPARATOR, '/', $class) . '.php';
    }
}
