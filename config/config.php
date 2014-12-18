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
    'ioc_extension' => [
        new \Maketok\Installer\Ddl\DI(),
        new \Maketok\Module\DI(),
        new \Maketok\Http\Session\DI(),
        new \Maketok\Template\DI(),
        new \Maketok\Mvc\DI(),
        new \Maketok\Observer\DI()
    ],
    'iod_compiler_pass' => [
        new \Maketok\Template\TemplateCompilerPass(),
        new \Maketok\Template\Symfony\Form\FormExtensionCompilerPass,
        new \Maketok\Template\Symfony\Form\FormTypeCompilerPass
    ],
    'routing_provider' => []
];
