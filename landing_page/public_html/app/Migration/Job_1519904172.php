<?php


namespace Whiskey\Bourbon\App\Migration;


use Whiskey\Bourbon\App\Facade\Db;
use Itg\Buildr\Facade\User;

/**
 * Job_1519904172 migration class
 * @package Whiskey\Bourbon\App\Migration
 */
class Job_1519904172 extends Job
{


    /**
     * Description of the migration's purpose
     * @var string
     */
    public $description = 'Setup CMS tables';


    /**
     * Apply the migration
     */
    public function up() {

        /*
         * Set up the 'users' table
         */
        Db::buildSchema()->table('users')
                         ->autoId()
                         ->bigInt('parent_id', 0)
                         ->varChar('username', '')
                         ->varChar('email', '')
                         ->varChar('password', '')
                         ->tinyInt('role', 2)
                         ->int('last_online', 0)
                         ->create();
        
        /*
         * Add the 'admin' user
         */
        $user = User::create('admin');

        $user->setRole(0);
        $user->updatePassword('Pw9WdtkGSP');

        /*
         * Set up the 'logs' table
         */
        Db::buildSchema()->table('logs')
                         ->autoId()
                         ->bigInt('user_id', 0)
                         ->varChar('controller', '')
                         ->varChar('action', '')
                         ->varChar('slug', '')
                         ->int('date', 0)
                         ->create();


    }


    /**
     * Undo the migration
     */
    public function down() {


        Db::drop('users');
        Db::drop('logs');

    }


}