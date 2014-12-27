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

use Maketok\App\Helper\ContainerTrait;
use Maketok\Http\Request;

class AreaRule extends  AbstractRule
{
    use ContainerTrait;

    /**
     * return true if access should be granted
     * @param Request $request
     * @param array $conditions
     * @return bool
     */
    protected function getSpecialConditionBlack(Request $request, array $conditions)
    {
        return !in_array($request->getArea(), $conditions);
    }

    /**
     * return true if access should be granted
     * @param Request $request
     * @param array $conditions
     * @return bool
     */
    protected function getSpecialConditionWhite(Request $request, array $conditions)
    {
        return in_array($request->getArea(), $conditions);
    }
}
