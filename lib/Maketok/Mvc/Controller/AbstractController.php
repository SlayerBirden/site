<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Mvc\Controller;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Http\Response;
use Maketok\Mvc\GenericException;
use Maketok\Template\EngineInterface;
use Maketok\Util\RequestInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AbstractController
{
    use UtilityHelperTrait;

    /** @var  Response */
    protected $response;

    /** @var  string */
    protected $body;

    /** @var  string */
    protected $module;

    /** @var  string */
    protected $template;

    /** @var string[] */
    private $templatePaths;

    /**
     * add base template path
     */
    public function __construct()
    {
        $rc = new \ReflectionClass($this);
        $root = dirname(dirname($rc->getFileName()));
        $view = $root . "/view";
        if (file_exists($view) && is_dir($view)) {
            $this->addTemplatePath($view);
        }
    }

    /**
     * @param  string|null $content
     * @param  int         $code
     * @param  array|null  $headers
     * @return Response
     */
    public function getResponse($content = null, $code = 200, array $headers = null)
    {
        if (is_null($this->response)) {
            $this->initResponse($content, $code, $headers);
        }

        return $this->response;
    }

    /**
     * @param  string|null      $content
     * @param  int              $code
     * @param  array|null       $headers
     * @throws GenericException
     */
    protected function initResponse($content, $code, $headers)
    {
        if (is_null($content) && !is_null($this->body)) {
            $content = $this->body;
        } elseif (is_null($content) && is_null($this->body)) {
            throw new GenericException("Can't initiate Response object without any body.");
        }
        if (is_null($headers)) {
            $headers = array();
        }
        $this->response = Response::create($content, $code, $headers);
    }

    /**
     * @param  RequestInterface                           $request
     * @param  array                                      $templateVars
     * @param  array                                      $params
     * @param  int                                        $httpCode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function prepareResponse(RequestInterface $request, array $templateVars, array $params = null, $httpCode = 200)
    {
        if (is_null($this->response)) {
            $this->prepareContent($templateVars, $params);
            $this->initResponse($this->getContent(), $httpCode, array());
        }

        return $this->response->prepare($request);
    }

    /**
     * @param  array $templateVars
     * @return void
     */
    public function prepareContent(array $templateVars)
    {
        // get template Engine
        /** @var EngineInterface $engine */
        $engine = $this->getSC()->get('template_engine');
        $templateVars = array_merge($this->getDefaults(), $templateVars);
        $engine->loadDependencies($this->getTemplatePaths());
        $engine->loadTemplate($this->getTemplate());
        $engine->setVariables($templateVars);
        $this->body = $engine->render();
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return [
            'css_url' => $this->getUrl('/dist/css/'),
            'js_url' => $this->getUrl('/dist/js/'),
            'fonts_url' => $this->getUrl('/dist/fonts/'),
            'dist_url' => $this->getUrl('/dist/'),
            'images_url' => $this->getUrl('/dist/images/'),
            'base_css_url' => $this->getUrl('/dist/css/', ['clear_path' => 1]),
            'base_js_url' => $this->getUrl('/dist/js/', ['clear_path' => 1]),
            'base_fonts_url' => $this->getUrl('/dist/fonts/', ['clear_path' => 1]),
            'base_dist_url' => $this->getUrl('/dist/', ['clear_path' => 1]),
            'base_images_url' => $this->getUrl('/dist/images/', ['clear_path' => 1]),
            'base_url' => $this->getUrl('/', ['wts' => 1]),
            'current_url' => $this->getCurrentUrl(),
            'path_info' => $this->getRouter()->getRequest()->getPathInfo(),
            'session' => $this->getSession()
        ];
    }

    /**
     * @param  array  $templateVars
     * @return string
     */
    public function render(array $templateVars)
    {
        $this->prepareContent($templateVars);

        return $this->getContent();
    }

    /**
     * @param  string $path
     * @return self
     */
    public function addTemplatePath($path)
    {
        $this->templatePaths[] = $path;

        return $this;
    }

    /**
     * @throws GenericException
     * @return string[]
     */
    public function getTemplatePaths()
    {
        return $this->templatePaths;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->body;
    }

    /**
     * @param  string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->ioc()->get('form_builder')
            ->getFormFactory();
    }

    /**
     * @param  string           $url
     * @return RedirectResponse
     */
    protected function redirectUrl($url)
    {
        return new RedirectResponse($url);
    }

    /**
     * @param  string           $path
     * @return RedirectResponse
     */
    protected function redirect($path)
    {
        $url = $this->getUrl($path);

        return $this->redirectUrl($url);
    }

    /**
     * @return RedirectResponse
     */
    protected function returnBack()
    {
        $referer = $this->ioc()->get('request')->getHeaders()->get('referer');

        return $this->redirectUrl($referer);
    }
}
