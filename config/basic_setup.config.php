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
    'debug' => 1,
    'ioc_extension_path' => [
        AR . '/lib/Maketok/Observer/Resource/config/di',
        AR . '/lib/Maketok/Shell/Resource/config/di',
    ],
    'ioc_compiler_pass' => [
        new \Maketok\Shell\InstallerCompilerPass(),
    ],
    'subscribers_config_path' => [
        AR . '/lib/Maketok/App/Resource/config/events',
        AR . '/lib/Maketok/Shell/Resource/config/events',
    ],
];
