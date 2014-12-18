<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Mvc\Router\Route\Http;

use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Mvc\Router\Route\Success;
use Maketok\Util\RequestInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class Error extends AbstractRoute implements RouteInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param callable $resolver
     * @param array    $options
     */
    public function __construct($resolver, array $options = [])
    {
        $this->resolver = $resolver;
        $this->options = $options;
    }

    /**
     * @param  RequestInterface $request
     * @return Success
     */
    public function match(RequestInterface $request)
    {
        $this->request = $request;
        $this->options['_route'] = $this;
        $attributes = $request->getAttributes();
        if (is_object($attributes) && ($attributes instanceof ParameterBag)) {
            $attributes->add($this->options);
        }

        return new Success($this);
    }

    /**
     * @param  array  $params
     * @return string
     */
    public function assemble(array $params = array())
    {
        return '';
    }
}
