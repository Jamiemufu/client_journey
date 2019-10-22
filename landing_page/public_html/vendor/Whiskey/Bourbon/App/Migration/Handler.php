<?php


namespace Whiskey\Bourbon\App\Migration;


use stdClass;
use Exception;
use InvalidArgumentException;
use Whiskey\Bourbon\Exception\EngineNotInitialisedException;
use Whiskey\Bourbon\Exception\Storage\Database\RecordWriteException;
use Whiskey\Bourbon\Storage\Database\Mysql\Handler as Db;


/**
 * Migration Handler class
 * @package Whiskey\Bourbon\App\Migration
 */
class Handler
{


    protected $_dependencies = null;
    protected $_db_table     = '_bourbon_migrations';
    protected $_directory    = null;


    /**
     * Instantiate the migration Handler object
     * @param Db $db Db object
     * @throws InvalidArgumentException if dependencies are not provided
     */
    public function __construct(Db $db)
    {

        if (!isset($db))
        {
            throw new InvalidArgumentException('Dependencies not provided');
        }

        $this->_dependencies     = new stdClass();
        $this->_dependencies->db = $db;

    }


    /**
     * Set the migration storage directory
     * @param  string $directory Path to migration storage directory
     * @return bool              Whether the migration storage directory was successfully set
     */
    public function setDirectory($directory = null)
    {

        $directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!is_null($directory) AND
            is_readable($directory) AND
            is_writable($directory))
        {

            $this->_directory = $directory;

            $this->_init();

            return true;

        }

