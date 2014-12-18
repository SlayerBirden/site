<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
