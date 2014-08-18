<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project store
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace modules\cover;


use Maketok\App\Site;
use Maketok\Module\AdminConfigInterface;
use Maketok\Mvc\Router\Route\Http\Literal;
use Maketok\Mvc\Router\Route\Http\Parameterized;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AdminConfig extends Config implements AdminConfigInterface, ExtensionInterface
{

    public function initRoutes()
    {
        Site::getServiceContainer()->get('router')->addRoute(new Literal('/', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\cover\\controller\\admin\\Index',
            'action' => 'index',
        )));
        Site::getServiceContainer()->get('router')->addRoute(new Literal('/modules', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\cover\\controller\\admin\\Modules',
            'action' => 'index',
        )));
        Site::getServiceContainer()->get('router')->addRoute(new Parameterized('/modules/{code}', array(
            'module' => $this->getCode(),
            'controller' => 'modules\\cover\\controller\\admin\\Modules',
            'action' => 'view',
        ), [], ['code' => '\w+']));
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
        $loader->load('services.yml');
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
     * Returns the recommended alias to use in XML.
     *
     * This alias is also the mandatory prefix to use when using YAML.
     *
     * @return string The alias
     *
     * @api
     */
    public function getAlias()
    {
        return $this->getCode();
    }
}
