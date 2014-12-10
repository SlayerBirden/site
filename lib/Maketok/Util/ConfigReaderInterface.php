<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;


interface ConfigReaderInterface
{

    /**
     * @param string $path config path
     * @return mixed|array
     */
    public function source($path);
}
