<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Authentication;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Firewall\AuthorizationInterface;
use Maketok\Firewall\RoleProviderInterface;
use Maketok\Http\Request;
use Maketok\Observer\State;

class Authentication implements IdentityManagerInterface, RoleProviderInterface
{
    use UtilityHelperTrait;

    /**
     * @var IdentityProviderInterface
     */
    protected $provider;

    /**
     * @var IdentityInterface
     */
    protected $currentIdentity;

    /**
     * {@inheritdoc}
     * @throws AuthException
     */
    public function authenticate(Request $request)
    {
        $provider = $this->getProvider();
        if (empty($provider)) {
            throw new AuthException("Can not authenticate. No identity providers are set.");
        }
        // request identity
        $identity = $this->provider->provide($request);
        if ($identity) {
            $this->getDispatcher()->notify('auth_attempt_success', new State(['user' => $identity]));
            $this->setCurrentIdentity($identity);
        } else {
            $this->getDispatcher()->notify('auth_attempt_failure', new State(['user' => $identity]));
        }
    }

    /**
     * persistent storage, use session
     * @return \Maketok\Http\Session
     */
    public function getStorage()
    {
        return $this->ioc()->get('session_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentIdentity()
    {
        if (!$this->hasCurrentIdentity()) {
            $provider = $this->getProvider();
            if ($provider && !$provider->isStateless()) {
                $identity = $this->getStorage()->get('current_identity');
                if ($identity) {
                    $this->setCurrentIdentity($identity);
                }
            }
        }
        return $this->currentIdentity;
    }

    /**
     * {@inheritdoc}
     */
    public function logout()
    {
        $this->currentIdentity = null;
        $provider = $this->getProvider();
        if ($provider && !$provider->isStateless()) {
            $this->getStorage()->remove('current_identity');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setProvider(IdentityProviderInterface $provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetProvider()
    {
        $this->provider = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentRoles(Request $request)
    {
        if ($this->getCurrentIdentity()) {
            return $this->getCurrentIdentity()->getRoles();
        } else {
            return [AuthorizationInterface::ROLE_GUEST];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentIdentity(IdentityInterface $identity)
    {
        $this->getStorage()->set('current_identity', $identity);
        $this->currentIdentity = $identity;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCurrentIdentity()
    {
        return !is_null($this->currentIdentity);
    }
}
