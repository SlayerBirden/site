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
    'topmenu' => [
        'auth_users' => [
            'href' => '/auth/users',
            'order' => 20,
            'title' => function () use ($ioc) {return $ioc->get('translator')->trans('Users');},
        ],
        'auth_roles' => [
            'href' => '/auth/roles',
            'order' => 30,
            'title' => function () use ($ioc) {return $ioc->get('translator')->trans('User Roles');},
        ]
    ]
];