        return false;

    }


    /**
     * Set up a migration table in the database
     * @return bool Whether the table was successfully set up
     */
    protected function _init()
    {

        /*
         * Fail immediately if the directory has not been set
         */
        if (is_null($this->_directory))
        {
            return false;
        }

        if (!$this->isActive())
        {

            /*
             * Directory check
             */
            if (!is_readable($this->_directory))
            {
                mkdir($this->_directory);
                file_put_contents($this->_directory . 'index.html', '');
            }

            if (!$this->isActive())
            {

                /*
                 * Database check
                 */
                $table_columns   = [];
                $table_columns[] = ['field' => 'id', 'type' => 'bigint', 'length' => 20, 'auto_increment' => true, 'primary_key' => true];
                $table_columns[] = ['field' => 'migration', 'type' => 'int', 'length' => 11, 'null' => false, 'default' => 0];
                $table_columns[] = ['field' => 'datetime', 'type' => 'int', 'length' => 11, 'null' => false, 'default' => 0];
                
                try
                {
                    $this->_dependencies->db->create($this->_db_table, $table_columns);
                }

                catch (Exception $exception) {}
                
                if (!$this->isActive())
                {

                    /*
                     * Permission check
                     */
                    if (is_readable($this->_directory))
                    {
                        chmod($this->_directory, 0777);
                    }

                }

            }

        }

        /*
         * Final check after all of the above
         */
        if ($this->isActive())
        {
            return true;
        }

        return false;

    }


    /**
     * Get all migrations
     * @return array Array of migration Job objects
     * @throws EngineNotInitialisedException if migrations are not enabled
     */
    public function getAll()
    {

        $this->_init();

        if (!$this->isActive())
        {
            throw new EngineNotInitialisedException('Migrations not enabled');
        }
        
        $migrations    = [];
        $migrations[0] = new Job_0();
        
        if (is_readable($this->_directory))
        {

            $files = scandir($this->_directory);

            foreach ($files as $value)
            {

                if (!is_dir($value) AND
                    mb_substr($value, -4) == '.php')
                {

                    /*
                     * Get just the timestamp from the filename
                     */
                    $migration_short_class_name = explode('.', $value);
                    $migration_short_class_name = array_shift($migration_short_class_name);

                    try
                    {
                        $migration_class                 = $this->getMigrationClassName($migration_short_class_name);
                        $migration                       = new $migration_class();
                        $migrations[$migration->getId()] = $migration;
                    }

                    catch (Exception $exception) {}

                }

            }

        }

        /*
         * Put into descending date order
         */
        ksort($migrations, SORT_NATURAL);

        $migrations = array_reverse($migrations, true);

        return $migrations;

    }


    /**
     * Reset the migration index
     * @return bool Whether the migration data was successfully reset
     */
    public function reset()
    {

        return $this->_dependencies->db->truncate($this->_db_table);

    }


    /**
     * Run migration(s)
     * @param  int  $migration Migration to work towards
     * @return bool            Whether all migrations were successfully run
     * @throws EngineNotInitialisedException if migrations are not enabled
     */
    public function run($migration = null)
    {

        $this->_init();

        if (!$this->isActive())
        {
            throw new EngineNotInitialisedException('Migrations not enabled');
        }

        $migrations       = $this->getAll();
        $latest_migration = $this->getLatest();

        /*
         * Check that the target migration exists and is not the current
         * migration
         */
        if (($migration == 0 OR isset($migrations[$migration])) AND
            $migration != $latest_migration->getId())
        {

            /*
             * Ascertain in which direction the migrations are being run
             */
            if ($migration < $latest_migration->getId())
            {
                $direction  = 'down';
                $migrations = array_reverse($migrations, true);
            }

            else
            {
                $direction  = 'up';
            }

            /*
             * Shuffle off unneeded migrations until the starting point is
             * reached
             */
            foreach ($migrations as $migration_id => $temp_migration)
            {

                if ($direction == 'up' AND
                    ($temp_migration->getId() <= $latest_migration->getId() OR
                     $temp_migration->getId() > $migration))
                {
                    unset($migrations[$migration_id]);
                }

                if ($direction == 'down' AND
                    ($temp_migration->getId() > $latest_migration->getId() OR
                     $temp_migration->getId() < $migration))
                {
                    unset($migrations[$migration_id]);
                }
                
            }

            $migrations = array_reverse($migrations);

            /*
             * Iterate through each migration
             */
            foreach ($migrations as $active_migration)
            {

                /*
                 * Check that the active migration is within bounds
                 */
                if (($direction == 'up' AND $active_migration->getId() <= $migration) OR
                    ($direction == 'down' AND $active_migration->getId() >= $migration))
                {

                    /*
                     * Action the migration, unless we are moving down and this
                     * migration is the target
                     */
                    if (!($active_migration->getId() == $migration AND $direction == 'down'))
                    {
                        $active_migration->$direction();
                    }

                    /*
                     * Make a note of it if necessary
                     */
                    $latest_set_result = true;
                    
                    if ($active_migration->getId() != $latest_migration->getId())
                    {
                        $latest_set_result = $this->_setLatest($active_migration);
                    }

                    /*
                     * In the case of an error, halt here to minimise damage
                     */
                    if (!$latest_set_result)
                    {
                        return false;
                    }

                }

            }

            /*
             * Successful if no errors were encountered
             */
            return true;

        }

        return false;

    }


    /**
     * Create a new migration file
     * @return string Migration file name
     * @throws EngineNotInitialisedException if migrations are not enabled
     */
    public function create()
    {

        $this->_init();

        if (!$this->isActive())
        {
            throw new EngineNotInitialisedException('Migrations not enabled');
        }

        return Job::create($this->_directory);

    }


    /**
     * Get the latest migration that has been run
     * @return Job Migration Job object
     * @throws EngineNotInitialisedException if migrations are not enabled
     */
    public function getLatest()
    {

        $this->_init();

        if (!$this->isActive())
        {
            throw new EngineNotInitialisedException('Migrations not enabled');
        }

        $migration = $this->_dependencies->db->build()
                                             ->table($this->_db_table)
                                             ->fetch(1)
                                             ->orderBy('id', 'DESC')
                                             ->getField('migration');

        if ($migration)
        {

            $migration_class = $this->getMigrationClassName('Job_' . $migration);

            return new $migration_class();

        }

        /*
         * 'Origin' job
         */
        return new Job_0();

    }


    /**
     * Record the latest migration to have been run
     * @param  Job  $migration Migration Job object
     * @return bool            Whether the migration name was successfully set
     * @throws EngineNotInitialisedException if migrations are not enabled
     * @throws RecordWriteException if the migration index could not be updated
     */
    protected function _setLatest($migration = null)
    {

        $this->_init();
        
        if (!$this->isActive())
        {
            throw new EngineNotInitialisedException('Migrations not enabled');
        }

        $result = $this->_dependencies->db->build()
                                          ->table($this->_db_table)
                                          ->data('migration', $migration->getId())
                                          ->data('datetime', time())
                                          ->insert();

        if ($result !== false)
        {
            return true;
        }

        throw new RecordWriteException('Migration index could not be updated');

    }


    /**
     * Check if migrations can be used
     * @return bool Whether all migration prerequisites have been fulfilled
     */
    public function isActive()
    {

        if ($this->_dependencies->db->connected())
        {

            /*
             * Check if directory exists and is writable
             */
            if (!is_null($this->_directory) AND
                is_readable($this->_directory) AND
                is_dir($this->_directory) AND
                is_writable($this->_directory))
            {

                $table_name = $this->_dependencies->db->escape($this->_db_table);

                /*
                 * Check if the migration table exists
                 */
                $table_check = $this->_dependencies->db->raw('SHOW TABLES LIKE \'' . $table_name . '\'');

                foreach ($table_check as $value)
                {
                    foreach ($value as $value_2)
                    {
                        if ($value_2 == $this->_db_table)
                        {
                            return true;
                        }
                    }
                }

            }

        }

        return false;

    }


    /**
     * Get the fully-qualified class name of a migration
     * @param  string $migration Short migration class name
     * @return string            Fully-qualified migration class name
     * @throws InvalidArgumentException if the migration does not exist
     */
    public function getMigrationClassName($migration = 'Job_0')
    {

        $filename = $this->_directory . $migration . '.php';

        if ($migration == 'Job_0' OR is_readable($filename))
        {
            return trim(__NAMESPACE__, '\\') . '\\' . $migration;
        }

        throw new InvalidArgumentException('Invalid migration');

    }


}