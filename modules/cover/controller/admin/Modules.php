<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace modules\cover\controller\admin;

use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Mvc\RouteException;
use Maketok\Util\Exception\ModelException;
use Maketok\Util\RequestInterface;
use modules\cover\model\ModuleTable;

class Modules extends AbstractAdminController
{

    /**
     * @param RequestInterface $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(RequestInterface $request)
    {
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
        $this->setTemplate('module.html.twig');
        $module = $this->_initModule($request);
        $form = $this->getFormFactory()->create('module', $module, array(
            'action' => $this->getCurrentUrl(),
            'method' => 'POST',
            'attr' => array('back_url' => $this->getUrl('/modules')),
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var ModuleTable $moduleTable */
            $moduleTable = $this->getSC()->get('module_table');
            $moduleTable->save($form->getData());
            // todo success should go in session
            return $this->_redirect('modules');
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
     * @return \modules\cover\model\Module
     * @throws RouteException
     */
    protected function _initModule(RequestInterface $request)
    {
        $code = $request->attributes->get('code');
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
