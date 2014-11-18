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
                    $router->addRoute( new \Maketok\Mvc\Router\Route\Http\Literal(
                        '/',  array(
                            'module' => 'admin_manager',
                            'controller' => 'admin\\manager\\controller\\Index',
                            'action' => 'index',
                        )
                    ));
                },
                'type' => 'closure',
                'priority' => 100,
            ],
        ],
    ],
];