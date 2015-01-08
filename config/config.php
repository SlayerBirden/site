<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'debug' => 0,
    'ioc_extension_path' => [
        AR . '/lib/Maketok/Installer/Ddl/Resource/config/di',
        AR . '/lib/Maketok/Module/Resource/config/di',
        AR . '/lib/Maketok/Http/Session/Resource/config/di',
        AR . '/lib/Maketok/Template/Resource/config/di',
        AR . '/lib/Maketok/Mvc/Resource/config/di',
        AR . '/lib/Maketok/Observer/Resource/config/di',
        AR . '/lib/Maketok/Navigation/Resource/config/di',
        AR . '/lib/Maketok/Firewall/Resource/config/di',
        AR . '/lib/Maketok/Authentication/Resource/config/di',
        AR . '/lib/Maketok/Model/Resource/config/di',
    ],
    'ioc_compiler_pass' => [
        new \Maketok\Template\TemplateCompilerPass(),
        new \Maketok\Template\Symfony\Form\FormExtensionCompilerPass(),
        new \Maketok\Template\Symfony\Form\FormTypeCompilerPass(),
        new \Maketok\Template\Symfony\Form\ValidationBuilderCompilerPass(),
        new \Maketok\Template\Symfony\Form\AddConstraintValidatorPass(),
    ],
    'routing_provider_path' => [
        AR . '/config/routes',
        AR . '/lib/Maketok/Module/Resource/config/routes',
        AR . '/lib/Maketok/Installer/Ddl/Resource/config/routes',
        AR . '/lib/Maketok/Authentication/Resource/config/routes',
    ],
    'modules_dir' => AR . '/modules',
    'template_path' => [
        AR . '/lib/Maketok/Template/Resource/view/Form',
        AR . '/vendor/symfony/twig-bridge/Symfony/Bridge/Twig/Resources/views/Form',
        AR . '/lib/Maketok/Navigation/Resource/view',
    ],
    'subscribers_config_path' => [
        AR . '/lib/Maketok/App/Resource/config/events',
        AR . '/lib/Maketok/Module/Resource/config/events',
        AR . '/lib/Maketok/Mvc/Resource/config/events',
        AR . '/lib/Maketok/Firewall/Resource/config/events',
        AR . '/lib/Maketok/Authentication/Resource/config/events',
    ],
    'navigation_config_path' => [
        AR . '/config/navigation',
        AR . '/lib/Maketok/Module/Resource/config/navigation',
        AR . '/lib/Maketok/Installer/Ddl/Resource/config/navigation',
        AR . '/lib/Maketok/Authentication/Resource/config/navigation',
    ],
    'firewall_config_path' => [
        AR . '/config/firewall',
    ],
    'installer_ddl_clients' => [
        'Maketok\Installer\Ddl\InstallerClient',
        '@session_save_handler',
        '@module_manager',
        '@db_auth_provider',
    ],
    'validation_yaml_mapping_path' => [
        AR . '/lib/Maketok/Authentication/Resource/config/validation/validation.yml',
    ]
];
