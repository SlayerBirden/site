<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Http;



use Maketok\Util\RequestInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class Request extends HttpRequest implements RequestInterface
{

    /**
     * currently not in use
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        return $this;
    }
}