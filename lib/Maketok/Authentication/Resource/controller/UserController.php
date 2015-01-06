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
use Maketok\Authentication\Resource\Model\User;
use Maketok\Authentication\Resource\Model\UserTable;
use Maketok\Http\Request;
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
        $form = $this->getFormFactory()->create('create_user', new User());
        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->handleUser($form);
        }
        return $this->prepareResponse($request, [
            'title' => 'Maketok Admin - Create New User',
            'description' => 'User Creation',
            'form' => $form->createView(),
        ]);
    }

    /**
     * handle form request
     * @param FormInterface $form
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function handleUser(FormInterface $form)
    {
        /** @var UserTable $userTable */
        $userTable = $this->ioc()->get('auth_user_table');
        try {
            $data = $form->getData();
            $this->validateUser($data);
            $userTable->save($data);
            $this->getSession()->getFlashBag()->add('success', 'The user was saved successfully!');
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
        return $this->redirect('auth/users/');
    }

    /**
     * @param User $user
     * @throws AuthException
     */
    protected function validateUser(User $user)
    {
        // validation of password vs confirmation is happening in form validation extension
        $provider = $this->ioc()->get('auth')->getProvider();
        if ($provider instanceof DataBaseProvider) {
            $user->password_hash = $provider->getEncoder()->encodePassword($user->getPassword(), false);
        } else {
            $user->password_hash = $user->getPassword();
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws RouteException
     */
    public function editAction(Request $request)
    {
        $this->setTemplate('user.create.html.twig');
        $user = $this->initUser($request);
        $form = $this->getFormFactory()->create('create_user', $user);
        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->handleUser($form);
        }
        return $this->prepareResponse($request, [
            'title' => 'Maketok Admin - Edit User ' . $user->firstname,
            'description' => 'User ' . $user->firstname,
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
        $user = $this->initUser($request);
        /** @var UserTable $userTable */
        $userTable = $this->ioc()->get('auth_user_table');
        try {
            $userTable->delete($user);
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
            'title' => 'Maketok Admin - Users management',
            'description' => 'Users',
            'users' => $users,
        ]);
    }

    /**
     * @param Request $request
     * @return \Maketok\Authentication\Resource\Model\User
     * @throws RouteException
     */
    protected function initUser(Request $request)
    {
        $id = $request->getAttributes()->get('id');
        if ($id === null) {
            // route exception will lead to 404
            throw new RouteException("Can not process user without id.");
        }
        /** @var UserTable $articleTable */
        $articleTable = $this->ioc()->get('auth_user_table');
        try {
            return $articleTable->find($id);
        } catch (ModelException $e) {
            throw new RouteException("Could not find user by id.");
        }
    }
}
