<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\Module;

use Maketok\Module\ModuleManager;
use Maketok\Module\Resource\Model\Module;
use Maketok\Util\Test\MuteStub;

class ModuleManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModuleManager
     */
    protected $manager;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tmStub;

    public function setUp()
    {
        $this->tmStub = $this->getMock('Maketok\Model\TableMapper', [], [], '', false);
        $this->manager = new ModuleManager($this->tmStub, 'test', 'test');
    }
    /**
     * @test
     */
    public function updateToVersion()
    {
        $module = new Module();
        $module->module_code = 1;
        $module->version = 1;
        $this->tmStub->expects($this->once())->method('find')->with($this->equalTo('1'))->will($this->returnValue($module));
        $this->tmStub->expects($this->once())->method('save')->with($this->equalTo($module));

        $this->manager->updateToVersion('1', '2');
        $this->assertEquals('2', $module->version);
    }

    /**
     * @test
     */
    public function getActiveModules()
    {
        $st1 = $this->getMock('Maketok\Module\ConfigInterface');
        $st1->expects($this->once())->method('__toString')->will($this->returnValue('m1'));
        $st2 = $this->getMock('Maketok\Module\ConfigInterface');
        $st2->expects($this->once())->method('__toString')->will($this->returnValue('m2'));

        $prop = new \ReflectionProperty(get_class($this->manager), 'modules');
        $prop->setAccessible(true);
        $prop->setValue($this->manager, [$st1, $st2]);
        $module = new Module();
        $module->module_code = 'm1';
        $this->tmStub->expects($this->once())->method('fetchFilter')->will($this->returnValue([$module]));

        $this->assertEquals([$st1], $this->manager->getActiveModules());
    }

    /**
     * @test
     */
    public function getModuleExistsInDb()
    {
        $st1 = $this->getMock('Maketok\Module\ConfigInterface');
        $st1->expects($this->once())->method('__toString')->will($this->returnValue('m1'));

        $module = new Module();
        $module->module_code = 'm1';
        $this->tmStub->expects($this->once())->method('fetchFilter')->will($this->returnValue([$module]));

        $this->assertTrue($this->manager->getModuleExistsInDb($st1));
    }

    /**
     * @test that modules are pulled from the directory
     */
    public function processModuleConfig()
    {
        $manager = $this->getMock('Maketok\Module\ModuleManager', ['getConfigClassName'], [], '', false);
        $manager->expects($this->any())->method('getConfigClassName')->willReturnMap([
            ['bar', 'Maketok\Util\Test\MuteStub'],
            ['baz', 'Maketok\Util\Test\MuteStub'],
        ]);

        $directoryHanderStub = $this->getMock('Maketok\Util\DirectoryHandler');
        $directoryHanderStub->expects($this->once())->method('ls')->willReturn(['bar', 'baz']);

        /** @var ModuleManager $manager */
        $manager->setDirectoryHandler($directoryHanderStub);
        $manager->processModuleConfig();

        $prop = new \ReflectionProperty('Maketok\Module\ModuleManager', 'modules');
        $prop->setAccessible(true);
        $this->assertEquals([
            'bar' => new MuteStub(['code' => 'bar']),
            'baz' => new MuteStub(['code' => 'baz']),
        ], $prop->getValue($manager));
    }

    /**
     * @test
     */
    public function getConfigClassName()
    {
        $manager = $this->getMock('Maketok\Module\ModuleManager', ['getDir'], [$this->tmStub, 'test', 'test']);
        $manager->expects($this->any())->method('getDir')->willReturn('/some/path/modules');
        $this->assertEquals('\modules\bar\test', $manager->getConfigClassName('bar'));
    }

    /**
     * @test that modules are added to db
     */
    public function addDbModule()
    {
        $resultSetProtoStub = $this->getMock('ResultSet', ['getObjectPrototype']);
        $resultSetProtoStub->expects($this->any())
            ->method('getObjectPrototype')
            ->willReturn(new Module());
        $gateWayStub = $this->getMock('GateWay', ['getResultSetPrototype']);
        $gateWayStub->expects($this->any())
            ->method('getResultSetPrototype')
            ->willReturn($resultSetProtoStub);
        $this->tmStub->expects($this->any())->method('getGateway')->willReturn($gateWayStub);

        $config = $this->getMock('Maketok\Module\ConfigInterface');
        $config->expects($this->any())->method('getCode')->willReturn('m1');
        $config->expects($this->any())->method('getVersion')->willReturn('1');
        $config->expects($this->any())->method('isActive')->willReturn(true);

        $module = new Module();
        $module->module_code = 'm1';
        $module->version = '1';
        $module->active = true;
        $module->area = 'test';
        $this->tmStub->expects($this->once())->method('save')->with($this->equalTo($module));

        $this->manager->addDbModule($config);
    }

    /**
     * @test
     */
    public function updateDbModule()
    {
        $config = $this->getMock('Maketok\Module\ConfigInterface');
        $config->expects($this->any())->method('getVersion')->willReturn('1');

        $module = new Module();
        $module->module_code = 'm1';
        $module->version = '1';
        $module->active = true;
        $module->area = 'test';
        $this->tmStub->expects($this->once())->method('save')->with($this->equalTo($module));

        $given = new Module();
        $given->module_code = 'm1';
        $given->version = '0.9';
        $given->active = true;
        $given->area = 'test';

        $this->manager->updateDbModule($given, $config);
    }

    /**
     * @test
     */
    public function removeDbModule()
    {
        $module = new Module();
        $module->module_code = 'm1';
        $this->tmStub->expects($this->once())->method('delete')->with($this->equalTo('m1'));
        $this->manager->removeDbModule($module);
    }

    /**
     * @test that modules are updated in the db
     */
    public function updateModules()
    {
        $manager = $this->getMock('Maketok\Module\ModuleManager', [
            'getModuleExistsInDb',
            'addDbModule',
            'removeDbModule',
            'updateDbModule',
            'getDbModules',
            'getActiveModules',
        ], [], '', false);

        $st1 = $this->getMock('Maketok\Module\ConfigInterface');
        $st2 = $this->getMock('Maketok\Module\ConfigInterface');

        $prop = new \ReflectionProperty(get_class($this->manager), 'modules');
        $prop->setAccessible(true);
        $prop->setValue($manager, [
            'm1' => $st1,
            'm2' => $st2,
        ]);

        $manager->expects($this->atLeastOnce())->method('getModuleExistsInDb')->willReturnMap([
            [$st1, false],
            [$st2, true],
        ]);

        $module = new Module();
        $module->module_code = 'm1';
        $module2 = new Module();
        $module2->module_code = 'm2';
        $module3 = new Module();
        $module3->module_code = 'm3';
        $manager->expects($this->once())->method('getDbModules')->willReturn([$module, $module2, $module3]);

        $manager->expects($this->once())->method('addDbModule')->with($st1);
        $manager->expects($this->once())->method('removeDbModule')->with($module3);
        $manager->expects($this->atLeastOnce())->method('updateDbModule');
        /** @var ModuleManager $manager */
        $manager->updateModules();
    }

    /**
     * @test that modules' routes and observers definitions are triggered
     */
    public function processModules()
    {
        $manager = $this->getMock('Maketok\Module\ModuleManager', [
            'getActiveModules',
        ], [], '', false);

        $st1 = $this->getMock('Maketok\Module\ConfigInterface');
        $st1->expects($this->once())->method('initListeners');
        $st1->expects($this->once())->method('initRoutes');

        $prop = new \ReflectionProperty(get_class($this->manager), 'modules');
        $prop->setAccessible(true);
        $prop->setValue($manager, [
            'm1' => $st1,
        ]);

        $manager->expects($this->atLeastOnce())->method('getActiveModules')->willReturn([$st1]);
        /** @var ModuleManager $manager */
        $manager->processModules();
    }
}
