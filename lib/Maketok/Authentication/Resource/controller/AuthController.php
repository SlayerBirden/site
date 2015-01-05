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

use Maketok\Authentication\IdentityManagerInterface;
use Maketok\Firewall\RoleProviderInterface;
use Maketok\Http\Request;
use Maketok\Http\Response;
use Maketok\Mvc\Controller\AbstractBaseController;
use Maketok\Mvc\FlowException;
use Maketok\Observer\SubjectInterface;

class AuthController extends AbstractBaseController
{
    /**
     * @var IdentityManagerInterface
     */
    private $auth;

    /**
     * @param IdentityManagerInterface $auth
     */
    public function __construct(IdentityManagerInterface $auth)
    {
        $this->auth = $auth;
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
        $form = $this->getFormFactory()->create('login');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->getAuth()->authenticate($request);
            return $this->returnBack();
        }
        return new Response('show form', Response::HTTP_UNAUTHORIZED);
    }
}
