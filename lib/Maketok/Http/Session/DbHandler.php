<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Http\Session;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Http\Session\Resource\Model\Session;
use Maketok\Model\TableMapper;
use Zend\Db\Sql\Where;
use Maketok\Installer\Ddl\ClientInterface;

/**
 * Class DbHandler
 * @codeCoverageIgnore
 */
class DbHandler implements \SessionHandlerInterface, ClientInterface
{
    use UtilityHelperTrait;

    /**
     * @var TableMapper
     */
    private $tableMapper;

    /**
     * init
     * @param TableMapper $tableMapper
     */
    public function __construct(TableMapper $tableMapper)
    {
        $this->tableMapper = $tableMapper;
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
        try {
            $this->tableMapper->delete($session_id);
            return true;
        } catch (\Exception $e) {
            $this->getLogger()->err($e);
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
        try {
            $this->tableMapper->getGateway()->delete($where);
        } catch (\Exception $e) {
            $this->getLogger()->emerg($e);
        }
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
        /** @var Session $model */
        try {
            $model = $this->tableMapper->find($session_id);
            if ($this->isExpired($model)) {
                return '';
            }
            return $model->data;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param Session $session
     * @return bool
     */
    protected function isExpired(Session $session)
    {
        return $session->updated_at->add($session->lifetime) <= new \DateTime('now');
    }

    /**
     * {@inheritdoc}
     */
    public function write($session_id, $session_data)
    {
        $maxLifetime = (int) ini_get('session.gc_maxlifetime');
        try {
            $model = $this->tableMapper->find($session_id);
        } catch (\Exception $e) {
            $model = $this->tableMapper->getObjectPrototype();
        }
        try {
            /** @var Session $model */
            $model->session_id = $session_id;
            $model->data = $session_data;
            $model->lifetime = new \DateInterval("PT{$maxLifetime}S");
            $this->tableMapper->save($model);
        } catch (\Exception $e) {
            $this->getLogger()->emerg($e);
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
        return current($this->ioc()->get('config_getter')->getConfig(__DIR__.'/Resource/config/installer/ddl', $version));
    }

    /**
     * {@inheritdoc}
     * @pass
     */
    public function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getDdlVersion()
    {
        return '0.1.3';
    }

    /**
     * {@inheritdoc}
     */
    public function getDdlCode()
    {
        return 'session_storage';
    }
}
