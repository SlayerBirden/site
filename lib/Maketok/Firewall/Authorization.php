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

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\App\Site;
use Maketok\Firewall\Rule\RuleInterface;
use Maketok\Http\Request;
use Maketok\Http\Response;
use Maketok\Util\ConfigConsumerInterface;

class Authorization implements AuthorizationInterface, ConfigConsumerInterface
{
    use UtilityHelperTrait;

    /**
     * @var RuleInterface[]
     */
    protected $rules = [];
    /**
     * @var RoleProviderInterface
     */
    protected $roleProvider;

    /**
     * @param RoleProviderInterface $roleProvider
     */
    public function __construct(RoleProviderInterface $roleProvider = null)
    {
        $this->roleProvider = $roleProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * {@inheritdoc}
     */
    public function addRule(RuleInterface $rule)
    {
        $this->rules[] = $rule;
    }

    /**
     * current decision is at-least-one
     * possible modes for future: first-serve-first-set, unanimous, votes-positive
     * {@inheritdoc}
     */
    public function validate(Request $request)
    {
        $roleProvider = $this->getRoleProvider();
        if (is_null($roleProvider)) {
            $roles = [0];
        } else {
            $roles = $roleProvider->getCurrentRoles($request);
        }
        // start from latest role in stack
        while ($roles && $role = array_pop($roles)) {
            foreach ($this->getRules() as $rule) {
                if ($rule->isGranted($role, $request)) {
                    return;
                }
            }
        }
        throw new AccessDeniedException("Access denied for current entity.", Response::HTTP_FORBIDDEN);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function initConfig()
    {
        $env = $this->ioc()->get('request')->getArea();
        $configs = $this->ioc()->get('config_getter')->getConfig(
            Site::getConfig('firewall_config_path'),
            'navigation',
            $env
        );
        foreach ($configs as $config) {
            $this->parseConfig($config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parseConfig(array $config)
    {
        foreach ($config as $role => $configType) {
            $blacklist = $this->getIfExists('blacklist', $configType, []);
            foreach ($blacklist as $ruleClass => $rules) {
                if (!class_exists($ruleClass)) {
                    throw new FirewallException(sprintf(
                        "Tried to create non existent rule with class '%s'.", $ruleClass
                    ));
                }
                /** @var RuleInterface $rule */
                $this->addRule(new $ruleClass('black', $role, $rules));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addBlacklist($role, $condition)
    {
        throw new \LogicException("Please add blacklist via 'addRule' method.");
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($role, Request $request)
    {
        throw new \LogicException("Please use 'validate' method instead.");
    }

    /**
     * @return RoleProviderInterface
     */
    public function getRoleProvider()
    {
        return $this->roleProvider;
    }

    /**
     * @param RoleProviderInterface $roleProvider
     */
    public function setRoleProvider(RoleProviderInterface $roleProvider)
    {
        $this->roleProvider = $roleProvider;
    }
}
