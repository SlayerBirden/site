<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace Maketok\Http;

/**
 * Class Session
 * @package Maketok\Http
 * @deprecated
 */
class Session
{
    /**
     * Session start
     */
    public function start()
    {
        session_start();
    }

    /**
     * Session destroy
     */
    public function destroy()
    {
        session_destroy();
    }

    /**
     * Session regenerate
     */
    public function regenerate()
    {
        session_regenerate_id();
    }

    /**
     * @param \SessionHandlerInterface $handler
     */
    public function registerSaveHandler(\SessionHandlerInterface $handler)
    {
        session_set_save_handler($handler, true);
    }

    /**
     * @param \SessionHandlerInterface $handler
     */
    public function init(\SessionHandlerInterface $handler)
    {
        ini_set('session.save_handler', 'user');
        $this->registerSaveHandler($handler);
        $this->start();
    }
}
