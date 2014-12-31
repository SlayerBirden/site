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

class PathRule extends  AbstractRule
{
    /**
     * return true if access should be granted
     * @param Request $request
     * @param array $conditions
     * @return bool
     */
    protected function getSpecialConditionBlack(Request $request, array $conditions)
    {
        foreach ($conditions as $condition) {
            $res = preg_match("#$condition#", $request->getPathInfo());
            if ($res === 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * return true if access should be granted
     * @param Request $request
     * @param array $conditions
     * @return bool
     */
    protected function getSpecialConditionWhite(Request $request, array $conditions)
    {
        foreach ($conditions as $condition) {
            $res = preg_match("#$condition#", $request->getPathInfo());
            if ($res === 1) {
                return true;
            }
        }
        return false;
    }
}
