<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Mvc\Controller;

use Maketok\Observer\StateInterface;

class Front
{
    public function dispatch(StateInterface $state)
    {
        var_dump($state); die;
    }
}