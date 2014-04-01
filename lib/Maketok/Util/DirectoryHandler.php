<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
namespace Maketok\Util;

class DirectoryHandler
{

    protected $_path;
    protected $_handle;

    const PERMISSIONS = 0755;


    /**
     * @param null $path
     * @throws \Exception
     */
    protected function _initHandle($path = null)
    {
        if (is_resource($this->_handle)) {
            return;
        }
        if (is_null($path)) {
            $path = $this->_path;
        }
        if (is_null($path)) {
            throw new \Exception('The path to write is not specified.');
        }
        $this->_handle = opendir($path);
    }

    /**
     * @param null|string $path
     * @return bool
     */
    public function delete($path = null)
    {
        $res = false;
        if (is_dir($path)) {
            $res = rmdir($path);
        }
        return $res;
    }

    /**
     * @param null|string $path
     * @param int $permissions
     * @param bool $recursive
     * @return bool
     */
    public function make($path = null, $permissions = self::PERMISSIONS, $recursive = true)
    {
        $res = false;
        if (is_dir($path)) {
            $res = mkdir($path, $permissions, $recursive);
        }
        return $res;
    }

    /**
     * destroy handler
     * @param null|string $path
     * @return mixed
     */
    public function setPath($path)
    {
        $this->_path = $path;
        $this->_handle = null;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param null|string $path
     * @return array
     * @throws \Exception
     */
    public function ls($path = null)
    {
        if (is_string($path)) {
            $this->_initHandle($path);
        } elseif (!is_resource($this->_handle)) {
            throw new \Exception('Empty handle');
        }
        $dirs = array();
        while (false !== ($entry = readdir($this->_handle))) {
            $dirs[] = $entry;
        }
        return $dirs;
    }

    /**
     * @return void
     */
    public function close()
    {
        if (is_resource($this->_handle)) {
            closedir($this->_handle);
            $this->_handle = null;
        }
    }
}