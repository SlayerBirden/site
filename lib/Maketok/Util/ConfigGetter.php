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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

class ConfigGetter
{
    /**
     * @var AbstractFileLoader[]
     */
    private static $loaders;

    /**
     * @param string|string[] $paths
     * @param string|string[] $fileName
     * @param string $prefix
     * @throws \Symfony\Component\Config\Exception\FileLoaderLoadException
     * @return mixed
     */
    public static function getConfig($paths, $fileName, $prefix = '')
    {
        $configs = [];
        if (!is_array($fileName)) {
            $fileName = [$fileName];
        }
        if ($prefix) {
            $fileName = array_merge($fileName, array_map(function ($fn) use ($prefix) {
                return "$prefix.$fn";
            }, $fileName));
        }
        if (!is_array($paths)) {
            $paths = [$paths];
        }
        foreach ($paths as $path) {
            $loader = self::getLoader($path);
            $correctFileNames = [];
            foreach (self::$loaders as $ld) {
                $suffix = $ld->getExtension();
                $correctFileNames = array_merge($correctFileNames, array_map(function ($fn) use ($suffix) {
                    return "$fn.$suffix";
                }, $fileName));
            }
            foreach ($correctFileNames as $file) {
                try {
                    $config = $loader->load($file);
                    if (!is_array($config)) {
                        continue;
                    }
                } catch (\InvalidArgumentException $e) {
                    continue;
                }
                $configs[] = $config;
            }
        }
        return $configs;
    }

    /**
     * @param string $path
     * @return DelegatingLoader
     */
    private static function getLoader($path)
    {
        $locator = new FileLocator($path);
        self::$loaders = [new YamlFileLoader($locator), new PhpFileLoader($locator)];
        $resolver = new LoaderResolver(self::$loaders);
        return new DelegatingLoader($resolver);
    }
}
