<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer\Ddl\Test;

use Maketok\App\Site;

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
        $client = $this->getMock('Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())->method('getDdlVersion')->will($this->returnValue('0.1.0'));
        $client->expects($this->any())->method('getDdlConfig')->will($this->returnValue([]));
        $client->expects($this->any())->method('getDdlCode')->will($this->returnValue('t1'));

        self::$_manager->addClient($client);
        $this->assertTrue(self::$_manager->hasClients());
        $this->assertCount(1, self::$_manager->getClients());
        $actual = current(self::$_manager->getClients());
        $this->assertEquals('0.1.0', $actual->getDdlVersion());
        $this->assertEquals([], $actual->getDdlConfig());
        $this->assertEquals('t1', $actual->getDdlCode());

        $client = $this->getMock('Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())->method('getDdlVersion')->will($this->returnValue('0.1.0'));
        $client->expects($this->any())->method('getDdlConfig')->will($this->returnValue([]));
        $client->expects($this->any())->method('getDdlCode')->will($this->returnValue('t2'));

        self::$_manager->addClient($client);
        $this->assertTrue(self::$_manager->hasClients());
        $this->assertCount(2, self::$_manager->getClients());
        $clients = self::$_manager->getClients();
        $actual = $clients['t2'];
        $this->assertEquals('0.1.0', $actual->getDdlVersion());
        $this->assertEquals([], $actual->getDdlConfig());
        $this->assertEquals('t2', $actual->getDdlCode());

        $client = $this->getMock('Maketok\Installer\Ddl\ClientInterface');
        $client->expects($this->any())->method('getDdlVersion')->will($this->returnValue('0.2.0'));
        $client->expects($this->any())->method('getDdlConfig')->will($this->returnValue(['bla']));
        $client->expects($this->any())->method('getDdlCode')->will($this->returnValue('t2'));

        self::$_manager->addClient($client);
        $this->assertTrue(self::$_manager->hasClients());
        $this->assertCount(2, self::$_manager->getClients());
        $clients = self::$_manager->getClients();
        $actual = $clients['t2'];
        $this->assertEquals('0.2.0', $actual->getDdlVersion());
        $this->assertEquals(['bla'], $actual->getDdlConfig());
        $this->assertEquals('t2', $actual->getDdlCode());
    }

    /**
     * @test
     * @covers createDirectives
     */
    public function testCreateDirectives()
    {
        // @TODO add logic
    }
}
