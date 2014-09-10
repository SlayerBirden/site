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
     * @covers createDirectives
     */
    public function testCreateDirectives()
    {
        // @TODO add logic
    }
}
