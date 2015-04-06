<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
//    Maketok\Firewall\AuthorizationInterface::ROLE_GUEST => [
//        'whitelist' => [
//            'Maketok\Firewall\Rule\IpRule' => ['127.0.0.1']
//        ],
//    ],
    Maketok\Firewall\AuthorizationInterface::ROLE_ADMINISTRATOR => [
        'whitelist' => [
            'Maketok\Firewall\Rule\AreaRule' => ['admin']
        ],
    ]
];
