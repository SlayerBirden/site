<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\error\controller;

use Maketok\Http\Request;
use Maketok\Http\Response;
use Maketok\Mvc\Controller\AbstractBaseController;

class Index extends AbstractBaseController
{
    /**
     * @var int[]
     */
    protected $availableCodes = [
        403,
        404,
        500,
    ];

    /**
     * @param Request $request
     * @param int $httpCode
     * @param string $message
     * @return Response
     */
    public function dump(Request $request, $httpCode, $message = null)
    {
        $title = Response::$statusTexts[$httpCode];
        if (in_array($httpCode, $this->availableCodes)) {
            $this->setTemplate("$httpCode.html.twig");
        } else {
            $this->setTemplate("500.html.twig");
        }
        return $this->prepareResponse($request, [
            'title' => $title,
            'message' => $message,
        ], null, $httpCode);
    }
}
