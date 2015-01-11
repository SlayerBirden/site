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
use Maketok\Firewall\RoleProviderInterface;
use Maketok\Http\Request;
use Maketok\Http\Response;
use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Mvc\FlowException;
use Maketok\Mvc\Router\Route\Http\Literal;
use Maketok\Observer\SubjectInterface;

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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logoutAction()
    {
        $this->getAuth()->logout();
        $this->addSessionMessage('success', 'You have been logged out.');
        return $this->redirect('/');
    }
}
