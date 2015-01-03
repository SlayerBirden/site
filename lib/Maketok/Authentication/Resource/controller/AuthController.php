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

use Maketok\Authentication\AuthenticationManagerInterface;
use Maketok\Http\Request;

class AuthController
{
    /**
     * @var AuthenticationManagerInterface
     */
    private $auth;

    /**
     * @param AuthenticationManagerInterface $auth
     */
    public function __construct(AuthenticationManagerInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return AuthenticationManagerInterface
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @param AuthenticationManagerInterface $auth
     */
    public function setAuth($auth)
    {
        $this->auth = $auth;
    }

    /**
     * @param Request $request
     */
    public function resolve(Request $request)
    {
        #test code here
        echo '123';
        die;
    }
}
