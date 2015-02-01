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

use Maketok\Authentication\AuthException;
use Maketok\Authentication\Provider\DataBaseProvider;
use Maketok\Authentication\Resource\Model\ChangePassword;
use Maketok\Authentication\Resource\Model\NewUser;
use Maketok\Authentication\Resource\Model\UserTable;
use Maketok\Http\Request;
use Maketok\Model\TableMapper;
use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Mvc\RouteException;
use Maketok\Util\Exception\ModelException;
use Maketok\Util\Exception\ModelInfoException;
use Symfony\Component\Form\FormInterface;

class UserController extends AbstractAdminController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $this->setTemplate('user.create.html.twig');
        $form = $this->getFormFactory()->create('create_user', new NewUser());
        $response = $this->handleUser($request, $form, $this->ioc()->get('auth_user_table'));
        if (!$response) {
            $response = $this->prepareResponse($request, [
                'title' => $this->trans('Maketok Admin - Create New User'),
                'description' => $this->trans('User Creation'),
                'form' => $form->createView(),
            ]);
        }

        return $response;
    }

    /**
     * handle form request
     * @param Request $request
     * @param FormInterface $form
     * @param TableMapper $table
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function handleUser(Request $request, FormInterface $form, TableMapper $table)
    {
        try {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $this->validateUser($data);
                $table->save($data);
                $this->addSessionMessage('success', 'The user was saved successfully!');
                return $this->redirect('auth/users/');
            }
        } catch (ModelInfoException $e) {
            $this->addSessionMessage(
                'warning',
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->getLogger()->err($e);
            $this->addSessionMessage(
                'error',
                sprintf("There was an error processing your request.\nThe error text: %s", $e->getMessage())
            );
        }
        return false;
    }

    /**
     * @param NewUser|ChangePassword $user
     * @throws AuthException
     */
    protected function validateUser($user)
    {
        if (!($user instanceof NewUser) && !($user instanceof ChangePassword)) {
            return;
        }
        // validation of password vs confirmation is happening in form validation extension
        $provider = $this->ioc()->get('auth')->getProvider();
        if ($provider instanceof DataBaseProvider) {
            $user->password_hash = $provider->getEncoder()->encodePassword($user->password, false);
        } else {
            $user->password_hash = $user->password;
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws RouteException
     */
    public function editAction(Request $request)
    {
        /** @var UserTable $table */
        $table = $this->ioc()->get('auth_user_edit_table');
        $this->setTemplate('user.create.html.twig');
        $user = $this->initUser($request, $table);
        $form = $this->getFormFactory()->create('edit_user', $user);

        $response = $this->handleUser($request, $form, $table);
        if (!$response) {
            $response = $this->prepareResponse($request, [
                'title' => $this->trans('Maketok Admin - Edit User'),
                'description' => $this->trans('User "firstname"', ['firstname' => $user->firstname]),
                'form' => $form->createView(),
            ]);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws RouteException
     */
    public function changePasswordAction(Request $request)
    {
        /** @var UserTable $table */
        $table = $this->ioc()->get('auth_user_password_change_table');
        $this->setTemplate('user.create.html.twig');
        $user = $this->initUser($request, $table);
        $form = $this->getFormFactory()->create('change_password', $user);

        $response = $this->handleUser($request, $form, $table);
        if (!$response) {
            $response = $this->prepareResponse($request, [
                'title' => $this->trans('Maketok Admin - Change User Password'),
                'description' => $this->trans('Password for User "firstname"', ['firstname' => $user->firstname]),
                'form' => $form->createView(),
            ]);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws RouteException
     */
    public function deleteAction(Request $request)
    {
        /** @var UserTable $userTable */
        $userTable = $this->ioc()->get('auth_user_table');
        $user = $this->initUser($request, $userTable);
        try {
            $userTable->delete($user);
            $this->addSessionMessage('success', "The user was deleted!");
        } catch (\Exception $e) {
            $this->getLogger()->error(sprintf("Could not remove user #%d", $user->id));
        }
        return $this->redirect('/auth/users');
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usersAction(Request $request)
    {
        $this->setTemplate('users.html.twig');
        /** @var UserTable $userTable */
        $userTable = $this->ioc()->get('auth_user_table');
        try {
            $users = $userTable->fetchAll();
        } catch (\Exception $e) {
            $this->getLogger()->err($e);
            $users = [];
        }
        return $this->prepareResponse($request, [
            'title' => $this->trans('Maketok Admin - Users management'),
            'description' => $this->trans('Users'),
            'users' => $users,
        ]);
    }

    /**
     * @param Request $request
     * @return \Maketok\Authentication\Resource\Model\User
     * @throws RouteException
     */
    protected function initUser(Request $request, TableMapper $table)
    {
        $id = $request->getAttributes()->get('id');
        if ($id === null) {
            // route exception will lead to 404
            throw new RouteException("Can not process user without id.");
        }
        try {
            return $table->find($id);
        } catch (ModelException $e) {
            throw new RouteException("Could not find user by id.");
        }
    }
}
