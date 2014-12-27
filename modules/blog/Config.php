<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\blog;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Installer\Ddl\ClientInterface;
use Maketok\Module\ConfigInterface;
use Maketok\Mvc\Router\Route\Http\Literal;
use Maketok\Mvc\Router\Route\Http\Parameterized;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @codeCoverageIgnore
 */
class Config extends Extension implements ConfigInterface, ClientInterface
{
    use UtilityHelperTrait;

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '0.1.3';
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'blog';
    }

    /**
     * {@inheritdoc}
     */
    public function initRoutes()
    {
        $this->getRouter()->addRoute(new Literal('/blog', ['\modules\blog\controller\Index', 'indexAction']));
        $this->getRouter()->addRoute(new Parameterized(
            '/blog/{code}',
            ['\modules\blog\controller\Article', 'indexAction'],
            [],
            ['code' => '^[a-zA-Z0-9_.-]+$']
        ));
        $this->getRouter()->addRoute(new Parameterized(
            '/blog/{code}',
            ['\modules\blog\controller\Article', 'indexAction'],
            [],
            ['code' => '^[a-zA-Z0-9_.-]+$']
        ));
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
    public function __toString()
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
        $loader->load('parameters.yml');
        $loader->load('services.yml');
        // validator config files
        $validatorYmlConfigPaths = [];
        if ($container->hasParameter('validator_builder.yml.config.paths')) {
            $validatorYmlConfigPaths = $container->getParameter('validator_builder.yml.config.paths');
        }
        if (!array($validatorYmlConfigPaths)) {
            $this->getLogger()->error("Wrong parameter type for validator_builder.yml.config.paths.");
            return;
        }
        $validatorYmlConfigPaths[] = __DIR__. DS .'config' . DS . 'validation.yml';
        $container->setParameter('validator_builder.yml.config.paths', $validatorYmlConfigPaths);
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
    public function isActive()
    {
        return false;
    }

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
    public function getDdlCode()
    {
        return $this->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getDdlConfig($version)
    {
        return current($this->ioc()->get('config_getter')->getConfig(__DIR__ . "/config/installer/ddl", $version));
    }
}
