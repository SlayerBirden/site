<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Module\Resource\controller;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Http\Request;
use Maketok\Module\Resource\Model\Module;
use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Mvc\RouteException;
use Maketok\Util\Exception\ModelException;
use Maketok\Util\Exception\ModelInfoException;
use Maketok\Model\TableMapper;

class Modules extends AbstractAdminController
{
    use UtilityHelperTrait;

    /**
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $this->setTemplate('modules.html.twig');
        try {
            /** @var TableMapper $moduleTable */
            $moduleTable = $this->getSC()->get('module_table');
            $modules = $moduleTable->fetchAll();
        } catch (\Exception $e) {
            $this->getLogger()->emerg($e);
            $modules = [];
        }
        return $this->prepareResponse($request, array(
            'title' => $this->trans('Maketok Admin - Modules'),
            'modules' => $modules,
            'description' => $this->trans('Modules')
        ));
    }

    /**
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request)
    {
        $this->setTemplate('module.html.twig');
        $module = $this->initModule($request);
        $form = $this->getFormFactory()->create('module', $module);
        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var TableMapper $moduleTable */
            $moduleTable = $this->getSC()->get('module_table');
            try {
                $data = $form->getData();
                $moduleTable->save($data);
                $this->getSession()->getFlashBag()->add(
                    'success',
                    'The module was updated successfully!'
                );
            } catch (ModelInfoException $e) {
                $this->getSession()->getFlashBag()->add(
                    'notice',
                    $e->getMessage()
                );
            } catch (\Exception $e) {
                $this->getSC()->get('logger')->err($e);
                $this->getSession()->getFlashBag()->add(
                    'error',
                    'There was an error processing your request. Our specialists will be looking into it.'
                );

                return $this->returnBack();
            }

            return $this->redirect('modules');
        }

        return $this->prepareResponse($request, array(
            'title' => $this->trans('Maketok Admin - View Module'),
            'description' => $this->trans('Module "code"', ['code' => $module->module_code]),
            'module' => $module,
            'form' => $form->createView()
        ));
    }

    /**
     * @param  Request $request
     * @return Module
     * @throws RouteException
     */
    protected function initModule(Request $request)
    {
        $area = $request->getAttributes()->get('area');
        $code = $request->getAttributes()->get('module_code');
        if ($area === null) {
            throw new RouteException("Can not process module without area.");
        }
        if ($code === null) {
            throw new RouteException("Can not process module without code.");
        }
        /** @var TableMapper $moduleTable */
        $moduleTable = $this->getSC()->get('module_table');
        try {
            return $moduleTable->find(array('area' => $area, 'module_code' => $code));
        } catch (ModelException $e) {
            throw new RouteException("Could not find model by id.");
        }
    }
}
