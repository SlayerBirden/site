<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace Maketok\Loader;

class Autoload
{
    const NS_SEPARATOR = '\\';

    public function register()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    public function autoload($class)
    {
        $filename = $this->getRealClassName($class);
        $resolvedName = stream_resolve_include_path($filename);
        if (false !== $resolvedName) {
            return include $resolvedName;
        }
        return false;
    }

    public function getRealClassName($class)
    {
        return str_replace(self::NS_SEPARATOR, '/', $class) . '.php';
    }
}