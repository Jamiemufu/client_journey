<?php


namespace Whiskey\Bourbon\App\Facade;


use Whiskey\Bourbon\Instance;


/**
 * AppEnv façade class
 * @package Whiskey\Bourbon\App\Facade
 */
class AppEnv extends Instance
{


    /**
     * Get the façade target class
     * @return string Façade target class
     */
    protected static function _getTarget()
    {

        return \Whiskey\Bourbon\App\AppEnv::class;

    }


}