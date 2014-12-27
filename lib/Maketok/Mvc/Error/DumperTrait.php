<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Mvc\Error;

use Maketok\Http\Request;
use Maketok\Http\Response;

trait DumperTrait
{
    /**
     * @param Request $request
     * @param int $httpCode
     * @return Response
     */
    public function dump(Request $request, $httpCode)
    {
        switch ($httpCode) {
            case 404:
                return $this->norouteAction($request);
            default:
                return $this->errorAction($request);
        }
    }

    /**
     * @return Response
     * @internal param Request $request
     */
    public function errorAction()
    {
        return new Response('<h1>Oops! We are really sorry, but there was an error!</h1>', 500);
    }

    /**
     * @return Response
     * @internal param Request $request
     */
    public function norouteAction()
    {
        return new Response("<h1>Oops! We couldn't find the page you searched for. Looks like it doesn't exist anymore.</h1>", 404);
    }
}
