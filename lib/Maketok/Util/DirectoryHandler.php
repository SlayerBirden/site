<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util;

use Maketok\Util\Exception\DirectoryException;

class DirectoryHandler implements DirectoryHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function rm($path)
    {
        if (is_dir($path)) {
            $files = $this->ls($path, false);
            if (count($files)) {
                foreach ($files as $file) {
                    if ($file['path']) {
                        $this->rm($file['path']);
                    } elseif (file_exists($path . $file['name'])) {
                        $this->rm($path . $file['name']);
                    } elseif (file_exists($path . DS . $file['name'])) {
                        $this->rm($path . DS . $file['name']);
                    }
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
     * {@inheritdoc}
     */
    public function mkdir($path, $permissions = 0755, $recursive = true)
    {
        $res = mkdir($path, $permissions, $recursive);
        return $res;
    }


    /**
     * {@inheritdoc}
     */
    public function ls($path, $namesOnly = true)
    {
        if (!is_dir($path)) {
            throw new DirectoryException('The path does not exist.');
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
