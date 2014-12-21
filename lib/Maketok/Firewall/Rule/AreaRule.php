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

class AreaRule implements RuleInterface
{

    /**
     * {@inheritdoc}
     */
    public function addBlacklist($role, $condition)
    {
        // TODO: Implement addBlacklist() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($role, Request $request)
    {
        // TODO: Implement isGranted() method.
    }
}
