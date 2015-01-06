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
use Maketok\Authentication\IdentityManagerInterface;
use Maketok\Authentication\Provider\DataBaseProvider;
use Maketok\Authentication\Resource\Model\User;
use Maketok\Authentication\Resource\Model\UserTable;
use Maketok\Firewall\RoleProviderInterface;
use Maketok\Http\Request;
use Maketok\Http\Response;
use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Mvc\FlowException;
use Maketok\Mvc\RouteException;
use Maketok\Mvc\Router\Route\Http\Literal;
use Maketok\Observer\SubjectInterface;
use Maketok\Util\Exception\ModelException;
use Maketok\Util\Exception\ModelInfoException;
use Symfony\Component\Form\FormInterface;

class AuthController extends AbstractAdminController
{
    /**
     * @var IdentityManagerInterface
     */
    private $auth;

    /**
     * @param IdentityManagerInterface $auth
     */
    public function __construct(IdentityManagerInterface $auth = null)
    {
        parent::__construct();
        if ($auth) {
            $this->auth = $auth;
        } else {
            $this->auth = $this->ioc()->get('auth');
        }
    }

    /**
     * @return IdentityManagerInterface
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @param IdentityManagerInterface $auth
     */
    public function setAuth($auth)
    {
        $this->auth = $auth;
    }

    /**
     * @param Request $request
     * @param RoleProviderInterface $roleProvider
     * @param SubjectInterface $subject
     * @throws FlowException
     */
    public function resolve(Request $request, RoleProviderInterface $roleProvider, SubjectInterface $subject)
    {
        if ($this->getAuth()->hasCurrentIdentity()) {
            // well it means we have identity which doesn't suite current firewall rules
            return;
        }
        if ($roleProvider != $this->getAuth()) {
            // we may need different approach in the future
            throw new \LogicException("Invalid Role Provider to work with.");
        }
        $subject->setShouldStopPropagation(true);
        if ($this->getAuth()->getProvider()->isStateless()) {
            // login upon each request
            // let provider determine the strategy
            // TODO add stateless provider/auth
        } else {
            $response = $this->loginAction($request);
            $this->ioc()->get('front_controller')->sendResponse($response, true);
            throw new FlowException('Response already sent');
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {
        // protection from direct access
        if ($this->getAuth()->hasCurrentIdentity()) {
            return $this->redirect('/');
        }
        $this->setTemplate('login.html.twig');
        $form = $this->getFormFactory()->create('login');
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $this->getAuth()->authenticate($request);
            } catch (AuthException $e) {
                $this->addSessionMessage('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->getLogger()->err($e);
                $this->addSessionMessage('error', 'Error while logging in.');
            }
            return $this->returnBack();
        }
        $request->getAttributes()->add(['_route' => new Literal('/login', [$this, 'loginAction'])]);
        return $this->prepareResponse($request, [
            'title' => 'Maketok Admin - Log In',
            'description' => 'Log In form',
            'form' => $form->createView()
        ], null, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $this->setTemplate('create.html.twig');
        $form = $this->getFormFactory()->create('create_admin', new User());
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
        $password = $user->getPassword();
        $confirm = $user->getConfirm();
        if ($password !== $confirm) {
            throw new AuthException("Password doesn't match the confirmation.");
        }
        $provider = $this->getAuth()->getProvider();
        if ($provider instanceof DataBaseProvider) {
            $user->password_hash = $provider->getEncoder()->encodePassword($password, false);
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws RouteException
     */
    public function editAction(Request $request)
    {
        $this->setTemplate('create.html.twig');
        $user = $this->initUser($request);
        $form = $this->getFormFactory()->create('create_admin', $user);
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
