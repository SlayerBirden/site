<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace Maketok\App;

class Session
{
    public function start()
    {
        session_start();
    }

    public function destroy()
    {
        session_destroy();
    }

    public function regenerate()
    {
        session_regenerate_id();
    }

    public function registerSaveHandler(\SessionHandlerInterface $handler)
    {
        session_set_save_handler($handler, true);
    }

    public function init()
    {
        ini_set('session.save_handler', 'user');
        $this->registerSaveHandler(Site::registry()->session_save_handler);
        $this->start();
    }
}