<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\Authentication\Providers;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Authentication\Provider\DataBaseProvider;
use Maketok\Authentication\Resource\Model\User;
use Maketok\Http\Request;
use Maketok\Model\HydratingTableFactory;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Zend\Db\Adapter\Adapter;

class DataBaseProviderTest extends \PHPUnit_Framework_TestCase
{
    use UtilityHelperTrait;

    /**
     * @var \Maketok\Model\TableMapper
     */
    protected $tableMapper;

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
  username TEXT,
  firstname TEXT,
  lastname TEXT,
  password_hash TEXT,
  created_at TEXT,
  updated_at TEXT,
  roles TEXT
)
SQL;
        $adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
        $factory = new HydratingTableFactory('t1', 'id', new User(), 'id');
        $this->tableMapper = $factory->spawnTable();
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
     * @test
     */
    public function provide()
    {
        $user = new User();
        $user->username = 'oleg';
        $user->password_hash = 'test123';
        $user->roles = '0,1';
        $this->tableMapper->save($user);
        $dbProvider = new DataBaseProvider($this->tableMapper);
        $dbProvider->setEncoder(new PlaintextPasswordEncoder());
        $request = Request::create('/login_post', 'POST', ['username' => 'oleg', 'password' => 'test123']);

        $provided = $dbProvider->provide($request);
        $this->assertEquals($user->username, $provided->getUsername());
        $this->assertEquals($user->getRoles(), $provided->getRoles());
    }
}
