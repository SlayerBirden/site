<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Http;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface as BaseSessionInterface;

interface SessionInterface extends BaseSessionInterface
{
    /**
     * Gets the flashbag interface.
     *
     * @return FlashBagInterface
     */
    public function getFlashBag();
}
