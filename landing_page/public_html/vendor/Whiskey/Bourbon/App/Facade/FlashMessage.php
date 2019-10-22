<?php


namespace Whiskey\Bourbon\App\Facade;


use Whiskey\Bourbon\Instance;


/**
 * FlashMessage façade class
 * @package Whiskey\Bourbon\App\Facade
 */
class FlashMessage extends Instance
{


    /**
     * Get the façade target class
     * @return string Façade target class
     */
    protected static function _getTarget()
    {

        return \Whiskey\Bourbon\Html\FlashMessage::class;

    }


}