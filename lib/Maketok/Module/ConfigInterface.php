<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Module;


use Maketok\App\Ddl\InstallerApplicableInterface;

interface ConfigInterface extends InstallerApplicableInterface
{

    /**
     * @return void
     */
    public function initRoutes();

    /**
     * @return void
     */
    public function initListeners();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return string
     */
    public function getCode();

    /**
     * magic method for returning string representation of the the config class
     * @return string
     */
    public function __toString();
} 