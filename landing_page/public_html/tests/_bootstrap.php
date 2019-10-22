<?php
// This is global bootstrap for autoloading


use Whiskey\Bourbon\App\Bootstrap as Bourbon;


/*
 * Define a shorthand directory separator constant
 */
define('DS', DIRECTORY_SEPARATOR);


/*
 * Figure out enough directories to include the bootstrapper
 */
$public_dir        = '/_public/';
$root_dir          = rtrim(realpath(rtrim(__DIR__, DS) . DS . '..'), DS) . DS;
$bootstrapper_path = $root_dir . 'vendor' . DS . 'Whiskey' . DS . 'Bourbon' . DS . 'App' . DS . 'Bootstrap.php';


/*
 * Include and execute the bootstrapper
 */
require_once($bootstrapper_path);


(new Bourbon($root_dir, $public_dir))->execute('../.env', false);