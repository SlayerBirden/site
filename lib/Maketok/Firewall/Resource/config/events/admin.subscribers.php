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
    'front_before_process' => [
        'attach' => [
            [[$ioc->get('firewall'), 'validate'], 99],
        ]
    ]
];
