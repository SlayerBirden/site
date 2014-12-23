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
    private $loaders;

    /**
     * @param AbstractFileLoader[] $loaders
     */
    public function setLoaders($loaders)
    {
        $this->loaders = $loaders;
    }

    /**
     * @return AbstractFileLoader[]
     */
    public function getLoaders()
    {
        return $this->loaders;
    }

    /**
     * @param string|string[] $paths
     * @param string|string[] $fileName
     * @param string $prefix
     * @throws \Symfony\Component\Config\Exception\FileLoaderLoadException
     * @return mixed
     */
    public function getConfig($paths, $fileName, $prefix = '')
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
            $locator = new FileLocator($path);
            $this->setLoaders([new YamlFileLoader($locator), new PhpFileLoader($locator)]);
            $loader = $this->getLoader();
            $correctFileNames = [];
            foreach ($this->getLoaders() as $ld) {
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
     * @codeCoverageIgnore
     * @return DelegatingLoader
     */
    private function getLoader()
    {
        $resolver = new LoaderResolver($this->getLoaders());
        return new DelegatingLoader($resolver);
    }
}
