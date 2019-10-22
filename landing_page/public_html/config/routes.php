<?php


namespace Whiskey\Bourbon\Config;


use Whiskey\Bourbon\Config\Type\Routes;
use Whiskey\Bourbon\App\Http\Middleware\AccessControlList;
use Whiskey\Bourbon\App\Http\Middleware\Authentication;
use Whiskey\Bourbon\App\Http\Middleware\Csrf;
use Whiskey\Bourbon\App\Http\Middleware\Https;
use Whiskey\Bourbon\App\Http\Middleware\IpWhitelist;
use Whiskey\Bourbon\App\Http\Middleware\RateLimit;
use Whiskey\Bourbon\App\Http\Model\PageModel;
use Whiskey\Bourbon\App\Http\Controller\PageController;


$routes = new Routes();


/*
 * Regular expression route tags
 */
$routes->addRegex('{alpha}',    '[a-zA-Z]+');
$routes->addRegex('{num}',      '[0-9]+');
$routes->addRegex('{alphanum}', '[a-zA-Z0-9]+');


$routes->addGlobalMiddleware([Csrf::class]);


/*
 * Routes
 */
$routes->set('/',
    [
        'controller' => PageController::class,
        'model'      => PageModel::class,
        'action'     => 'home'
    ]);

$routes->set('/attending/',
    [
        'controller' => PageController::class,
        'model'      => PageModel::class,
        'action'     => 'attending'
    ]);


$routes->set('/not_attending/',
    [
        'controller' => PageController::class,
        'model'      => PageModel::class,
        'action'     => 'not_attending'
    ]);

$routes->set('/submit/',
    [
        'controller'  => PageController::class,
        'model'       => PageModel::class,
        'action'      => 'submit',
        'http_method' => 'POST'
    ]);
    
$routes->set('/error/',
[
    'controller' => PageController::class,
    'model'      => PageModel::class,
    'action'     => 'error'
]);

$routes->set('/thanks/',
[
    'controller' => PageController::class,
    'model'      => PageModel::class,
    'action'     => 'thanks'
]);


return $routes;