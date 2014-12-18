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


use Maketok\Util\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class Response extends HttpResponse implements ResponseInterface
{

}
