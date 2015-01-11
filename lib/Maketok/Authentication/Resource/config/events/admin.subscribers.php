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
    'firewall_user_forbidden' => [
        'attach' => [
            [
                new \Maketok\Observer\SubscriberBag(
                    'auth_controller_resolve',
                    [$ioc->get('auth_controller'), 'resolve']
                ),
                99
            ]
        ]
    ]
];