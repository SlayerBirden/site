<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace modules\error;


use Maketok\Module\ConfigInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Config implements ConfigInterface, ExtensionInterface
{

    /**
     * @return string
     */
    public function getVersion()
    {
        return '0.1.0';
    }

    /**
     * {@inheritdoc}
     */
    public function initRoutes()
    {
        return;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'error';
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
            new FileLocator(__DIR__.'/config/di')
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

    /**
     * {@inheritdoc}
     */
    public function initListeners()
    {
        return;
    }
}
