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

use Maketok\Firewall\Rule\AreaRule;
use Maketok\Http\Request;

class AreaRuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function addBlacklist()
    {
        $rule = new AreaRule();
        $rule->addBlacklist(1, 'admin');
        $rule->addBlacklist(2, 'base');

        $this->assertEquals([
            1 => ['admin'],
            2 => ['base']
        ], $rule->getBlacklist());
    }

    /**
     * @test
     */
    public function addWhitelist()
    {
        $rule = new AreaRule();
        $rule->addWhitelist(1, 'admin');
        $rule->addWhitelist(2, 'base');

        $this->assertEquals([
            1 => ['admin'],
            2 => ['base']
        ], $rule->getWhitelist());
    }

    /**
     * @test
     * @dataProvider provider
     * @param string[] $blacklist
     * @param string[] $whitelist
     * @param int $role
     * @param Request $request
     * @param boolean $expected
     */
    public function isGranted($blacklist, $whitelist, $role, Request $request, $expected)
    {
        $rule = new AreaRule();
        if ($blacklist) {
            $rule->addBlacklist($blacklist[0], $blacklist[1]);
        }
        if ($whitelist) {
            $rule->addWhitelist($whitelist[0], $whitelist[1]);
        }

        $this->assertSame($expected, $rule->isGranted($role, $request));
    }

    /**
     * @return array
     */
    public function provider()
    {
        /** @var Request $request */
        $request = Request::create('/admin');
        $request->setArea('admin');
        return [
            [
                [0, ['admin']], // blacklist for role 0 -> restrict admin
                null,
                0,
                $request,
                false
            ],
            [
                [0, ['base', 'api']],
                [],
                0,
                $request,
                true
            ],
            [
                [0, ['admin']],
                null,
                1,
                $request,
                true
            ],
            // whitelist check precedes blacklist
            // but if both black denies white, the check is not passed
            [
                [0, ['admin']],
                [0, ['admin']],
                0,
                $request,
                false
            ],
            [
                null,
                [0, ['admin']],
                0,
                $request,
                true
            ],
            [
                null,
                [0, ['admin']],
                1,
                $request,
                false
            ]
        ];
    }

    /**
     * @test
     * @expectedException \Maketok\Firewall\FirewallException
     * @expectedExceptionMessage No lists found.
     */
    public function isGrantedNoLists()
    {
        $rule = new AreaRule();
        $rule->isGranted(0, new Request());
    }
}
