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

interface IdentityManagerInterface extends AuthenticationManagerInterface
{
    /**
     * @return IdentityInterface|null
     */
    public function getCurrentIdentity();

    /**
     * @param IdentityInterface $identity
     * @return self
     */
    public function setCurrentIdentity(IdentityInterface $identity);

    /**
     * logout current identity
     * @return void
     */
    public function logout();

    /**
     * @param IdentityProviderInterface $provider
     * @return self
     */
    public function setProvider(IdentityProviderInterface $provider);

    /**
     * @return IdentityProviderInterface|null
     */
    public function getProvider();

    /**
     * @return void
     */
    public function unsetProvider();
}
