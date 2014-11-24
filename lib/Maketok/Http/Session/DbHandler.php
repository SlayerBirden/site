<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Http\Session;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Maketok\Installer\Ddl\ClientInterface;

/**
 * Class DbHandler
 * @package Maketok\Http\Session
 */
class DbHandler implements \SessionHandlerInterface, ClientInterface
{

    /** @var Sql */
    protected $_sql;
    /** @var Adapter */
    protected $_adapter;

    /**
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->_adapter = $adapter;
        $this->_sql = new Sql($this->_adapter, $this->getDdlCode());
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->gc(ini_get('session.gc_maxlifetime'));
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($session_id)
    {
        $delete = $this->_sql->delete()->where(array('session_id' => $session_id));
        $statement = $this->_sql->prepareStatementForSqlObject($delete);
        $result = $statement->execute();
        if ($result->getAffectedRows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        $stamp = time() - $maxlifetime;
        $expirationDate = new \DateTime();
        $expirationDate->setTimestamp($stamp);

        $where = new Where();
        $where->lessThan('updated_at', $expirationDate->format('Y-m-d H:i:s'));
        $delete = $this->_sql->delete()->where($where);
        $statement = $this->_sql->prepareStatementForSqlObject($delete);
        $statement->execute();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function open($save_path, $session_id)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($session_id)
    {
        $select = $this->_sql->select()->columns(array('data'))->where(array('session_id' => $session_id));
        $statement = $this->_sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        foreach ($result as $row) {
            return $row['data'];
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($session_id, $session_data)
    {
        $now = new \DateTime();
        // try update first, and if failed, insert instead
        $update = $this->_sql->update()
            ->set(array('data' => $session_data, 'updated_at' => $now->format('Y-m-d H:i:s')))
            ->where(array('session_id' => $session_id));
        $statement = $this->_sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();
        if ($result->getAffectedRows() <= 0) {
            $insert = $this->_sql->insert()
                ->columns(array('session_id', 'data', 'updated_at'))
                ->values(array(
                    'session_id' => $session_id,
                    'data' => $session_data,
                    'updated_at' => $now->format('Y-m-d H:i:s'),
                ));
            $statement = $this->_sql->prepareStatementForSqlObject($insert);
            $result = $statement->execute();
            if ($result->getAffectedRows() <= 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * destruct routine
     */
    public function __destruct()
    {
        session_write_close();
    }

    /**
     * {@inheritdoc}
     */
    public function getDdlConfig($version)
    {
        return include __DIR__ . '/Resource/config/ddl/' . $this->getDdlVersion() . '.php';
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        // TODO: Implement getDependencies() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getDdlVersion()
    {
        return '0.1.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getDdlCode()
    {
        return 'session_storage';
    }
}
