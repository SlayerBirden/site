<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer;


interface DirectivesInterface extends \IteratorAggregate, \Countable
{

    /**
     * @return mixed
     */
    public function unique();
}
