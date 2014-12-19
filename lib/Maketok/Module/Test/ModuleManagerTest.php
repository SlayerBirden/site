<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Module\Test;
use Maketok\Module\ModuleManager;
use Maketok\Module\Resource\Model\Module;

/**
 * @coversDefaultClass \Maketok\Module\ModuleManager
 */
class ModuleManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers ::updateToVersion
     * @covers ::__construct
     * @covers ::initModule
     */
    public function updateToVersion()
    {
        $tmStub = $this->getMock('Maketok\Model\TableMapper', [], [], '', false);
        $manager = new ModuleManager($tmStub, 'test', 'test');
        $module = new Module();
        $module->module_code = 1;
        $module->version = 1;
        $tmStub->expects($this->once())->method('find')->with($this->equalTo('1'))->will($this->returnValue($module));
        $tmStub->expects($this->once())->method('save')->with($this->equalTo($module));

        $manager->updateToVersion('1', '2');
        $this->assertEquals('2', $module->version);
    }

    /**
     * @test
     * @covers ::getActiveModules
     */
    public function getActiveModules()
    {
        $st1 = $this->getMock('Maketok\Module\ConfigInterface');
        $st1->expects($this->once())->method('__toString')->will($this->returnValue('m1'));
        $st2 = $this->getMock('Maketok\Module\ConfigInterface');
        $st2->expects($this->once())->method('__toString')->will($this->returnValue('m2'));

        $tmStub = $this->getMock('Maketok\Model\TableMapper', [], [], '', false);
        $manager = new ModuleManager($tmStub, 'test', 'test');
        $prop = new \ReflectionProperty(get_class($manager), 'modules');
        $prop->setAccessible(true);
        $prop->setValue($manager, [$st1, $st2]);
        $module = new Module();
        $module->module_code = 'm1';
        $tmStub->expects($this->once())->method('fetchFilter')->will($this->returnValue([$module]));

        $this->assertEquals([$st1], $manager->getActiveModules());
    }

    /**
     * @test
     * @covers ::getDbModules
     * @covers ::getModuleExistsInDb
     */
    public function getModuleExistsInDb()
    {
        $st1 = $this->getMock('Maketok\Module\ConfigInterface');
        $st1->expects($this->once())->method('__toString')->will($this->returnValue('m1'));

        $tmStub = $this->getMock('Maketok\Model\TableMapper', [], [], '', false);
        $manager = new ModuleManager($tmStub, 'test', 'test');
        $module = new Module();
        $module->module_code = 'm1';
        $tmStub->expects($this->once())->method('fetchFilter')->will($this->returnValue([$module]));

        $this->assertTrue($manager->getModuleExistsInDb($st1));
    }

    public function processModuleConfig()
    {

    }

    public function addDbModule()
    {

    }

    public function removeDbModule()
    {

    }

    public function updateModules()
    {

    }

    public function processModules()
    {

    }
}
