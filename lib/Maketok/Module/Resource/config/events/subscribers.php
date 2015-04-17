<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$iocFactory = \Maketok\App\Site::getContainerFactory();
$ioc = $iocFactory->getServiceContainer();

return [
    'ioc_container_initialized' => [
        'attach' => [
            [['modules_process_config' => function () use ($ioc) {
                return $ioc->get('module_manager')->processModuleConfig();
            }], 15],
        ]
    ],
    'ioc_container_compiled' => [
        'attach' => [
            [['modules_update_modules' => function () use ($ioc) {
                return $ioc->get('module_manager')->updateModules();
            }], 20],
            [['modules_process_modules' => function () use ($ioc) {
                return $ioc->get('module_manager')->processModules();
            }], 15],
        ]
    ],
    'installer_before_process' => [
        'attach' => [
            [['modules_add_to_installer' => function () use ($ioc) {
                return $ioc->get('module_manager')->addInstallerSubscribers();
            }], 0]
        ]
    ],
    'software_clients_getter_create' => [
        'attach' => [
            [['modules_software_add_to_installer' => function () use ($ioc) {
                return $ioc->get('module_manager')->addInstallerSoftware();
            }], 0]
        ]
    ]
];
