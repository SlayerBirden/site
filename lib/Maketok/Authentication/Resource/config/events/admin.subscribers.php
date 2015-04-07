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
    'firewall_user_forbidden' => [
        'attach' => [
            [
                new \Maketok\Observer\SubscriberBag(
                    'auth_controller_resolve',
                    function ($request, $roleProvider, $subject) use ($ioc) {
                        $ioc->get('auth_controller')->resolve($request, $roleProvider, $subject);
                    }
                ),
                99
            ]
        ]
    ]
];
