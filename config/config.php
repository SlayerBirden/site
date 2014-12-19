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
    'debug' => 0,
    'ioc_extension_path' => [
        AR . '/lib/Maketok/Installer/Ddl/Resource/config/di',
        AR . '/lib/Maketok/Module/Resource/config/di',
        AR . '/lib/Maketok/Http/Session/Resource/config/di',
        AR . '/lib/Maketok/Template/Resource/config/di',
        AR . '/lib/Maketok/Mvc/Resource/config/di',
        AR . '/lib/Maketok/Observer/Resource/config/di'
    ],
    'iod_compiler_pass' => [
        new \Maketok\Template\TemplateCompilerPass(),
        new \Maketok\Template\Symfony\Form\FormExtensionCompilerPass,
        new \Maketok\Template\Symfony\Form\FormTypeCompilerPass
    ],
    'routing_provider_path' => [],
    'modules_dir' => AR . '/modules'
];
