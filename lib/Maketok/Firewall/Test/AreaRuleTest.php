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
     * @dataProvider provider
     * @param string[] $blacklist
     * @param int $role
     * @param Request $request
     * @param boolean $expected
     */
    public function isGranted($blacklist, $role, Request $request, $expected)
    {
        $rule = new AreaRule();
        if ($blacklist) {
            $rule->addBlacklist($blacklist[0], $blacklist[1]);
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
                0,
                $request,
                false
            ],
            [
                [0, ['base, api']],
                0,
                $request,
                true
            ],
            [
                [0, ['admin']],
                1,
                $request,
                true
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
