<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer
 */
namespace Maketok\App\Session;

use Maketok\App\Site;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Maketok\App\Ddl\InstallerApplicableInterface;

class DbHandler implements \SessionHandlerInterface, InstallerApplicableInterface
{

    protected $_table = 'session_storage';
    /**
     * @var Sql
     */
    protected $_sql;

    /**
     * PHP >= 5.4.0<br/>
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterafce.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function close()
    {
        $this->gc(ini_get('session.gc_maxlifetime'));
    }

    /**
     * PHP >= 5.4.0<br/>
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterafce.destroy.php
     * @param int $session_id The session ID being destroyed.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
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
     * PHP >= 5.4.0<br/>
     * Cleanup old sessions
     * @link http://php.net/manual/en/sessionhandlerinterafce.gc.php
     * @param int $maxlifetime <p>
     * Sessions that have not updated for
     * the last maxlifetime seconds will be removed.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function gc($maxlifetime)
    {
        $stamp = time() - $maxlifetime;
        $expirationDate = new \DateTime($stamp);

        $where = new Where();
        $where->lessThan('updated_at', $expirationDate->format('Y-m-d H:i:s'));
        $delete = $this->_sql->delete()->where($where);
        $statement = $this->_sql->prepareStatementForSqlObject($delete);
        $statement->execute();
        return true;
    }

    /**
     * PHP >= 5.4.0<br/>
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterafce.open.php
     * @param string $save_path The path where to store/retrieve the session.
     * @param string $session_id The session id.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function open($save_path, $session_id)
    {
        $this->_sql = new Sql(Site::getAdapter(), $this->_table);
        return true;
    }

    /**
     * PHP >= 5.4.0<br/>
     * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterafce.read.php
     * @param string $session_id The session id to read data for.
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function read($session_id)
    {
        $select = $this->_sql->select()->columns(array('data'))->where(array('session_id' => $session_id));
        $statement = $this->_sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        foreach ($result as $row) {
            return $row['data'];
        }
        return '';
    }

    /**
     * PHP >= 5.4.0<br/>
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterafce.write.php
     * @param string $session_id The session id.
     * @param string $session_data <p>
     * The encoded session data. This data is the
     * result of the PHP internally encoding
     * the $_SESSION superglobal to a serialized
     * string and passing it as this parameter.
     * Please note sessions use an alternative serialization method.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function write($session_id, $session_data)
    {

        $data = $this->read($session_id);
        $now = new \DateTime();
        if (empty($data)) {
            $insert = $this->_sql->insert()
                ->columns('session_id', 'data', 'updated_at')
                ->values($session_id, $session_data, $now->format('Y-m-d H:i:s'));
        } else {
            $insert = $this->_sql->update()
                ->set(array('data' => $session_data, 'updated_at' => $now->format('Y-m-d H:i:s')))
                ->where(array('session_id' => $session_id));
        }
        $statement = $this->_sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();
        if ($result->getAffectedRows() > 0) {
            return true;
        }
        return false;
    }

    public function __destruct()
    {
        session_write_close();
    }

    /**
     * @return array
     */
    public static function getDdlConfig()
    {
        return array(
            'session_storage' => array(
                'columns' => array(
                    'session_id' => array(
                        'type' => 'varchar',
                        'length' => 32,
                    ),
                    'data' => array(
                        'type' => 'text',
                    ),
                    'updated_at' => array(
                        'type' => 'time',
                    ),
                ),
                'constraints' => array(
                    'primaryKey' => array('session_id'),
                ),
            )
        );
    }

    /**
     * @return string
     */
    public static function getDdlConfigVersion()
    {
        return '0.1.0';
    }

    /**
     * @return string
     */
    public static function getDdlConfigName()
    {
        return 'session_storage';
    }
}