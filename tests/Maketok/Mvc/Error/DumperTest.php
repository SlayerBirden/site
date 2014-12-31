<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\Mvc\Error;

use Maketok\Http\Request;
use Maketok\Http\Response;
use Maketok\Mvc\Error\Dumper;

class DumperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function dump()
    {
        $request = Request::create('/someroute');
        $dumper = new Dumper();
        $actualNoRoute = $dumper->dump($request, Response::HTTP_NOT_FOUND);
        $actualError = $dumper->dump($request, Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertContains("We couldn't find the page you searched for", $actualNoRoute->getContent());
        $this->assertContains("there was an error", $actualError->getContent());
    }
}
