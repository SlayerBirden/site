<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\pages;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Installer\Ddl\ClientInterface;
use Maketok\Module\ConfigInterface;
use Maketok\Mvc\Router\Route\Http\Literal;
use modules\pages\Model\PageTable;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Config extends Extension implements ConfigInterface, ClientInterface
{
    use UtilityHelperTrait;
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getDdlVersion()
    {
        return $this->getVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function getDdlConfig($version)
    {
        return current($this->ioc()->get('config_getter')->getConfig(__DIR__ . "/config/installer/ddl", $version));
    }

    /**
     * {@inheritdoc}
     */
    public function getDdlCode()
    {
        return $this->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/config/di')
        );
        $loader->load('services.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return $this->getCode();
    }


    /**
     * {@inheritdoc}
     */
    public function initRoutes()
    {
        /** @var PageTable $table */
        $table = $this->ioc()->get('pages_table');
        foreach ($table->fetchActive() as $page) {
            $this->getRouter()->addRoute(
                new Literal(
                    $page->code,
                    ['\modules\pages\controller\PageController', 'indexAction'],
                    ['page_id' => $page->id]
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '0.1.1';
    }

    /**
     * {@inheritdoc}
     */
    public function initListeners()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'pages';
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getCode();
    }
}
