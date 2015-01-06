<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Authentication\Provider;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Authentication\AuthException;
use Maketok\Authentication\IdentityProviderInterface;
use Maketok\Authentication\Resource\Model\UserTable;
use Maketok\Http\Request;
use Maketok\Installer\Ddl\ClientInterface;
use Maketok\Model\TableMapper;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class DataBaseProvider implements IdentityProviderInterface, ClientInterface
{
    use UtilityHelperTrait;

    /**
     * @var UserTable
     */
    private $pwTable;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface
     */
    private $encoder;

    /**
     * @param TableMapper $pwTable
     */
    public function __construct(TableMapper $pwTable)
    {
        $this->pwTable = $pwTable;
    }

    /**
     * {@inheritdoc}
     * @throws AuthException
     */
    public function provide(Request $request)
    {
        $loginData = $request->request->get('login', []);
        $username = $this->getIfExists('username', $loginData);
        $password = $this->getIfExists('password', $loginData);
        if (is_null($username) || is_null($password)) {
            throw new AuthException("Username or password is not set.");
        }
        $user = $this->pwTable->getUserByUsername($username);
        if (!$user || !$this->getEncoder()->isPasswordValid($user->password_hash, $password, false)) {
            throw new AuthException("Invalid username or password.");
        }
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function isStateless()
    {
        return false;
    }

    /**
     * @return \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * @param PasswordEncoderInterface $encoder
     */
    public function setEncoder(PasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getDdlVersion()
    {
        return '0.1.0';
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getDdlConfig($version)
    {
        return current($this->ioc()->get('config_getter')->getConfig(dirname(__DIR__) . "/Resource/config/installer/ddl", $version));
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getDdlCode()
    {
        return 'auth_provider_db';
    }
}
