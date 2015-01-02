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

use Maketok\App\Helper\ContainerTrait;
use Maketok\Firewall\AuthorizationInterface;
use Maketok\Firewall\RoleProviderInterface;
use Maketok\Http\Request;

class Authentication implements IdentityManagerInterface, RoleProviderInterface
{
    use ContainerTrait;

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
            $this->setCurrentIdentity($identity);
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
        $provider = $this->getProvider();
        if ($provider && !$provider->isStateless()) {
            $identity = $this->getStorage()->get('current_identity');
            if ($identity) {
                $this->setCurrentIdentity($identity);
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
}
