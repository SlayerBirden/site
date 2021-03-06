<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util;

use Maketok\Util\Exception\StreamException;

class StreamHandler implements StreamHandlerInterface
{
    protected $path;
    protected $handle;

    const PERMISSIONS = 0755;

    /**
     * @param  null|string   $path
     * @param  string        $data
     * @return bool|null|int
     */
    public function writeWithLock($data, $path = null)
    {
        $this->initHandle($path, 'c+');
        $result = null;
        if ($this->lock($path)) {
            ftruncate($this->handle, 0);
            // do not write at the middle of no where
            rewind($this->handle);
            $result = fwrite($this->handle, $data);
            $this->unLock($path);
        }

        return $result;
    }
    /**
     * @param  null|string $path
     * @param  string      $data
     * @return bool
     */
    public function write($data, $path = null)
    {
        $this->initHandle($path, 'w');
        // truncate all file in case it was opened with c+
        ftruncate($this->handle, 0);
        // do not write at the middle of no where
        rewind($this->handle);
        $result = fwrite($this->handle, $data);
        // rewind pointer if we need to read later
        rewind($this->handle);

        return $result !== false;
    }

    /**
     * @param  null|string     $path
     * @param  string          $mode
     * @throws StreamException
     */
    protected function initHandle($path = null, $mode = 'w')
    {
        if (is_resource($this->handle)) {
            return;
        }
        if (is_null($path)) {
            $path = $this->path;
        }
        if (is_null($path)) {
            throw new StreamException('The path to write is not specified.');
        }
        $dirName = dirname($path);
        if (!is_dir($dirName)) {
            mkdir($dirName, self::PERMISSIONS, true);
        }
        $this->handle = fopen($path, $mode);
    }

    /**
     * @param  int         $length
     * @param  null|string $path
     * @return string
     */
    public function read($length = null, $path = null)
    {
        $this->initHandle($path, 'r+');
        if (is_null($length)) {
            if (is_null($path)) {
                $path = $this->path;
            }
            $length = filesize($path);
            if ($length <= 0) {
                $length = 1;
            }
        }

        return fread($this->handle, $length);
    }

    /**
     * @param  null|string $path
     * @param  bool|int    $includeDirectories
     * @return bool
     */
    public function delete($path = null, $includeDirectories = false)
    {
        if (is_null($path)) {
            $path = $this->path;
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
     * @param  null|string $path
     * @return bool
     */
    public function lock($path = null)
    {
        $this->initHandle($path, 'c');

        return flock($this->handle, LOCK_EX | LOCK_NB);
    }

    /**
     * @param  null|string $path
     * @return bool
     */
    public function unLock($path = null)
    {
        $this->initHandle($path, 'c+');

        return flock($this->handle, LOCK_UN);
    }

    /**
     * destroy handler
     * @param  null|string $path
     * @return mixed
     */
    public function setPath($path)
    {
        $this->path = $path;
        $this->handle = null;
    }

    /**
     * @codeCoverageIgnore
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return bool
     */
    public function eof()
    {
        if (is_resource($this->handle)) {
            return feof($this->handle);
        }

        return true;
    }

    /**
     * @return void
     */
    public function close()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    /**
     * @return string|null $path
     */
    public function pwd()
    {
        return $this->path;
    }
}
