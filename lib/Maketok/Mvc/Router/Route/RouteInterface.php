<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Mvc\Router\Route;

use Maketok\Util\RequestInterface;

interface RouteInterface
{
    /**
     * @param  RequestInterface $request
     * @return Success
     */
    public function match(RequestInterface $request);

    /**
     * @param  array  $params
     * @return string
     */
    public function assemble(array $params = array());

    /**
     * @return RequestInterface
     */
    public function getRequest();

    /**
     * @return callable
     */
    public function getResolver();
}
