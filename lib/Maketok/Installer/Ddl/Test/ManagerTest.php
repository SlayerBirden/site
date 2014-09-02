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
        $client = $this->getMock('\Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(ClientInterface::TYPE_INSTALL));
        $client->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('0.2.2'));
        $client->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue([22]));

        $this->addMockMapper();

        $chain = [
            [20],
            [22],
        ];
        $this->assertEquals($chain, self::$_manager->getConfigChain($client));

        $client = $this->getMock('\Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(ClientInterface::TYPE_UPDATE));
        $client->expects($this->any())
            ->method('getNextVersion')
            ->will($this->returnValue('0.2.0'));
        $chain = [
            [20],
        ];
        $this->assertEquals($chain, self::$_manager->getConfigChain($client));

        $client = $this->getMock('\Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(ClientInterface::TYPE_UPDATE));
        $client->expects($this->any())
            ->method('getNextVersion')
            ->will($this->returnValue('0.1.1'));
        $chain = [
            [12],
        ];
        $this->assertEquals($chain, self::$_manager->getConfigChain($client));
    }

    /**
     * helper method
     */
    public function addMockMapper()
    {
        $refProp = new \ReflectionProperty(get_class(self::$_manager), '_tableMapper');
        $refProp->setAccessible(true);
        $mockMapper = $this->getMock('Maketok\Installer\Resource\Model\DdlClientConfigType');
        $configItem1 = new DdlClientConfig();
        $configItem1->version = '0.2.0';
        $configItem1->config = [20];
        $configItem1->id = '1';
        $configItem2 = clone $configItem1;
        $configItem2->version = '0.1.1';
        $configItem2->config = [11];
        $configItem2->id = '2';
        $configItem3 = clone $configItem1;
        $configItem3->version = '0.1.2';
        $configItem3->config = [12];
        $configItem3->id = '3';
        $mockMapper->expects($this->any())
            ->method('getAllConfigs')
            ->will($this->returnValue(array($configItem1, $configItem2, $configItem3)));
        $mockMapper->expects($this->any())
            ->method('getCurrentConfig')
            ->will($this->returnValue($configItem3));
        $refProp->setValue(self::$_manager, $mockMapper);
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
        // set up mapper
        $this->addMockMapper();
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
        // check
        $this->assertTrue(self::$_manager->validateClient($client));

        // false tests
        $client = $this->getMock('\Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(ClientInterface::TYPE_INSTALL));
        $client->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('0.2.0'));
        $this->assertFalse(self::$_manager->validateClient($client));

        $client = $this->getMock('\Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(ClientInterface::TYPE_UPDATE));
        $client->expects($this->any())
            ->method('getNextVersion')
            ->will($this->returnValue('0.1.3'));
        $this->assertFalse(self::$_manager->validateClient($client));
    }
}
