<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Module;


interface ConfigInterface
{

    public function initRoutes();

    public function initListeners();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return string
     */
    public function getCode();
} 