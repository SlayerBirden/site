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
use Maketok\Observer\State;
use Maketok\Observer\SubjectManagerInterface;
use Maketok\Util\ConfigConsumerInterface;

class Authorization implements AuthorizationInterface, ConfigConsumerInterface
{
    use UtilityHelperTrait {
        getDispatcher as iocGetDispatcher;
    }

    /**
     * @var RuleInterface[]
     */
    protected $rules = [];
    /**
     * @var RoleProviderInterface
     */
    protected $roleProvider;
    /**
     * @var SubjectManagerInterface
     */
    protected $dispatcher;

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
        if (empty($this->rules)) {
            return;
        }
        $roleProvider = $this->getRoleProvider();
        if (is_null($roleProvider)) {
            $roles = [0];
        } else {
            $roles = $roleProvider->getCurrentRoles($request);
        }
        // start from latest role in stack
        while ($roles && ($role = array_pop($roles)) !== null) {
            foreach ($this->getRules() as $rule) {
                if ($rule->isGranted($role, $request)) {
                    return;
                }
            }
        }
        // the flow may be altered here
        $this->getDispatcher()->notify(
            'firewall_user_forbidden',
            new State(['request' => $request, 'role_provider' => $roleProvider])
        );
    }

    /**
     * @return \Maketok\Observer\SubjectManagerInterface
     */
    public function getDispatcher()
    {
        if (isset($this->dispatcher)) {
            return $this->dispatcher;
        }
        return $this->iocGetDispatcher();
    }

    /**
     * @param SubjectManagerInterface $dispatcher
     */
    public function setDispatcher(SubjectManagerInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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
            'rules',
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
            foreach (['blacklist', 'whitelist'] as $type) {
                $blacklist = $this->getIfExists($type, $configType, []);
                foreach ($blacklist as $ruleClass => $rules) {
                    if (!class_exists($ruleClass)) {
                        throw new FirewallException(sprintf(
                            "Tried to create non existent rule with class '%s'.", $ruleClass
                        ));
                    }
                    /** @var RuleInterface $rule */
                    $this->addRule(new $ruleClass($type, $role, $rules));
                }
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

    /**
     * {@inheritdoc}
     */
    public function addWhitelist($role, $condition)
    {
        throw new \LogicException("Please add whitelist via 'addRule' method.");
    }
}
