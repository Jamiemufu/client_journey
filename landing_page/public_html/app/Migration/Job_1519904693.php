<?php


namespace Whiskey\Bourbon\App\Migration;


use Whiskey\Bourbon\App\Facade\Db;
use Itg\Buildr\Facade\User;

/**
 * Job_1519904693 migration class
 * @package Whiskey\Bourbon\App\Migration
 */
class Job_1519904693 extends Job
{


    /**
     * Description of the migration's purpose
     * @var string
     */
    public $description = 'Add client user';


    /**
     * Apply the migration
     */
    public function up() {
        $user = User::create('dunelm');

        $user->setRole(1);
        $user->updatePassword('NXz4D674amNCmYpU');
    }


    /**
     * Undo the migration
     */
    public function down() {

    }


}