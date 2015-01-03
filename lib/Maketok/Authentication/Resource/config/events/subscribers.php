<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$subject = new \Maketok\Observer\Subject('firewall_user_forbidden');
$subject->setShouldStopPropagation(true);

$iocFactory = \Maketok\App\ContainerFactory::getInstance();
$ioc = $iocFactory->getServiceContainer();

return [
    $subject->__toString() => [
        'attach' => [
            [
                new \Maketok\Observer\SubscriberBag(
                    'auth_controller_resolve',
                    [$ioc->get('auth_controller'), 'resolve'],
                    $subject
                ),
                99
            ]
        ]
    ]
];
