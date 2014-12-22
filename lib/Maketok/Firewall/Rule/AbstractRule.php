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

abstract class AbstractRule implements RuleInterface
{

    /**
     * @var array
     */
    protected $blacklist = [];

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
}
