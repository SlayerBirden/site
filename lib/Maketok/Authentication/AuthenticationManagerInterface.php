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

use Maketok\Http\Request;

interface AuthenticationManagerInterface
{
    /**
     * Main auth method. Manager will work with identity providers
     * to get/authenticate current entity
     *
     * @param Request $request
     * @return void
     */
    public function authenticate(Request $request);
}
