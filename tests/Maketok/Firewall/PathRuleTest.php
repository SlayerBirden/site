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

use Maketok\Firewall\Rule\PathRule;
use Maketok\Http\Request;

class PathRuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function addBlacklist()
    {
        $rule = new PathRule();
        $rule->addBlacklist(0, ['test']);
        $rule->addBlacklist(1, 'test2');
        $rule->addBlacklist(0, 'test2');

        $this->assertEquals([
            0 => ['test', 'test2'],
            1 => ['test2']
        ], $rule->getBlacklist());
    }

    /**
     * @test
     */
    public function addWhitelist()
    {
        $rule = new PathRule();
        $rule->addWhitelist(0, ['test']);
        $rule->addWhitelist(1, 'test2');
        $rule->addWhitelist(0, 'test2');

        $this->assertEquals([
            0 => ['test', 'test2'],
            1 => ['test2']
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
        $rule = new PathRule();
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
        return [
            [
                [0, ['^/admin']], // blacklist for role 0 -> restrict admin
                null,
                0,
                Request::create('/admin'),
                false,
            ],
            [
                [0, '^(?!/admin).*'], // restrict all but admin
                null,
                0,
                Request::create('/admin'),
                true,
            ],
            [
                [0, ['^/admin']],
                null,
                1,
                Request::create('/admin'),
                true,
            ],
            // whitelist check precedes blacklist
            // but if both black denies white, the check is not passed
            [
                [0, ['^/admin']],
                [0, ['^/admin']],
                0,
                Request::create('/admin'),
                false,
            ],
            [
                null,
                [0, ['^/admin']],
                0,
                Request::create('/admin'),
                true,
            ],
            [
                null,
                [0, ['^/admin']],
                1,
                Request::create('/admin'),
                false,
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
        $rule = new PathRule();
        $rule->isGranted(0, new Request());
    }
}
