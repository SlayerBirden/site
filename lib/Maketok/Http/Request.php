<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Http;

use Maketok\Util\RequestInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * @codeCoverageIgnore
 */
class Request extends HttpRequest implements RequestInterface
{

    /**
     * {@inheritdoc}
     */
    public $attributes;

    /**
     * currently not in use
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\HeaderBag
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
