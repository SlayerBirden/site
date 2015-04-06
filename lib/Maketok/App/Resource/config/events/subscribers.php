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
            [[$iocFactory, 'scCompile'], 10],
            [[$iocFactory, 'scDump'], 5]
        ]
    ],
    'module_list_exists' => [
        'attach' => [
            [[$iocFactory, 'serviceContainerProcessModules'], 0]
        ]
    ],
    'response_send_before' => [
        'attach' => [
            [function () use ($ioc) {
                return $ioc->get('site')->terminate();
            }, 0]
        ]
    ],
    'noroute_action' => [
        'attach' => [
            [function () use ($ioc) {
                return $ioc->get('site')->terminate();
            }, 0]
        ]
    ]
];
