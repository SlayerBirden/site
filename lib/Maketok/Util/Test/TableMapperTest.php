<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Test;

use Maketok\Util\TableMapper;
use Zend\Db\ResultSet\HydratingResultSet;

class TableMapperTest extends \PHPUnit_Framework_TestCase
{

    /** @var TableMapper */
    protected $table;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getTableGatewayMock()
    {
        $tg = $this->getMock('\Zend\Db\TableGateway\AbstractTableGateway');
        return $tg;
    }

    /**
     * @test
     * @dataProvider idFieldProvider
     * @covers       Maketok\Util\TableMapper::getIdFilter
     * @param        int|string|string[] $id
     * @param        int|string|string[] $data
     * @param        string[] $expected
     * @throws       \Maketok\Util\Exception\ModelException
     */
    public function getIdFilter($id, $data, $expected)
    {
        // we need to init TableMapper each time in order for DP to work
        $this->table = new TableMapper($this->getTableGatewayMock(), $id);
        $method = new \ReflectionMethod('\Maketok\Util\TableMapper', 'getIdFilter');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invokeArgs($this->table, array($data)));
    }

    /**
     * @test
     * @covers                   Maketok\Util\TableMapper::getIdFilter
     * @expectedException        \LogicException
     * @ecpectedExceptionMessage Not enough data to get Filter.
     */
    public function getIdFilterLowData()
    {
        $this->table = new TableMapper($this->getTableGatewayMock(), array('code', 'version'));
        $method = new \ReflectionMethod('\Maketok\Util\TableMapper', 'getIdFilter');
        $method->setAccessible(true);
        $this->table->getIdField($method->invokeArgs($this->table, array('code1')));
    }

    /**
     * @test
     * @covers                   Maketok\Util\TableMapper::getIdFilter
     * @expectedException        \LogicException
     * @ecpectedExceptionMessage Missing data for id field
     */
    public function getIdFilterWrongData()
    {
        $this->table = new TableMapper($this->getTableGatewayMock(), 'code');
        $method = new \ReflectionMethod('\Maketok\Util\TableMapper', 'getIdFilter');
        $method->setAccessible(true);
        $this->table->getIdField($method->invokeArgs($this->table, array(['id' => 3])));
    }

    /**
     * provider for getIdFilter
     * @returns array
     */
    public function idFieldProvider()
    {
        return [
            ['id', 3, ['id' => 3]],
            ['code', ['code' => 'code1', 'id' => 3], ['code' => 'code1']],
            [['code', 'version'], ['code' => 'code1', 'version' => '3', 'id' => 3], ['code' => 'code1', 'version' => '3']],
        ];
    }

    /**
     * @test
     * @covers Maketok\Util\TableMapper::save
     */
    public function saveNewNoAI()
    {
        $tg = $this->getTableGatewayMock();
        $tg->expects($this->once())->method('update')->will($this->returnValue(0));
        $tg->expects($this->once())->method('getTable')->will($this->returnValue(null));
        $tg->expects($this->once())->method('insertWith')->will($this->returnValue(1));
        $tg->expects($this->once())->method('getLastInsertValue')->will($this->returnValue(null));
        $tg->expects($this->never())->method('insert');
        $tg->expects($this->once())->method('getResultSetPrototype')->will($this->returnValue(new HydratingResultSet()));
        $data = ['id' => 3, 'code' => 'code1'];
        $object = new \ArrayObject($data);

        $this->table = new TableMapper($tg, 'id');
        $this->table->save($object);
    }

    /**
     * @test
     * @covers Maketok\Util\TableMapper::save
     */
    public function saveNewAI()
    {
        $tg = $this->getTableGatewayMock();
        $tg->expects($this->once())->method('update')->will($this->returnValue(0));
        $tg->expects($this->once())->method('getTable')->will($this->returnValue(null));
        $tg->expects($this->once())->method('insertWith')->will($this->returnValue(1));
        $tg->expects($this->once())->method('getLastInsertValue')->will($this->returnValue(null));
        $tg->expects($this->never())->method('insert');
        $tg->expects($this->once())->method('getResultSetPrototype')->will($this->returnValue(new HydratingResultSet()));
        $data = ['id' => 3, 'code' => 'code1'];
        $object = new \ArrayObject($data);

        $this->table = new TableMapper($tg, 'id', 'id');
        $this->table->save($object);
    }

    /**
     * @test
     * @covers Maketok\Util\TableMapper::save
     */
    public function saveExisting()
    {
        $tg = $this->getTableGatewayMock();
        $tg->expects($this->once())->method('update')->will($this->returnValue(1));
        $tg->expects($this->never())->method('getTable');
        $tg->expects($this->never())->method('insertWith');
        $tg->expects($this->never())->method('getLastInsertValue');
        $tg->expects($this->never())->method('insert');
        $tg->expects($this->once())->method('getResultSetPrototype')->will($this->returnValue(new HydratingResultSet()));
        $data = ['code' => 'code1'];
        $object = new \ArrayObject($data);

        $this->table = new TableMapper($tg, 'code');
        $this->table->save($object);
    }

    /**
     * @test
     * @covers Maketok\Util\TableMapper::save
     */
    public function saveNewAIMissing()
    {
        $tg = $this->getTableGatewayMock();
        $tg->expects($this->once())->method('insert')->will($this->returnValue(1));
        $tg->expects($this->once())->method('getLastInsertValue')->will($this->returnValue(4));
        $tg->expects($this->never())->method('getTable');
        $tg->expects($this->never())->method('insertWith');
        $tg->expects($this->never())->method('update');
        $tg->expects($this->once())->method('getResultSetPrototype')->will($this->returnValue(new HydratingResultSet()));
        $data = ['code' => 'code1'];
        $object = new \ArrayObject($data);

        $this->table = new TableMapper($tg, 'id', 'id');
        $this->table->save($object);
    }
}
