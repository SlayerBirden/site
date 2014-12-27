<?php
/**
 * Created by PhpStorm.
 * User: okulik
 * Date: 22.12.14
 * Time: 19:40
 */

namespace Maketok\Util;

use Symfony\Component\Config\Loader\FileLoader;

abstract class AbstractFileLoader extends FileLoader
{
    /**
     * @return string
     */
    abstract public function getExtension();
}
