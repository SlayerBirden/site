<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Firewall\Test;

use Maketok\Firewall\Authorization;
use Maketok\Firewall\Rule\AreaRule;
use Maketok\Firewall\Rule\PathRule;
use Maketok\Http\Request;

class AuthorizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function addRule()
    {
        $auth = new Authorization();
        $rule = new AreaRule('black', 0, ['admin']);
        $auth->addRule($rule);

        $this->assertEquals([$rule], $auth->getRules());
    }

    /**
     * @test
     */
    public function validate()
    {
        $provider = $this->getMock('Maketok\Firewall\RoleProviderInterface');
        $provider->expects($this->once())->method('getCurrentRoles')->willReturn([1]);
        $auth = new Authorization($provider);
        $rule = new AreaRule('black', 0, ['admin']);
        $auth->addRule($rule);
        $request = new Request();
        $request->setArea('admin');

        $auth->validate($request);
    }

    /**
     * @test
     * @expectedException \Maketok\Firewall\AccessDeniedException
     * @expectedExceptionMessage Access denied for current entity.
     */
    public function validateRestrict()
    {
        $auth = new Authorization();
        $rule = new AreaRule('black', 0, ['admin']);
        $auth->addRule($rule);
        $request = new Request();
        $request->setArea('admin');

        $auth->validate($request);
    }

    /**
     * @test
     */
    public function parseConfig()
    {
        $auth = new Authorization();
        $auth->parseConfig([
            0 => [
                'blacklist' => [
                    'Maketok\Firewall\Rule\PathRule' => ['^/my-admin'],
                ]
            ]
        ]);

        $this->assertEquals([new PathRule('black', 0, ['^/my-admin'])], $auth->getRules());
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function addBlacklist()
    {
        $auth = new Authorization();
        $auth->addBlacklist(0, 'test');
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function isGranted()
    {
        $auth = new Authorization();
        $auth->isGranted(0, new Request());
    }
}
