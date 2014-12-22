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

interface DumperInterface
{

    /**
     * @param  Request $request
     * @return Response
     */
    public function errorAction(Request $request);

    /**
     * @param  Request $request
     * @return Response
     */
    public function norouteAction(Request $request);
}
