<?php


namespace Whiskey\Bourbon\App\Migration;


use Whiskey\Bourbon\App\Facade\Db;


/**
 * Job_1497271056 migration class
 * @package Whiskey\Bourbon\App\Migration
 */
class Job_1497271056 extends Job
{


    /**
     * Description of the migration's purpose
     * @var string
     */
    public $description = 'Create non_attendance table';


    /**
     * Apply the migration
     */
    public function up() {
        /*
         * Set up the 'non_attendance' table
         */
        Db::buildSchema()->table('nonattendance')
                         ->autoId()
                         ->varChar('email', '')
                         ->varChar('name', '')
                         ->varChar('attending', 'N')
                         ->timestamp()
                         ->create();
    }


    /**
     * Undo the migration
     */
    public function down() {

        Db::drop('nonattendance');
    }


}