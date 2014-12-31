<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\Util;

class ConfigGetterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getConfig()
    {
        $configGetter = $this->getMock('Maketok\Util\ConfigGetter', ['getLoader']);
        $loaderMock = $this->getMock('Symfony\Component\Config\Loader\LoaderInterface');
        $map = [
            ['config.yml', null, null],
            ['admin.config.yml', null, ['value' => [1]]],
            ['config.php', null, ['value' => [2]]],
            ['admin.config.php', null, ['value' => [3]]],
        ];
        $loaderMock->expects($this->any())->method('load')->willReturnMap($map);
        $this->assertEquals(['value' => [1]], $loaderMock->load('admin.config.yml', null));
        $configGetter->expects($this->any())->method('getLoader')->will($this->returnValue($loaderMock));
        $this->assertEquals([
            ['value' => [1]],
            ['value' => [2]],
            ['value' => [3]],
        ], $configGetter->getConfig('testpath', 'config', 'admin'));
    }
}
