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

use Maketok\Firewall\Rule\IpRule;
use Maketok\Http\Request;

class IpRuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function addBlacklist()
    {
        $rule = new IpRule();
        $rule->addBlacklist(1, '1.1.1.1');
        $rule->addBlacklist(2, '2.2.2.2');

        $this->assertEquals([
            1 => ['1.1.1.1'],
            2 => ['2.2.2.2']
        ], $rule->getBlacklist());
    }

    /**
     * @test
     */
    public function addWhitelist()
    {
        $rule = new IpRule();
        $rule->addWhitelist(1, '1.1.1.1');
        $rule->addWhitelist(2, '2.2.2.2');

        $this->assertEquals([
            1 => ['1.1.1.1'],
            2 => ['2.2.2.2']
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
        $rule = new IpRule();
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
        $request = Request::create('/admin', 'GET', [], [], [], ['REMOTE_ADDR' => '2.2.2.2']);
        return [
            [
                [0, ['2.2.2.2']], // blacklist for role 0 -> restrict admin
                null,
                0,
                $request,
                false
            ],
            [
                [0, ['1.1.1.1']],
                null,
                0,
                $request,
                true
            ],
            [
                [0, ['2.2.2.2']],
                null,
                1,
                $request,
                true
            ],
            // whitelist check precedes blacklist
            // but if both black denies white, the check is not passed
            [
                [0, ['2.2.2.2']],
                [0, ['2.2.2.2']],
                0,
                $request,
                false
            ],
            [
                null,
                [0, ['2.2.2.2']],
                0,
                $request,
                true
            ],
            [
                null,
                [0, ['2.2.2.2']],
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
        $rule = new IpRule();
        $rule->isGranted(0, new Request());
    }
}
