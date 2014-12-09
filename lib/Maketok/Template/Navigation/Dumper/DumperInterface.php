<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template\Navigation\Dumper;

interface DumperInterface
{

    /**
     * dump navigation to a file
     * @param string $path
     * @return mixed
     */
    public function write($path);

    /**
     * @return string
     */
    public function getFileExtension();
}
