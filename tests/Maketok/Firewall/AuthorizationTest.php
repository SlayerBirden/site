<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\Firewall;

use Maketok\Firewall\Authorization;
use Maketok\Firewall\Rule\AreaRule;
use Maketok\Firewall\Rule\IpRule;
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
        $rule = new AreaRule('blacklist', 0, ['admin']);
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
        $rule = new AreaRule('blacklist', 0, ['admin']);
        $auth->addRule($rule);
        $request = new Request();
        $request->setArea('admin');

        $auth->validate($request);
    }

    /**
     * @test
     */
    public function validateBlackNWhite()
    {
        $provider = $this->getMock('Maketok\Firewall\RoleProviderInterface');
        $provider->expects($this->once())->method('getCurrentRoles')->willReturn([0]);
        $auth = new Authorization($provider);
        $rule = new AreaRule('blacklist', 0, ['admin']);
        $auth->addRule($rule);
        $rule2 = new IpRule('whitelist', 0, ['127.0.0.1']);
        $auth->addRule($rule2);
        /** @var Request $request */
        $request = Request::create('/test');
        $request->setArea('admin');

        $auth->validate($request);
    }

    /**
     * @test
     */
    public function validateNoRules()
    {
        $provider = $this->getMock('Maketok\Firewall\RoleProviderInterface');
        $provider->expects($this->never())->method('getCurrentRoles');
        $auth = new Authorization($provider);
        /** @var Request $request */
        $request = Request::create('/test');
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
        $rule = new AreaRule('blacklist', 0, ['admin']);
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
                ],
                'whitelist' => [
                    'Maketok\Firewall\Rule\IpRule' => ['127.0.0.1'],
                ],
            ]
        ]);

        $this->assertEquals([
            new PathRule('blacklist', 0, ['^/my-admin']),
            new IpRule('whitelist', 0, ['127.0.0.1']),
        ], $auth->getRules());
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
    public function addWhitelist()
    {
        $auth = new Authorization();
        $auth->addWhitelist(0, 'test');
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
