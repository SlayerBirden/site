<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Module\Resource\controller\admin;

use Maketok\App\Site;
use Maketok\Module\Resource\Model\Module;
use Maketok\Module\Resource\Model\ModuleTable;
use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Mvc\RouteException;
use Maketok\Util\Exception\ModelException;
use Maketok\Util\Exception\ModelInfoException;
use Maketok\Util\RequestInterface;

class Modules extends AbstractAdminController
{

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(RequestInterface $request)
    {
        $this->setViewDependency(array('admin'));
        $this->setTemplate('modules.html.twig');
        /** @var ModuleTable $articleTable */
        $moduleTable = $this->getSC()->get('module_table');
        $modules = $moduleTable->fetchAll();
        return $this->prepareResponse($request, array(
            'title' => 'Maketok Admin - Modules',
            'modules' => $modules,
            'description' => 'Modules'
        ));
    }

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(RequestInterface $request)
    {
        $this->setViewDependency(array('admin'));
        $this->setTemplate('module.html.twig');
        $module = $this->initModule($request);
        $form = $this->getFormFactory()->create('module', $module, array(
            'action' => $this->getCurrentUrl(),
            'method' => 'POST',
            'attr' => array('back_url' => $this->getUrl('/modules')),
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var ModuleTable $moduleTable */
            $moduleTable = $this->getSC()->get('module_table');
            try {
                $moduleTable->save($form->getData());
                Site::getSession()->getFlashBag()->add(
                    'success',
                    'The module was updated successfully!'
                );
            } catch (ModelInfoException $e) {
                Site::getSession()->getFlashBag()->add(
                    'notice',
                    $e->getMessage()
                );
            } catch (\Exception $e) {
                $this->getSC()->get('logger')->err($e);
                Site::getSession()->getFlashBag()->add(
                    'error',
                    'There was an error processing your request. Our specialists will be looking into it.'
                );
                return $this->returnBack();
            }
            return $this->redirect('modules');
        }
        return $this->prepareResponse($request, array(
            'title' => 'Maketok Admin - View Module ' . $module->module_code,
            'description' => 'Module ' . $module->module_code,
            'module' => $module,
            'form' => $form->createView()
        ));
    }

    /**
     * @param RequestInterface $request
     * @return Module
     * @throws RouteException
     */
    protected function initModule(RequestInterface $request)
    {
        $code = $request->getAttributes()->get('code');
        if ($code === null) {
            throw new RouteException("Can not process module without code.");
        }
        /** @var ModuleTable $articleTable */
        $moduleTable = $this->getSC()->get('module_table');
        try {
            return $moduleTable->find($code);
        } catch (ModelException $e) {
            throw new RouteException("Could not find model by id.");
        }
    }
}
