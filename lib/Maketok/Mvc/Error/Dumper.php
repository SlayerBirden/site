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
use Symfony\Component\HttpFoundation\Response;

class Dumper implements DumperInterface
{

    /**
     * {@inheritdoc}
     */
    public function errorAction(Request $request)
    {
        return new Response('<h1>Oops! We are really sorry, but there was an error!</h1>', 500);
    }

    /**
     * {@inheritdoc}
     */
    public function norouteAction(Request $request)
    {
        return new Response("<h1>Oops! We couldn't find the page you searched for. Looks like it doesn't exist anymore.</h1>", 404);
    }
}
