<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer;

use Maketok\Util\StreamHandlerInterface;
use Zend\Db\Adapter\Adapter;

abstract class AbstractManager implements ManagerInterface
{
    /**
     * @var ClientInterface[]
     */
    protected $clients;
    /**
     * @var Adapter
     */
    protected $adapter;
    /**
     * @var ConfigReaderInterface
     */
    protected $reader;
    /**
     * @var StreamHandlerInterface
     */
    protected $streamHandler;
    /**
     * @var DirectivesInterface
     */
    protected $directives;
    /**
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function hasClients()
    {
        return count($this->clients) > 0;
    }

    /**
     * @return DirectivesInterface
     * @codeCoverageIgnore
     */
    public function getDirectives()
    {
        return $this->directives;
    }
}
