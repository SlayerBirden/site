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

abstract class AbstractRule implements RuleInterface
{
    /**
     * @var array
     */
    protected $blacklist = [];
    /**
     * @var array
     */
    protected $whitelist = [];

    /**
     * @param string $type
     * @param int $role
     * @param mixed $condition
     * @throws FirewallException
     */
    public function __construct($type = null, $role = null, $condition = null)
    {
        if (!is_null($type) && !is_null($role) && !is_null($condition)) {
            $this->addList($type, $role, $condition);
        }
    }

    /**
     * @param string $type
     * @param int $role
     * @param mixed $condition
     * @throws FirewallException
     */
    public function addList($type, $role, $condition)
    {
        if (!property_exists($this, $type)) {
            throw new FirewallException(sprintf("Undefined type %s of list for rule.". $type));
        }
        if (!is_array($condition)) {
            $condition = [$condition];
        }
        if (!isset($this->{$type}[$role])) {
            $this->{$type}[$role] = [];
        }
        $this->{$type}[$role] = array_merge_recursive($this->{$type}[$role], $condition);
    }

    /**
     * {@inheritdoc}
     */
    public function addBlacklist($role, $condition)
    {
        $this->addList('blacklist', $role, $condition);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addWhitelist($role, $condition)
    {
        $this->addList('whitelist', $role, $condition);
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
     * @return array
     */
    public function getWhitelist()
    {
        return $this->whitelist;
    }

    /**
     * return true if access should be granted
     * @param Request $request
     * @param array $conditions
     * @return bool
     */
    abstract protected function getSpecialConditionBlack(Request $request, array $conditions);

    /**
     * return true if access should be granted
     * @param Request $request
     * @param array $conditions
     * @return bool
     */
    abstract protected function getSpecialConditionWhite(Request $request, array $conditions);

    /**
     * {@inheritdoc}
     */
    public function isGranted($role, Request $request)
    {
        if (empty($this->blacklist) && empty($this->whitelist)) {
            throw new FirewallException("No lists found.");
        }
        if (isset($this->whitelist[$role]) && is_array($this->whitelist[$role])) {
            if (!$this->getSpecialConditionWhite($request, $this->whitelist[$role])) {
                return false;
            }
        } elseif (!empty($this->whitelist)) {
            return false;
        }
        if (isset($this->blacklist[$role]) && is_array($this->blacklist[$role])) {
            if (!$this->getSpecialConditionBlack($request, $this->blacklist[$role])) {
                return false;
            }
        }
        return true;
    }
}
