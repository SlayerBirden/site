<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer
 */
namespace modules\cover\controller\admin;

use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Util\RequestInterface;
use modules\cover\model\ModuleTable;

class Modules extends AbstractAdminController
{

    public function indexAction(RequestInterface $request)
    {
        $this->setTemplate('modules.html.twig');
        /** @var ModuleTable $articleTable */
        $moduleTable = $this->getSC()->get('module_table');
        $modules = $moduleTable->fetchAll();
        return $this->prepareResponse($request, array('title' => 'Modules', 'modules' => $modules));
    }

    public function viewAction(RequestInterface $request)
    {
        $this->setTemplate('module.html.twig');
        $module = $this->_initModule($request);
        return $this->prepareResponse($request, array('title' => 'Modules', 'module' => $module));
    }

    /**
     * @param RequestInterface $request
     * @return \modules\cover\model\Module
     * @throws \Exception
     */
    protected function _initModule(RequestInterface $request)
    {
        $code = $request->attributes->get('code');
        if ($code === null) {
            throw new \Exception("Can not process module without code.");
        }
        /** @var ModuleTable $articleTable */
        $moduleTable = $this->getSC()->get('module_table');
        return $moduleTable->find($code);
    }
}
