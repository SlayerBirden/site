<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Util;

class StreamHandler implements StreamHandlerInterface
{

    protected $_path;
    protected $_handler;

    const PERMISSIONS = 0755;

    /**
     * @param null|string $path
     * @param string $data
     * @return bool
     */
    public function writeWithLock($data, $path = null)
    {
        $this->_initHandler($path, 'c+');
        $result = false;
        if ($this->lock($path)) {
            ftruncate($this->_handler, 0);
            $result = fwrite($this->_handler, $data);
            $result = $result !== false;
            $result = $result && $this->unLock($path);
        }
        return $result;
    }
    /**
     * @param null|string $path
     * @param string $data
     * @return bool
     */
    public function write($data, $path = null)
    {
        $this->_initHandler($path, 'w');
        // truncate all file in case it was opened with c+
        ftruncate($this->_handler, 0);
        $result = fwrite($this->_handler, $data);
        // rewind pointer if we need to read later
        rewind($this->_handler);
        return $result !== false;
    }

    /**
     * @param null $path
     * @param string $mode
     * @throws \Exception
     */
    protected function _initHandler($path = null, $mode = 'w')
    {
        if (is_resource($this->_handler)) {
            return;
        }
        if (is_null($path)) {
            $path = $this->_path;
        }
        if (is_null($path)) {
            throw new \Exception('The path to write is not specified.');
        }
        $dirName = dirname($path);
        if (!is_dir($dirName)) {
            mkdir($dirName, self::PERMISSIONS, true);
        }
        $this->_handler = fopen($path, $mode);
    }

    /**
     * @param int $length
     * @param null|string $path
     * @return string
     */
    public function read($length = null, $path = null)
    {
        $this->_initHandler($path, 'r');
        if (is_null($length)) {
            if (is_null($path)) {
                $path = $this->_path;
            }
            $length = filesize($path);
        }
        return fread($this->_handler, $length);
    }

    /**
     * @param null|string $path
     * @param bool|int $includeDirectories
     * @return bool
     */
    public function delete($path = null, $includeDirectories = false)
    {
        if (is_null($path)) {
            $path = $this->_path;
        }
        if (is_dir($path)) {
            $res = rmdir($path);
        } else {
            $res = unlink($path);
        }
        if (is_int($includeDirectories) && $includeDirectories > 0) {
            $path = dirname($path);
            $res = $res && $this->delete($path, $includeDirectories - 1);
        }
        return $res;
    }

    /**
     * @param null|string $path
     * @return bool
     */
    public function lock($path = null)
    {
        $this->_initHandler($path, 'c');
        return flock($this->_handler, LOCK_EX | LOCK_NB);
    }

    /**
     * @param null|string $path
     * @return bool
     */
    public function unLock($path = null)
    {
        $this->_initHandler($path, 'c+');
        return flock($this->_handler, LOCK_UN);
    }

    /**
     * destroy handler
     * @param null|string $path
     * @return mixed
     */
    public function setPath($path)
    {
        $this->_path = $path;
        $this->_handler = null;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return bool
     */
    public function eof()
    {
        if (is_resource($this->_handler)) {
            return feof($this->_handler);
        }
        return true;
    }

    /**
     * @return void
     */
    public function close()
    {
        if (is_resource($this->_handler)) {
            fclose($this->_handler);
            $this->_handler = null;
        }
    }
}