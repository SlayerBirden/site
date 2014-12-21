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
use Maketok\Firewall\Rule\PathRule;
use Maketok\Firewall\FirewallException;
use Maketok\Http\Request;

/**
 * @coversDefaultClass \Maketok\Firewall\Rule\PathRule
 */
class PathRuleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers ::addBlacklist
     * @covers ::getBlacklist
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
     * @covers ::isGranted
     * @covers ::addBlacklist
     * @dataProvider provider
     * @param string[] $blacklist
     * @param int $role
     * @param Request $request
     * @param boolean $expected
     * @internal param string $pattern
     */
    public function isGranted($blacklist, $role, Request $request, $expected)
    {
        $rule = new PathRule();
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
        return [
            [
                [0, ['^/admin']], // blacklist for role 0 -> restrict admin
                0,
                Request::create('/admin'),
                false
            ],
            [
                [0, '^(?!/admin).*'], // restrict all but admin
                0,
                Request::create('/admin'),
                true
            ]
        ];
    }

    /**
     * @test
     * @covers ::isGranted
     * @expectedException \PHPUnit_Framework_Error
     */
    public function isGrantedWrongPattern()
    {
        $rule = new PathRule();

        $rule->addBlacklist(0, ['ab[\]c']);

        $rule->isGranted(0, new Request());
    }

    /**
     * @test
     * @covers ::isGranted
     * @expectedException \Maketok\Firewall\FirewallException
     * @expectedExceptionMessage No lists found for the role.
     */
    public function isGrantedNoLists()
    {
        $rule = new PathRule();

        $rule->isGranted(0, new Request());
    }
}
