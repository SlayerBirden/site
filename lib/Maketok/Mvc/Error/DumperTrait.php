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
     * @param string $message
     * @return Response
     */
    public function dump(Request $request, $httpCode, $message = null)
    {
        switch ($httpCode) {
            case 404:
                return $this->norouteAction($request, $httpCode, $message);
            default:
                return $this->errorAction($request, $httpCode, $message);
        }
    }

    /**
     * @param Request $request
     * @param int $code
     * @param string $message
     * @return Response
     */
    public function errorAction($request, $code, $message = null)
    {
        if (is_null($message)) {
            $message = 'Oops! We are really sorry, but there was an error!';
        }
        return new Response("<h1>$message</h1>", $code);
    }

    /**
     * @param Request $request
     * @param int $code
     * @param string $message
     * @return Response
     */
    public function norouteAction($request, $code, $message = null)
    {
        if (is_null($message)) {
            $message = "Oops! We couldn't find the page you searched for. Looks like it doesn't exist anymore.";
        }
        return new Response("<h1>$message</h1>", $code);
    }
}
