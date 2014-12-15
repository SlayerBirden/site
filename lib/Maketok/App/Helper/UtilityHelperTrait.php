<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\App\Helper;

use Maketok\Http\SessionInterface;
use Maketok\Mvc\Router\Route\RouteInterface;
use Maketok\Mvc\Router\RouterInterface;
use Maketok\Observer\SubjectManagerInterface;
use Maketok\Util\ArrayValueTrait;
use Monolog\Logger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;
use Zend\Uri\UriFactory;

/**
 * Utility belt (R)
 * @package Maketok\App\Helper
 */
trait UtilityHelperTrait
{
    use ContainerTrait;
    use ArrayValueTrait;

    /**
     * @codeCoverageIgnore
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->ioc()->get('session_manager');
    }

    /**
     * @codeCoverageIgnore
     * @return string
     * @deprecated
     */
    public function getBaseUrl()
    {
        return $this->ioc()->getParameter('base_url');
    }

    /**
     * @param string $path
     * @param array $config
     * @param string $baseUrl
     * @return string
     */
    public function getUrl($path, array $config = null, $baseUrl = null)
    {
        // @codeCoverageIgnoreStart
        if (is_null($baseUrl)) {
            $baseUrl = $this->ioc()->getParameter('base_url');
        }
        // @codeCoverageIgnoreEnd
        $uri = UriFactory::factory($baseUrl);
        // add left path delimiter even if there was one
        $path = '/' . ltrim($path, '/');
        // remove right path delimiter
        $path = rtrim($path, '/');
        if (!isset($config['wts']) || !$config['wts']) { // config Without Trailing Slash
            $path  = $path . '/';
        }
        $uri->setPath($path);
        return $uri->toString();
    }

    /**
     * Get current url
     * @codeCoverageIgnore
     * @return string
     */
    public function getCurrentUrl()
    {
        /** @var RouteInterface $route */
        $route = $this->ioc()->get('request')->attributes->get('_route');
        return $this->getUrl($route->assemble());
    }

    /**
     * @codeCoverageIgnore
     * @return SubjectManagerInterface
     */
    public function getDispatcher()
    {
        return $this->ioc()->get('subject_manager');
    }

    /**
     * @codeCoverageIgnore
     * @return Logger
     */
    public function getLogger()
    {
        return $this->ioc()->get('logger');
    }

    /**
     * @codeCoverageIgnore
     * @param string $path
     * @return array
     */
    public function parseYaml($path)
    {
        $locator = new FileLocator(dirname($path));
        $ymlReader = new Yaml();
        try {
            $file = $locator->locate(basename($path));
        } catch (\InvalidArgumentException $e) {
            $this->getLogger()->err($e->getMessage());
            return [];
        }
        return $ymlReader->parse($file);
    }

    /**
     * @codeCoverageIgnore
     * @return RouterInterface
     */
    public function getRouter()
    {
        return $this->ioc()->get('router');
    }
}
