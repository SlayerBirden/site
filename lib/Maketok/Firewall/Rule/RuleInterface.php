<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Firewall\Rule;

use Maketok\Http\Request;

interface RuleInterface
{
    /**
     * @param int $role
     * @param mixed $condition
     * @return mixed
     */
    public function addBlacklist($role, $condition);

    /**
     * @param int $role
     * @param mixed $condition
     * @return mixed
     */
    public function addWhitelist($role, $condition);

    /**
     * @param int $role
     * @param Request $request
     * @return bool
     */
    public function isGranted($role, Request $request);
}
