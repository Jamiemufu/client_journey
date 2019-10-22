<?php


namespace Whiskey\Bourbon;


use Exception;
use Whiskey\Bourbon\App\Facade\Migration;
use Whiskey\Bourbon\App\Facade\Server;
use Whiskey\Bourbon\App\Facade\Utils;


/**
 * Cli class
 * @package Whiskey\Bourbon
 */
class Cli
{


    protected $_handle = null;


    protected static $_cli_path = '_whsky/cli';


    /**
     * Instantiate the CLI and listen for standard input
     */
    public function __construct()
    {

        $this->_handle = fopen('php://stdin', 'r');

        $this->_showOptions();

    }


    /**
     * Clean up the CLI
     */
    public function __destruct()
    {

        fclose($this->_handle);

        $this->_clearScreen();

    }


    /**
     * Get the URL fragment to access the CLI
     * @return string URL path
     */
    public static function getPath()
    {

        return static::$_cli_path;

    }


    /**
     * Attempt to clear the terminal window
     */
    protected function _clearScreen()
    {

        /*
         * Windows
         */
        if (stristr(PHP_OS, 'win') AND
            !stristr(PHP_OS, 'darwin'))
        {
            passthru('cls');
        }

        /*
         * *NIX
         */
        else
        {
            passthru('clear');
        }

    }


    /**
     * Display a message to the user
     * @param string $message Message to display
     */
    protected function _displayMessage($message = '')
    {

        print $message . "\n";

    }


    /**
     * Get user input
     * @return string User input
     */
    protected function _getUserInput()
    {

        return trim(fgets($this->_handle, 1024));

    }


    /**
     * Display a message and get the user's response
     * @param  string $message Message to display
     * @return string          User's response
     */
    protected function _talkToUser($message = '')
    {

        $this->_displayMessage($message);

        return $this->_getUserInput();

    }


    /**
     * Show options menu to the user
     */
    protected function _showOptions()
    {

        $this->_clearScreen();

        $choice = (int)$this->_talkToUser("Welcome to the Whiskey CLI\n\nWhat would you like to do?\n\n1) Run latest migrations\n2) View active server modules\n3) View system information\n4) Exit\n");

        switch ($choice)
        {

            case 1:
                $this->_runMigrations('up');
                break;

            case 2:
                $this->_viewActiveServerModules();
                break;

            case 3:
                $this->_viewSystemInformation();
                break;

            case 4:
                break;

            default:
                $this->_showOptions();
                break;

        }

    }


    /**
     * View active server modules
     */
    protected function _viewActiveServerModules()
    {

        $this->_clearScreen();

        $active_modules =  "Active Server Modules\n";
        $active_modules .= "\n[" . (extension_loaded('mysqli') ? '*' : ' ') . "] MySQLi";
        $active_modules .= "\n[" . (extension_loaded('mcrypt') ? '*' : ' ') . "] mcrypt";
        $active_modules .= "\n[" . (extension_loaded('gd') ? '*' : ' ') . "] GD";
        $active_modules .= "\n[" . (extension_loaded('curl') ? '*' : ' ') . "] cURL";
        $active_modules .= "\n[" . (class_exists('\\finfo') ? '*' : ' ') . "] finfo";
        $active_modules .= "\n[" . (function_exists('json_encode') ? '*' : ' ') . "] php5-json";
        
        $this->_talkToUser($active_modules . "\n");

        /*
         * Return to the menu
         */
        $this->_showOptions();

    }


    /**
     * View system information
     */
    protected function _viewSystemInformation()
    {

        $this->_clearScreen();

        $system_info =  "System Information\n";
        $system_info .= "\nSystem User: " . Server::whoAmI();
        $system_info .= "\nSystem Info: " . php_uname();
        $system_info .= "\nPHP Version: " . PHP_VERSION;
        $system_info .= "\nServer Version: " . $_SERVER["SERVER_SOFTWARE"];
        $system_info .= "\nServer Name: " . $_SERVER["SERVER_NAME"];
        $system_info .= "\nServer IP: " . $_SERVER["SERVER_ADDR"];
        $system_info .= "\nDomain Root: " . $_SERVER["DOCUMENT_ROOT"];
        $system_info .= "\nServer Admin: " . $_SERVER["SERVER_ADMIN"];
        $system_info .= "\nDisk Usage: " . Utils::friendlyFileSize(Server::disk()->used) . ' / ' . Utils::friendlyFileSize(Server::disk()->total);

        if (Server::memory()->total)
        {
            $cpu_models   = Server::cpu()->names;
            $cpu_models   = reset($cpu_models);
            $system_info .= "\nMemory Usage: " . Utils::friendlyFileSize(Server::memory()->used) . ' / ' . Utils::friendlyFileSize(Server::memory()->total);
            $system_info .= "\nCPU Model: " . $cpu_models;
            $system_info .= "\nCPU Cores: " . Server::cpu()->cores;
        }

        $this->_talkToUser($system_info . "\n");

        /*
         * Return to the menu
         */
        $this->_showOptions();

    }


    /**
     * Apply migrations
     * @param string $direction Direction in which to apply migrations ('up' or 'down')
     */
    protected function _runMigrations($direction = 'up')
    {

        $this->_clearScreen();

        $direction = (strtolower($direction) == 'up') ? 'up' : 'down';

        /*
         * Run migrations up
         */
        if ($direction == 'up')
        {

            try
            {

                $migrations        = Migration::getAll();
                $migrations        = array_reverse($migrations);
                $latest_migration  = Migration::getLatest();
                $migrations_to_run = [];

                /*
                 * Get migrations that haven't been run yet
                 */
                foreach ($migrations as $migration)
                {

                    if ($migration->getId() > $latest_migration->getId())
                    {
                        $migrations_to_run[] = $migration;
                    }

                }

                /*
                 * Check if there are any migrations to run
                 */
                if (empty($migrations_to_run))
                {
                    $this->_talkToUser("No migrations to run\n");
                    $this->_showOptions();
                    return;
                }

                else
                {

                    /*
                     * Run each migration
                     */
                    foreach ($migrations_to_run as $migration)
                    {

                        $migration_name = ($migration->description != '') ? $migration->description : $migration->getId();

                        /*
                         * Per-migration success
                         */
                        try
                        {
                            Migration::run($migration->getId());
                            $this->_displayMessage('Success: ' . $migration_name);
                        }

                        /*
                         * Per-migration failure -- alert the user and return to
                         * the menu
                         */
                        catch (Exception $exception)
                        {
                            $this->_displayMessage('Failure: ' . $migration_name);
                            $this->_talkToUser("\nErrors encountered when attempting to run migrations\n");
                            $this->_showOptions();
                            return;
                        }

                    }

                }

                /*
                 * If all migrations were successful
                 */
                $this->_talkToUser("\nMigrations successfully run");

            }

            /*
             * Miscellaneous errors (such as PHP parse errors in migration
             * classes)
             */
            catch (Exception $exception)
            {
                $this->_talkToUser('Unable to run migrations: ' . $exception->getMessage() . "\n");
            }

        }

        /*
         * Return to the menu
         */
        $this->_showOptions();

    }


}