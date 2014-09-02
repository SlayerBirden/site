<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Test;

use Maketok\App\Site;
use Maketok\Installer\Ddl\ClientInterface;
use Maketok\Installer\Resource\Model\DdlClientConfig;

class ManagerTest extends \PHPUnit_Framework_TestCase
{

    /** @var \Maketok\Installer\Ddl\Manager */
    protected static $_manager;

    public function setUp()
    {
        self::$_manager = Site::getServiceContainer()->get('installer_ddl_manager');
    }

    /**
     * @test
     * @covers process
     */
    public function testProcess()
    {
        // @TODO add logic
    }

    /**
     * @test
     * @covers addClient
     */
    public function testAddClient()
    {
        // @TODO add logic
    }

    /**
     * @test
     * @covers mergeDirectives
     */
    public function testMergeDirectives()
    {
        // @TODO add logic
    }

    /**
     * @test
     * @covers applyDirectives
     */
    public function testApplyDirectives()
    {
        // @TODO add logic
    }

    /**
     * @test
     * @covers getConfigChain
     */
    public function testGetConfigChain()
    {

    }

    /**
     * @test
     * @covers validateClient
     */
    public function testValidateClient()
    {
        $client = $this->getMock('\Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(ClientInterface::TYPE_INSTALL));
        $client->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('0.2.2'));

        $refProp = new \ReflectionProperty(get_class(self::$_manager), '_tableMapper');
        $refProp->setAccessible(true);
        $mockMapper = $this->getMock('Maketok\Installer\Resource\Model\DdlClientConfigType');
        $configItem1 = new DdlClientConfig();
        $configItem1->version = '0.2.0';
        $configItem1->id = '1';
        $configItem2 = clone $configItem1;
        $configItem2->version = '0.1.1';
        $configItem2->id = '2';
        $mockMapper->expects($this->any())
            ->method('getAllConfigs')
            ->will($this->returnValue(array($configItem1, $configItem2)));
        $refProp->setValue(self::$_manager, $mockMapper);

        // now when all fixture work is done, let's test it works
        $this->assertTrue(self::$_manager->validateClient($client));

        // let's set up one more for update
        $client = $this->getMock('\Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(ClientInterface::TYPE_UPDATE));
        $client->expects($this->any())
            ->method('getNextVersion')
            ->will($this->returnValue('0.2.0'));
        $this->assertTrue(self::$_manager->validateClient($client));
    }
}
