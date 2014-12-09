<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */
use Maketok\Mvc\Router\RouterInterface;

return [
    'subject_config' => [
        'dispatch' => [
            [
                'subscriber' => function() {
                    /** @var RouterInterface $router */
                    $router = \Maketok\App\Site::getServiceContainer()->get('router');
                    // index
                    $router->addRoute( new \Maketok\Mvc\Router\Route\Http\Literal(
                        '/',  array(
                            'module' => 'admin_manager',
                            'controller' => 'admin\\controller\\Index',
                            'action' => 'index',
                        )
                    ));
                    // install
                    $router->addRoute( new \Maketok\Mvc\Router\Route\Http\Literal(
                        '/install/run',  array(
                            'module' => 'admin_manager',
                            'controller' => 'admin\\controller\\Install',
                            'action' => 'run',
                        )
                    ));
                    $router->addRoute( new \Maketok\Mvc\Router\Route\Http\Literal(
                        '/install',  array(
                            'module' => 'admin_manager',
                            'controller' => 'admin\\controller\\Install',
                            'action' => 'index',
                        )
                    ));
                    $router->addRoute( new \Maketok\Mvc\Router\Route\Http\Literal(
                        '/modules',  array(
                            'module' => 'admin_manager',
                            'controller' => 'Maketok\Module\Resource\controller\admin\Modules',
                            'action' => 'index',
                        )
                    ));
                    $router->addRoute( new \Maketok\Mvc\Router\Route\Http\Parameterized(
                        '/modules/{area}/{module_code}',  array(
                            'module' => 'admin_manager',
                            'controller' => 'Maketok\Module\Resource\controller\admin\Modules',
                            'action' => 'view',
                        ), [], []
                    ));
                },
                'type' => 'closure',
                'priority' => 100
            ]
        ]
    ]
];
