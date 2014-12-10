<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;

interface StreamHandlerInterface
{
    /**
     * @param null|string $path
     * @param string $data
     * @return bool
     */
    public function write($data, $path = null);

    /**
     * @param null|string $path
     * @param string $data
     * @return bool
     */
    public function writeWithLock($data, $path = null);

    /**
     * @param int $length
     * @param null|string $path
     * @return string
     */
    public function read($length = null, $path = null);

    /**
     * @param null|string $path
     * @param bool|int $includeDirectories
     * @return bool
     */
    public function delete($path = null, $includeDirectories = false);

    /**
     * @param null|string $path
     * @return bool
     */
    public function lock($path = null);

    /**
     * @param null|string $path
     * @return bool
     */
    public function unLock($path = null);

    /**
     * @param null|string $path
     * @return mixed
     */
    public function setPath($path);

    /**
     * @return string $path
     */
    public function pwd();

    /**
     * @return bool
     */
    public function eof();

    /**
     * @return void
     */
    public function close();
}
