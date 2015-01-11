<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\Maketok\Authentication;

use Maketok\App\Helper\ContainerTrait;
use Maketok\Authentication\Authentication;
use Maketok\Authentication\Resource\Model\User;
use Maketok\Http\Request;

class AuthenticationTest extends \PHPUnit_Framework_TestCase
{
    use ContainerTrait;

    /**
     * @test
     */
    public function authenticate()
    {
        $user = new User();
        $user->username = 'test';
        $request = Request::create('test', 'POST', ['username' => 'test', 'password' => 'test']);
        $provider = $this->getMock('Maketok\Authentication\Provider\DataBaseProvider', ['provide'], [], '', false);
        $provider->expects($this->once())->method('provide')->with($this->equalTo($request))->willReturn($user);

        $auth = new Authentication();
        $auth->setProvider($provider);
        $auth->authenticate($request);

        $this->assertEquals($user, $auth->getCurrentIdentity());
    }

    /**
     * @test
     */
    public function getCurrentIdentity()
    {
        $auth = new Authentication();
        $user = new User();
        $user->username = 'test';
        $auth->setCurrentIdentity($user);

        $this->assertSame($user, $auth->getCurrentIdentity());
    }

    /**
     * @test
     */
    public function logout()
    {
        $auth = new Authentication();
        $user = new User();
        $user->username = 'test';
        $auth->setCurrentIdentity($user);
        $auth->logout();

        $this->assertNull($auth->getCurrentIdentity());
    }

    /**
     * @test
     */
    public function setProvider()
    {
        $provider = $this->getMock('Maketok\Authentication\Provider\DataBaseProvider', [], [], '', false);
        $auth = new Authentication();
        $auth->setProvider($provider);

        $this->assertSame($provider, $auth->getProvider());
    }

    /**
     * @test
     */
    public function unsetProvider()
    {
        $provider = $this->getMock('Maketok\Authentication\Provider\DataBaseProvider', [], [], '', false);
        $auth = new Authentication();
        $auth->setProvider($provider);
        $auth->unsetProvider();

        $this->assertNull($auth->getProvider());
    }

    /**
     * @test
     */
    public function getCurrentRoles()
    {
        $request = Request::create('test');
        $provider = $this->getMock('Maketok\Authentication\Provider\DataBaseProvider', [], [], '', false);
        $auth = new Authentication();
        $auth->setProvider($provider);
        /** @var \Maketok\Http\Session $session */
        $session = $this->ioc()->get('session_manager');
        $session->clear();
        $this->assertEquals([0], $auth->getCurrentRoles($request));
        $user = new User();
        $user->username = 'test';
        $user->roles = [0, 1];
        $auth->setCurrentIdentity($user);

        $this->assertEquals([0, 1], $auth->getCurrentRoles($request));
    }
}
