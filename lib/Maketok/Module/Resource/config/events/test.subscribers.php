<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$iocFactory = \Maketok\App\ContainerFactory::getInstance();
$ioc = $iocFactory->getServiceContainer();

return [
    'ioc_container_initialized' => [
        'attach' => [
            [[$ioc->get('module_manager'), 'processModuleConfig'], 15],
        ]
    ],
    'ioc_container_compiled' => [
        'detach' => [
            [$ioc->get('module_manager'), 'updateModules'],
            [$ioc->get('module_manager'), 'processModules'],
        ]
    ],
    'installer_before_process' => [
        'detach' => ['modules_add_to_installer']
    ],
    'software_clients_getter_create' => [
        'detach' => ['modules_software_add_to_installer']
    ]
];
