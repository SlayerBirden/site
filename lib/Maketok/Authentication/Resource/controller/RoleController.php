<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Authentication\Resource\controller;

use Maketok\Authentication\Resource\Model\User;
use Maketok\Authentication\Resource\Model\UserTable;
use Maketok\Http\Request;
use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Mvc\RouteException;
use Maketok\Util\Exception\ModelException;
use Maketok\Util\Exception\ModelInfoException;
use Symfony\Component\Form\FormInterface;

class RoleController extends AbstractAdminController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $this->setTemplate('role.create.html.twig');
        $form = $this->getFormFactory()->create('create_user_role');
        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->handleRole($form);
        }
        return $this->prepareResponse($request, [
            'title' => $this->trans('Maketok Admin - Create New User Role'),
            'description' => $this->trans('Role Creation'),
            'form' => $form->createView(),
        ]);
    }

    /**
     * handle form request
     * @param FormInterface $form
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function handleRole(FormInterface $form)
    {
        /** @var UserTable $table */
        $table = $this->ioc()->get('auth_role_table');
        try {
            $data = $form->getData();
            $table->save($data);
            $this->getSession()->getFlashBag()->add('success', 'The user role was saved successfully!');
        } catch (ModelInfoException $e) {
            $this->getSession()->getFlashBag()->add(
                'warning',
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->getLogger()->err($e);
            $this->getSession()->getFlashBag()->add(
                'error',
                sprintf("There was an error processing your request.\nThe error text: %s", $e->getMessage())
            );
            return $this->returnBack();
        }
        return $this->redirect('auth/roles');
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws RouteException
     */
    public function editAction(Request $request)
    {
        $this->setTemplate('role.create.html.twig');
        $data = $this->initRole($request);
        $form = $this->getFormFactory()->create('create_user_role', $data);
        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->handleRole($form);
        }
        return $this->prepareResponse($request, [
            'title' => $this->trans('Maketok Admin - Edit User Role'),
            'description' => $this->trans('User Role "title"', $data),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws RouteException
     */
    public function deleteAction(Request $request)
    {
        $data = $this->initRole($request);
        /** @var UserTable $table */
        $table = $this->ioc()->get('auth_role_table');
        try {
            $table->delete($data);
            $this->addSessionMessage('success', "The user role was deleted.");
        } catch (\Exception $e) {
            $this->getLogger()->error(sprintf("Could not remove user role #%d", $data['id']));
        }
        return $this->redirect('/auth/roles');
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rolesAction(Request $request)
    {
        $this->setTemplate('roles.html.twig');
        /** @var UserTable $table */
        $table = $this->ioc()->get('auth_role_table');
        try {
            $datas = $table->fetchAll();
        } catch (\Exception $e) {
            $this->getLogger()->err($e);
            $datas = [];
        }
        return $this->prepareResponse($request, [
            'title' => $this->trans('Maketok Admin - User Roles management'),
            'description' => $this->trans('User Roles'),
            'roles' => $datas,
        ]);
    }

    /**
     * @param Request $request
     * @return array
     * @throws RouteException
     */
    protected function initRole(Request $request)
    {
        $id = $request->getAttributes()->get('id');
        if ($id === null) {
            // route exception will lead to 404
            throw new RouteException("Can not process user role without id.");
        }
        /** @var UserTable $articleTable */
        $articleTable = $this->ioc()->get('auth_role_table');
        try {
            return $articleTable->find($id);
        } catch (ModelException $e) {
            throw new RouteException("Could not find user role by id.");
        }
    }
}
