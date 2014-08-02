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

    const PERMISSIONS = 0755;


    /**
     * @param string $path
     * @return bool
     */
    public function rm($path)
    {
        if (is_dir($path)) {
            $files = $this->ls($path, false);
            if (count($files)) {
                foreach ($files as $file) {
                    $this->rm($file['path']);
                }
                $res = rmdir($path);
            } else {
                $res = rmdir($path);
            }
        } else {
            $res = unlink($path);
        }
        return $res;
    }

    /**
     * @param string $path
     * @param int $permissions
     * @param bool $recursive
     * @return bool
     * @throws \Exception
     */
    public function mkdir($path, $permissions = self::PERMISSIONS, $recursive = true)
    {
        $res = mkdir($path, $permissions, $recursive);
        return $res;
    }


    /**
     * @param null|string $path
     * @param bool $namesOnly
     * @return array
     * @throws \Exception
     */
    public function ls($path, $namesOnly = true)
    {
        if (!is_dir($path)) {
            throw new \Exception('The path does not exist.');
        }
        $files = new \DirectoryIterator($path);
        $result = [];
        foreach($files as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isDot()) {
                continue;
            }
            if ($namesOnly) {
                $result[] = $file->getFilename();
            } else {
                $result[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getRealPath(),
                ];
            }
        }
        return $result;
    }

}