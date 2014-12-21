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

class PathRule implements RuleInterface
{

    /**
     * @var array
     */
    private $blacklist = [];

    /**
     * {@inheritdoc}
     */
    public function addBlacklist($role, $condition)
    {
        if (!is_array($condition)) {
            $condition = [$condition];
        }
        if (!isset($this->blacklist[$role])) {
            $this->blacklist[$role] = [];
        }
        $this->blacklist[$role] = array_merge_recursive($this->blacklist[$role], $condition);

        return $this;
    }

    /**
     * @return array
     */
    public function getBlacklist()
    {
        return $this->blacklist;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($role, Request $request)
    {
        if (isset($this->blacklist[$role]) && is_array($this->blacklist[$role])) {
            foreach ($this->blacklist[$role] as $condition) {
                $res = preg_match("#$condition#", $request->getPathInfo());
                if ($res === 1) {
                    return false;
                }
            }
        } else {
            throw new FirewallException("No lists found for the role.");
        }
        return true;
    }
}
