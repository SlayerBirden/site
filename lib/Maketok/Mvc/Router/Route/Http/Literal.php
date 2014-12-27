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

class Literal extends AbstractRoute implements RouteInterface
{
    /** @var  array */
    protected $defaults;

    /**
     * @param string   $path
     * @param callable $resolver
     * @param array    $defaults
     */
    public function __construct($path, $resolver,  array $defaults = [])
    {
        $this->setPath($path);
        $this->resolver = $resolver;
        $this->defaults = $defaults;
    }

    /**
     * @param  RequestInterface $request
     * @return bool|Success
     */
    public function match(RequestInterface $request)
    {
        $this->request = $request;
        if ($this->stripTrailingSlash($request->getPathInfo()) === $this->stripTrailingSlash($this->matchPath)) {
            $attributes = $request->getAttributes();
            if (is_object($attributes) && ($attributes instanceof ParameterBag)) {
                $attributes->add(array(
                    '_route' => $this,
                ));
                if (!empty($this->_defaults)) {
                    $attributes->add($this->defaults);
                }
            }

            return new Success($this);
        }

        return false;
    }

    /**
     * @param  array  $params
     * @return string
     */
    public function assemble(array $params = array())
    {
        return $this->matchPath;
    }
}
