<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Http;


use Maketok\Util\Message;

abstract class AbstractMessage extends Message
{


    /** @var  array */
    protected $_headers;


    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    public function setHeaders(array $headers)
    {
        $this->_headers = $headers;
        return $this;
    }
}