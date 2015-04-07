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
    'topmenu' => [
        'installer' => [
            'order' => 10,
            'title' => function () use ($ioc) {return $ioc->get('translator')->trans('Installer');},
            'href'  => '/install',
            'children' => [
                'ddl' => [
                    'href' => '/install/ddl',
                    'order' => 0,
                    'title' => function () use ($ioc) {return $ioc->get('translator')->trans('Ddl');},
                ]
            ]
        ]
    ]
];
