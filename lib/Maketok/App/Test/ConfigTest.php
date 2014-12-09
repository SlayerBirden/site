<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App\Test;

use Maketok\App\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        // funny activity: reset static property of Config for every test :)
        // oh those statics
        $refProp = new \ReflectionProperty('Maketok\App\Config', '_config');
        $refProp->setAccessible(true);
        $refProp->setValue([]);
    }

    /**
     * @test
     * @covers Maketok\App\Config::merge
     */
    public function testMerge()
    {
        $conf1 = [
            'articles' => [
                'article' => 1,
                'title' => 'F',
            ],
            'movies' => [
                'actor' => 'JC',
                'title' => 'F',
            ],
        ];
        $conf2 = [
            'articles' => [
                'title' => 'The primus',
            ],
            'movies' => [
                'title' => 'Batman & Robin',
            ],
        ];
        $expected = [
            'articles' => [
                'article' => 1,
                'title' => 'The primus',
            ],
            'movies' => [
                'actor' => 'JC',
                'title' => 'Batman & Robin',
            ],
        ];
        $this->assertEquals($expected, Config::merge($conf1, $conf2));
    }

    /**
     * @test
     * @covers Maketok\App\Config::add
     */
    public function testAdd()
    {
        $cnf = [
            'articles' => [
                'article' => 1,
                'title' => 'F',
            ],
            'movies' => [
                'actor' => 'JC',
                'title' => 'F',
            ],
        ];
        Config::add($cnf);
        $this->assertEquals($cnf, Config::getConfig());
        Config::add(['blue' => 1]);
        $this->assertEquals([
            'articles' => [
                'article' => 1,
                'title' => 'F',
            ],
            'movies' => [
                'actor' => 'JC',
                'title' => 'F',
            ],
            'blue' => 1,
        ], Config::getConfig());
    }

    /**
     * @test
     * @depends testAdd
     * @covers Maketok\App\Config::getConfig
     */
    public function testGetConfig()
    {
        // empty array when wrong param set
        $this->assertSame([], Config::getConfig('bla'));
        // array as first state of config
        $this->assertSame([], Config::getConfig());
        // now set something
        $cnf = [
            'articles' => [
                'article' => 1,
                'title' => 'F',
            ],
            'movies' => [
                'actor' => 'JC',
                'title' => 'F',
            ],
        ];
        Config::add($cnf);
        $this->assertEquals([
            'article' => 1,
            'title' => 'F',
        ], Config::getConfig('articles'));
        $this->assertEquals('F', Config::getConfig('articles/title'));
    }
}
