<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Installer;

use Maketok\Util\StreamHandlerInterface;
use Zend\Db\Adapter\Adapter;

abstract class AbstractManager implements ManagerInterface
{

    /** @var array */
    protected $_clients;
    /** @var Adapter */
    protected $_adapter;
    /** @var ConfigReaderInterface */
    protected $_reader;
    /** @var StreamHandlerInterface */
    protected $_streamHandler;
    /** @var array */
    protected $_messages;
    /** @var \Maketok\Util\Zend\Db\Sql\Sql  */
    protected $_sql;
    /** @var string */
    protected $_type;
    /** @var DirectivesInterface */
    protected $directives;
    /**
     * @var ResourceInterface
     */
    protected $_resource;

    /**
     * {@inheritdoc}
     */
    public function setStreamHandler(StreamHandlerInterface $handler)
    {
        $this->_streamHandler = $handler;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStreamHandler()
    {
        return $this->_streamHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getClients()
    {
        return $this->_clients;
    }

    /**
     * {@inheritdoc}
     */
    public function hasClients()
    {
        return count($this->_clients) > 0;
    }

    /**
     * the recursive compare function
     * should compare versions
     * for strings only!
     *
     * @param string $a
     * @param string $b
     * @throws \InvalidArgumentException
     * @return int
     */
    public function natRecursiveCompare($a, $b)
    {
        if (!is_string($a) || !is_string($b)) {
            throw new \InvalidArgumentException("Compared arguments must be strings.");
        }
        if (strpos($a, '.') || strpos($b, '.')) {
            $aA = explode('.', $a);
            $aB = explode('.', $b);
            $this->castEqualLength($aA, $aB);
            do {
                $a = array_shift($aA);
                $b = array_shift($aB);
            } while ($a == $b && !is_null($a) && !is_null($b));
            return $this->natRecursiveCompare((string) $a,(string) $b);
        } else {
            if ((int) $a > (int) $b) {
                return 1;
            } elseif ((int) $b > (int) $a) {
                return -1;
            } else {
                return 0;
            }
        }
    }

    /**
     * cast both array to equal length
     * @param array $a
     * @param array $b
     * @param mixed $placeholder
     */
    public function castEqualLength(array &$a, array &$b, $placeholder = '0')
    {
        $countA = count($a);
        $countB = count($b);
        if ($countA > $countB) {
            for ($i = $countB; $i < $countA; $i++){
                $b[] = $placeholder;
            }
        } elseif($countB > $countA) {
            for ($i = $countA; $i < $countB; $i++){
                $a[] = $placeholder;
            }
        }
    }

    /**
     * @return DirectivesInterface
     */
    public function getDirectives()
    {
        return $this->directives;
    }
}
