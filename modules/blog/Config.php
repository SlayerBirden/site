<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace modules\blog;


use Maketok\App\Site;
use Maketok\Installer\Ddl\ClientInterface;
use Maketok\Module\ConfigInterface;
use Maketok\Mvc\Router\Route\Http\Literal;
use Maketok\Mvc\Router\Route\Http\Parameterized;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Config implements ConfigInterface, ExtensionInterface, ClientInterface
{

    /**
     * @return string
     */
    public function getVersion()
    {
        return '0.1.3';
    }

    /**
     * @return string
     */
    public function getDdlConfigVersion()
    {
        return $this->getVersion();
    }

    /**
     * @return string
     */
    public function getDdlConfigName()
    {
        return 'blog';
    }

    public function initRoutes()
    {
        Site::getServiceContainer()->get('router')->addRoute(new Literal('/blog', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\blog\\controller\\Index',
            'action' => 'index',
        )));
        Site::getServiceContainer()->get('router')->addRoute(new Parameterized('/blog/{code}', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\blog\\controller\\Article',
            'action' => 'index',
        ), [], ['code' => '^[a-zA-Z0-9_.-]+$']));
        Site::getServiceContainer()->get('router')->addRoute(new Parameterized('/blog/article/{id}', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\blog\\controller\\Article',
            'action' => 'index',
        ), [], ['id' => '^\d+$']));
    }

    /**
     * {@inheritdoc}
     */
    public function initListeners()
    {
        return;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'blog';
    }

    /**
     * magic method for returning string representation of the the config class
     * @return string
     */
    public function __toString()
    {
        return $this->getCode();
    }

    /**
     * Loads a specific configuration.
     *
     * @param array $config An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/config')
        );
        $loader->load('parameters.yml');
        $loader->load('services.yml');
        // validator config files
        $validatorYmlConfigPaths = [];
        if ($container->hasParameter('validator_builder.yml.config.paths')) {
            $validatorYmlConfigPaths = $container->getParameter('validator_builder.yml.config.paths');
        }
        if (!array($validatorYmlConfigPaths)) {
            Site::getServiceContainer()->get('logger')->error("Wrong parameter type for validator_builder.yml.config.paths.");
            return;
        }
        $validatorYmlConfigPaths[] = __DIR__. DS .'config' . DS . 'validation.yml';
        $container->setParameter('validator_builder.yml.config.paths', $validatorYmlConfigPaths);
    }

    /**
     * Returns the namespace to be used for this extension (XML namespace).
     *
     * @return string The XML namespace
     *
     * @api
     */
    public function getNamespace()
    {
        return 'http://www.example.com/symfony/schema/';
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     *
     * @api
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/config';
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return $this->getCode();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return false;
    }

    /**
     * client register dependencies (parents)
     * it must register dependencies to change resources that were created by other clients
     *
     * @return array
     */
    public function getDependencies()
    {
        return [];
    }

    /**
     * get client version to install
     *
     * @return string
     */
    public function getDdlVersion()
    {
        return $this->getVersion();
    }

    /**
     * get client identifier
     * must be unique
     *
     * @return string
     */
    public function getDdlCode()
    {
        return $this->getCode();
    }

    /**
     * get client config to install
     *
     * @param string $version
     * @return array|bool
     */
    public function getDdlConfig($version)
    {
        return include __DIR__ . "/config/ddl/$version.php";
    }
}
