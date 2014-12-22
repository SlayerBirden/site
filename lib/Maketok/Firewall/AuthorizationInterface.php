<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Firewall;

use Maketok\Firewall\Rule\RuleInterface;
use Maketok\Http\Request;

interface AuthorizationInterface extends RuleInterface
{

    const ROLE_GUEST = 0;

    /**
     * get list of rules
     * @return RuleInterface[]
     */
    public function getRules();

    /**
     * add rule
     * @param RuleInterface $rule
     * @return self
     */
    public function addRule(RuleInterface $rule);

    /**
     * @param Request $request
     * @return mixed
     */
    public function validate(Request $request);
}
