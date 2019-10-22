<?php


namespace Whiskey\Bourbon\App\Migration;


use Whiskey\Bourbon\App\Facade\Db;


/**
 * Job_1453130156 migration class
 * @package Whiskey\Bourbon\App\Migration
 */
class Job_1453130156 extends Job
{


    /**
     * Description of the migration's purpose
     * @var string
     */
    public $description = 'Set up \'attendance\' table';


    /**
     * Apply the migration
     */
    public function up()
    {

        /*
         * Set up the 'users' table
         */
        Db::buildSchema()->table('attendance')
                         ->autoId()
                         // ->varChar('code', '')                         
                         ->varChar('email', '')
                         ->varChar('name', '')
                         ->varChar('attending', 'Y')
                         ->varChar('car_reg', '')
                         ->varChar('diet', '')
                         ->varChar('evening_meal')
                         ->varChar('staying_overnight')
                         ->timestamp()
                         ->create();

    }


    /**
     * Undo the migration
     */
    public function down()
    {

        Db::drop('attendance');

    }


}