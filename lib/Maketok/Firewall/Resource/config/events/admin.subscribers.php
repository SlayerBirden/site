<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Maketok\Firewall\AccessDeniedException;
use Maketok\Http\Response;

$iocFactory = \Maketok\App\ContainerFactory::getInstance();
$ioc = $iocFactory->getServiceContainer();

return [
    'front_before_process' => [
        'attach' => [
            [function ($request) use ($ioc) {
                return $ioc->get('firewall')->validate($request);
            }, 99],
        ]
    ],
    'firewall_user_forbidden' => [
        'attach' => [
            [['default_firewall_handle' => function () {
                throw new AccessDeniedException("Access denied for current entity.", Response::HTTP_FORBIDDEN);
            }], -100],
        ]
    ]
];
