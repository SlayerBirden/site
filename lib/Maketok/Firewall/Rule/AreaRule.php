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

use Maketok\Firewall\FirewallException;
use Maketok\Http\Request;

class AreaRule extends  AbstractRule
{
    /**
     * {@inheritdoc}
     */
    public function isGranted($role, Request $request)
    {
        if (empty($this->blacklist)) {
            throw new FirewallException("No lists found.");
        }
        if (isset($this->blacklist[$role]) && is_array($this->blacklist[$role]) && in_array(ENV, $this->blacklist[$role])) {
            return false;
        }
        return true;
    }
}
