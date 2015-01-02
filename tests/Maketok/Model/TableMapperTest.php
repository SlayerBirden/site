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

use Maketok\App\Helper\ContainerTrait;
use Maketok\Model\ArrayTableFactory;
use Maketok\Model\TableMapper;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;

class TableMapperTest extends \PHPUnit_Framework_TestCase
{
    use ContainerTrait;

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
     * @param  int|string|string[] $id
     * @param  int|string|string[] $data
     * @param  string[] $expected
     * @throws \Maketok\Util\Exception\ModelException
     */
    public function getIdFilter($id, $data, $expected)
    {
        // we need to init TableMapper each time in order for DP to work
        $this->table = new TableMapper($this->getTableGatewayMock(), $id);
        $method = new \ReflectionMethod('\Maketok\Model\TableMapper', 'getIdFilter');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invokeArgs($this->table, array($data)));
    }

    /**
     * @test
     * @expectedException \LogicException
     * @ecpectedExceptionMessage Not enough data to get Filter.
     */
    public function getIdFilterLowData()
    {
        $this->table = new TableMapper($this->getTableGatewayMock(), array('code', 'version'));
        $method = new \ReflectionMethod('\Maketok\Model\TableMapper', 'getIdFilter');
        $method->setAccessible(true);
        $this->table->getIdField($method->invokeArgs($this->table, array('code1')));
    }

    /**
     * @test
     * @expectedException \LogicException
     * @ecpectedExceptionMessage Missing data for id field
     */
    public function getIdFilterWrongData()
    {
        $this->table = new TableMapper($this->getTableGatewayMock(), 'code');
        $method = new \ReflectionMethod('\Maketok\Model\TableMapper', 'getIdFilter');
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
     * we insert model without autoincrement
     * @test
     */
    public function saveNewNoAI()
    {
        $tg = $this->getTableGatewayMock();
        // update retuns zero - no rows affected
        $tg->expects($this->once())->method('update')->will($this->returnValue(0));
        // insert with will return 1 - we inserted the row
        $tg->expects($this->once())->method('insertWith')->will($this->returnValue(1));
        // insert will never be called
        $tg->expects($this->never())->method('insert');
        $tg->expects($this->once())->method('getResultSetPrototype')->will($this->returnValue(new HydratingResultSet()));
        $data = ['id' => 3, 'code' => 'code1'];
        $object = new \ArrayObject($data);

        $this->table = new TableMapper($tg, 'id');
        $this->table->save($object);
    }

    /**
     * we insert model with autoincrement, which is set
     * since id is set in data we can't just insert the row,
     * it can be "update" case
     * @test
     */
    public function saveNewAI()
    {
        $tg = $this->getTableGatewayMock();
        // update retuns zero - no rows affected
        $tg->expects($this->once())->method('update')->will($this->returnValue(0));
        // insert with will return 1 - we inserted the row;
        $tg->expects($this->once())->method('insertWith')->will($this->returnValue(1));
        // insert will never be called
        $tg->expects($this->never())->method('insert');
        $tg->expects($this->once())->method('getResultSetPrototype')->will($this->returnValue(new HydratingResultSet()));
        $data = ['id' => 3, 'code' => 'code1'];
        $object = new \ArrayObject($data);

        $this->table = new TableMapper($tg, 'id', 'id');
        $this->table->save($object);
    }

    /**
     * we insert model, which happen to already exist in the DB
     * @test
     */
    public function saveExisting()
    {
        $tg = $this->getTableGatewayMock();
        // update retuns 1 - row was updated
        $tg->expects($this->once())->method('update')->will($this->returnValue(1));
        // both inserts will never be called
        $tg->expects($this->never())->method('insertWith');
        $tg->expects($this->never())->method('insert');
        $tg->expects($this->once())->method('getResultSetPrototype')->will($this->returnValue(new HydratingResultSet()));
        $data = ['code' => 'code1'];
        $object = new \ArrayObject($data);

        $this->table = new TableMapper($tg, 'code');
        $this->table->save($object);
    }

    /**
     * we are saving model which data wasn't altered
     * no db operations should be performed
     * @test
     */
    public function saveExistingNotChanged()
    {
        $dataArray = [
            'code' => 'code1',
            'title' => 'Best',
        ];
        // set up object
        $object = $this->getMock('Maketok\Model\LazyModelInterface');
        //check it's called once because we mocked the hydrator
        $object->expects($this->once())->method('processOrigin')->will($this->returnValue($dataArray));

        $hydrator = $this->getMock('Maketok\Util\Hydrator\ObjectProperty');
        $hydrator->expects($this->once())->method('extract')->will($this->returnValue($dataArray));

        $tg = $this->getTableGatewayMock();
        // data wasn't changed, no db operations should happen
        $tg->expects($this->never())->method('update');
        $tg->expects($this->never())->method('insertWith');
        $tg->expects($this->never())->method('insert');
        $tg->expects($this->once())
            ->method('getResultSetPrototype')
            ->will($this->returnValue(new HydratingResultSet($hydrator, $object)));

        $this->table = new TableMapper($tg, 'code');
        $this->table->save($object);
    }

    /**
     * @test
     */
    public function saveNewAIMissing()
    {
        $tg = $this->getTableGatewayMock();
        // insert returns 1 row affected
        $tg->expects($this->once())->method('insert')->will($this->returnValue(1));
        // last inserted id is assigned
        $tg->expects($this->once())->method('getLastInsertValue')->will($this->returnValue(4));
        // update or insert ignore isn't happening
        $tg->expects($this->never())->method('insertWith');
        $tg->expects($this->never())->method('update');
        $tg->expects($this->once())->method('getResultSetPrototype')->will($this->returnValue(new HydratingResultSet()));
        $data = ['code' => 'code1', 'updated_at' => null, 'created_at' => null];
        $object = new \ArrayObject($data);

        $this->table = new TableMapper($tg, 'id', 'id');
        $this->table->save($object);
        $this->assertEquals(4, $object->id);
    }

    /**
     * prepare storage
     */
    public function setUp()
    {
        /** @var \Zend\Db\Adapter\Adapter $adapter */
        $adapter = $this->ioc()->get('adapter');
        $query = <<<'SQL'
CREATE TABLE t1 (
  id INTEGER PRIMARY KEY ASC,
  name TEXT,
  created_at TEXT,
  author TEXT
)
SQL;
        $adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
        $factory = new ArrayTableFactory('t1', 'id', 'array', 'id');
        $this->table = $factory->spawnTable();
    }

    /**
     * remove storage
     */
    public function tearDown()
    {
        /** @var \Zend\Db\Adapter\Adapter $adapter */
        $adapter = $this->ioc()->get('adapter');
        $adapter->query('DROP TABLE t1', Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * save/find process
     * @test
     */
    public function save()
    {
        $tm = $this->table;
        $data = [
            'name' => 'Sherlock',
            'author' => 'Arthur',
            'created_at' => null,
        ];
        $tm->save($data);

        $data = $tm->find(1);
        $this->assertNotEmpty($data);
        $this->assertEquals('Sherlock', $data['name']);
    }

    /**
     * save/fetch process
     * @test
     */
    public function fetchFilter()
    {
        $tm = $this->table;
        $data = [
            'name' => 'Sherlock',
            'author' => 'Arthur',
            'created_at' => null,
        ];
        $tm->save($data);

        $resultSet = $tm->fetchFilter(['author' => 'Arthur']);
        $this->assertTrue($resultSet->count() === 1);
        $data = $resultSet->current();
        $this->assertEquals('Sherlock', $data['name']);
    }

    /**
     * delete process
     * @test
     */
    public function delete()
    {
        $tm = $this->table;
        $data = [
            'name' => 'Sherlock',
            'author' => 'Arthur',
            'created_at' => null,
        ];
        $tm->save($data);

        $tm->delete($data);
        $this->assertTrue($tm->isDeleted($data));

        $resultSet = $tm->fetchFilter(['author' => 'Arthur']);
        $this->assertTrue($resultSet->count() === 0);
    }

    /**
     * try to save deleted entity
     * @test
     * @expectedException \Maketok\Util\Exception\ModelException
     * @expectedExceptionMessage The model is already deleted.
     */
    public function saveDeleted()
    {
        $tm = $this->table;
        $data = [
            'name' => 'Sherlock',
            'author' => 'Arthur',
            'created_at' => null,
        ];
        $tm->save($data);

        $tm->delete($data);
        $data['author'] = 'Conan';
        $tm->save($data);
    }
}
