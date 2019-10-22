<?php


namespace Whiskey\Bourbon;


use Exception;
use Whiskey\Bourbon\App\Bootstrap as Bourbon;
use Whiskey\Bourbon\App\Facade\AppEnv;
use Whiskey\Bourbon\App\Facade\Auth;
use Whiskey\Bourbon\App\Facade\Db;
use Whiskey\Bourbon\App\Facade\Migration;
use Whiskey\Bourbon\App\Facade\Input;
use Whiskey\Bourbon\App\Facade\Email;
use Whiskey\Bourbon\App\Facade\Cron;
use Whiskey\Bourbon\App\Facade\Server;
use Whiskey\Bourbon\App\Facade\Router;
use Whiskey\Bourbon\App\Facade\Utils;
use Whiskey\Bourbon\App\Facade\Storage;
use Whiskey\Bourbon\App\Facade\Cache;
use Whiskey\Bourbon\Helper\Component\SafeString;
use Whiskey\Bourbon\App\Http\Response;
use Whiskey\Bourbon\Templating\Engine\Ice\Renderer as Ice;
use Twig_Loader_Filesystem;
use Aws\S3\S3Client;


/**
 * Dashboard class
 * @package Whiskey\Bourbon
 */
class Dashboard
{


    protected static $_dashboard_path = '_whsky/dashboard';
    protected static $_link_root      = null;


    /**
     * Get the URL fragment to access the dashboard
     * @return string URL path
     */
    public static function getPath()
    {

        return static::$_dashboard_path;

    }


    /**
     * Get the application's link root
     * @return string Application link root
     */
    public static function getLinkRoot()
    {

        if (is_null(self::$_link_root))
        {
            self::$_link_root = Bourbon::getInstance()->getLinkRootPath();
        }

        return self::$_link_root;

    }


    /**
     * Get the HTML to display the Dashboard link nub
     * @return string Link HTML
     */
    public static function getNubHtml()
    {

        /*
         * Get stats
         */
        $autoload_logs        = Bourbon::getInstance()->getAutoloadLogs();
        $database_connections = Db::getConnectionNames();

        /*
         * Add the current time on, for context
         */
        $autoload_logs["autoloads"][] =
            [
                "class"             => "End Of Script",
                "time"              => (microtime(true) - $autoload_logs["stats"]["start_time"]),
                "time_percentage"   => 100,
                "memory"            => memory_get_peak_usage(),
                "memory_percentage" => 100
            ];


        $result = "<div id='_whsky_sidebar_widget' style='position: fixed; left: 0; right: 0; bottom: 0; height: 0; overflow: visible; z-index: 2147483647; box-shadow: 0 0 10px 0 rgba(255, 255, 255, 0.35); background-color: rgb(35, 35, 35);'>
                       <span onClick='var _new_whsky_sidebar_height = document.getElementById(\"_whsky_sidebar_widget\").style.height; document.getElementById(\"_whsky_sidebar_widget\").style.height = (_new_whsky_sidebar_height == \"250px\" ? \"0\" : \"250px\");' title='Developer Tools' style='cursor: pointer; position: absolute; top: -15px; left: 50%; margin-left: -24px; width: 48px; height: 15px; background-color: rgb(35, 35, 35); border-top-left-radius: 4px; border-top-right-radius: 4px; background-repeat: no-repeat; background-position: center; box-shadow: 0 0 10px 0 rgba(255, 255, 255, 0.35); background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAHCAQAAACSTrSSAAAAEklEQVQIW2P8b89AA8A4AMYCAK4HA77RwX2pAAAAAElFTkSuQmCC);'></span>
                       <div style='position: absolute; top: 0; bottom: 0; left: 0; right: 0; padding: 20px; background-color: rgb(35, 35, 35);'>
                           <div style='position: absolute; top: 0; left: 0; right: 0; height: 25px; border-radius: 2px; padding-left: 1px;'>
                               <div onClick='document.getElementById(\"_whsky_dashboard_nub_information_content\").style.zIndex = 1; document.getElementById(\"_whsky_dashboard_nub_timeline_content\").style.zIndex = 1; document.getElementById(\"_whsky_dashboard_nub_database_content\").style.zIndex = 1; document.getElementById(\"_whsky_dashboard_nub_dashboard_content\").style.zIndex = 2;' style='background-color: rgba(255, 255, 255, 0.05); display: inline-block; line-height: 20px; margin: 1px; padding: 1px 15px; color: #999999; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px; cursor: pointer; float: left;'>Dashboard</div>
                               <div onClick='document.getElementById(\"_whsky_dashboard_nub_dashboard_content\").style.zIndex = 1; document.getElementById(\"_whsky_dashboard_nub_timeline_content\").style.zIndex = 1; document.getElementById(\"_whsky_dashboard_nub_database_content\").style.zIndex = 1; document.getElementById(\"_whsky_dashboard_nub_information_content\").style.zIndex = 2;' style='background-color: rgba(255, 255, 255, 0.05); display: inline-block; line-height: 20px; margin: 1px 1px 1px 0; padding: 1px 15px; color: #999999; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px; cursor: pointer; float: left;'>Info</div>
                               <div onClick='document.getElementById(\"_whsky_dashboard_nub_dashboard_content\").style.zIndex = 1; document.getElementById(\"_whsky_dashboard_nub_timeline_content\").style.zIndex = 1; document.getElementById(\"_whsky_dashboard_nub_information_content\").style.zIndex = 1; document.getElementById(\"_whsky_dashboard_nub_database_content\").style.zIndex = 2;' style='background-color: rgba(255, 255, 255, 0.05); display: inline-block; line-height: 20px; margin: 1px 1px 1px 0; padding: 1px 15px; color: #999999; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px; cursor: pointer; float: left;'>Databases</div>
                               <div onClick='document.getElementById(\"_whsky_dashboard_nub_dashboard_content\").style.zIndex = 1; document.getElementById(\"_whsky_dashboard_nub_information_content\").style.zIndex = 1; document.getElementById(\"_whsky_dashboard_nub_database_content\").style.zIndex = 1; document.getElementById(\"_whsky_dashboard_nub_timeline_content\").style.zIndex = 2;' style='background-color: rgba(255, 255, 255, 0.05); display: inline-block; line-height: 20px; margin: 1px 1px 1px 0; padding: 1px 15px; color: #999999; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px; cursor: pointer; float: left;'>Timeline</div>
                           </div>
                           <div id='_whsky_dashboard_nub_dashboard_content' style='position: absolute; top: 24px; left: 2px; right: 2px; bottom: 2px; background-color: rgb(255, 255, 255); border-radius: 2px; overflow: auto; z-index: 2;'>
                               <div style='color: #333333; background-color: rgba(0, 0, 0, 0.05); padding: 3px; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <sup>[link]</sup> <a href='" . Dashboard::getLinkRoot() . static::getPath() . "' target='_blank' style='color: #333333;'></sup>Dashboard</a>
                               </div>
                               <div style='color: #333333; background-color: rgba(0, 0, 0, 0.1); padding: 3px; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <sup>[link]</sup> <a href='" . Dashboard::getLinkRoot() . static::getPath() . "?migrations' target='_blank' style='color: #333333;'></sup>Migration Management</a>
                               </div>
                               <div style='color: #333333; background-color: rgba(0, 0, 0, 0.05); padding: 3px; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <sup>[link]</sup> <a href='" . Dashboard::getLinkRoot() . static::getPath() . "?cron' target='_blank' style='color: #333333;'>Cron Job Management</a>
                               </div>
                               <div style='color: #333333; background-color: rgba(0, 0, 0, 0.1); padding: 3px; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <sup>[link]</sup> <a href='http://whsky.uk' target='_blank' style='color: #333333;'>Framework Documentation</a>
                               </div>
                           </div>
                           <div id='_whsky_dashboard_nub_information_content' style='position: absolute; top: 24px; left: 2px; right: 2px; bottom: 2px; background-color: rgb(255, 255, 255); border-radius: 2px; overflow: auto; z-index: 1;'>
                               <div style='color: #333333; background-color: rgba(0, 0, 0, 0.05); font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding: 3px; border-right: 1px solid rgba(0, 0, 0, 0.1); color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>Route</div><div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding-left: 10px; color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>Controller: [" . SafeString::sanitise(AppEnv::controller()) . "] &nbsp;&nbsp;&nbsp;Action: [" . SafeString::sanitise(AppEnv::action()) . "] &nbsp;&nbsp;&nbsp;Slug: [" . SafeString::sanitise(implode('/', AppEnv::slugs())) . "]</div>
                               </div>
                               <div style='color: #333333; background-color: rgba(0, 0, 0, 0.1); font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding: 3px; border-right: 1px solid rgba(0, 0, 0, 0.1); color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>Authentication</div><div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding-left: 10px; color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>" . (Auth::isLoggedIn() ? SafeString::sanitise((isset(Auth::details()->username) ? Auth::details()->username : (isset(Auth::details()->email) ? Auth::details()->email : json_encode(Auth::details())))) : "Not logged in") . "</div>
                               </div>
                               <div style='color: #333333; background-color: rgba(0, 0, 0, 0.05); font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding: 3px; border-right: 1px solid rgba(0, 0, 0, 0.1); color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>Framework Version</div><div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding-left: 10px; color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>v" . SafeString::sanitise(Bourbon::VERSION) . "</div>
                               </div>
                               <div style='color: #333333; background-color: rgba(0, 0, 0, 0.1); font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding: 3px; border-right: 1px solid rgba(0, 0, 0, 0.1); color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>Environment</div><div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding-left: 10px; color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>" . SafeString::sanitise(ucwords(strtolower($_ENV['APP_ENVIRONMENT']))) . "</div>
                               </div>
                               <div style='color: #333333; background-color: rgba(0, 0, 0, 0.05); font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding: 3px; border-right: 1px solid rgba(0, 0, 0, 0.1); color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>Execution Time</div><div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding-left: 10px; color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>" . number_format(Bourbon::getInstance()->getExecutionTime(), 5) . "s</div>
                               </div>
                               <div style='color: #333333; background-color: rgba(0, 0, 0, 0.1); font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding: 3px; border-right: 1px solid rgba(0, 0, 0, 0.1); color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>PHP Version</div><div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding-left: 10px; color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>" . SafeString::sanitise(phpversion()) . "</div>
                               </div>
                               <div style='color: #333333; background-color: rgba(0, 0, 0, 0.05); font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding: 3px; border-right: 1px solid rgba(0, 0, 0, 0.1); color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>Server User</div><div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding-left: 10px; color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>" . SafeString::sanitise(Server::whoAmI()) . "</div>
                               </div>
                               <div style='color: #333333; background-color: rgba(0, 0, 0, 0.1); font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding: 3px; border-right: 1px solid rgba(0, 0, 0, 0.1); color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>Server Name</div><div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding-left: 10px; color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>" . SafeString::sanitise($_SERVER["SERVER_NAME"]) . "</div>
                               </div>
                               <div style='color: #333333; background-color: rgba(0, 0, 0, 0.05); font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding: 3px; border-right: 1px solid rgba(0, 0, 0, 0.1); color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>IP Address</div><div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding-left: 10px; color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>" . SafeString::sanitise($_SERVER["SERVER_ADDR"]) . "</div>
                               </div>";
        if (Server::memory()->total)
        {
            $result .= "       <div style='color: #333333; background-color: rgba(0, 0, 0, 0.1); font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>
                                   <div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding: 3px; border-right: 1px solid rgba(0, 0, 0, 0.1);'>Memory Usage</div><div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 50%; word-break: break-all; padding-left: 10px;'>" . Utils::friendlyFileSize(Server::memory()->used) . " &#47; " . Utils::friendlyFileSize(Server::memory()->total) . "</div>
                               </div>";
        }
        $result .= "       </div>
                           <div id='_whsky_dashboard_nub_timeline_content' style='position: absolute; top: 24px; left: 2px; right: 2px; bottom: 2px; background-color: rgb(255, 255, 255); border-radius: 2px; overflow: auto; z-index: 1;'>";
        $log_count = 0;
        foreach ($autoload_logs["autoloads"] as $autoload)
        {
            $result .= "       <div style='color: #333333; background-color: " . (($log_count++ % 2 == 0) ? "rgba(0, 0, 0, 0.05)" : "rgba(0, 0, 0, 0.1)") . "; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'><div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 34%; word-break: break-all; padding: 3px; border-right: 1px solid rgba(0, 0, 0, 0.1); color: #333333; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>" . SafeString::sanitise($autoload["class"]) . "</div><div style='display: inline-block; vertical-align: middle; box-sizing: border-box; width: 66%; height: 20px; position: relative;'><div style='position: absolute; top: 8px; left: 0; height: 4px; width: " . $autoload["memory_percentage"] . "%; background-color: rgba(0, 0, 0, 0.05);'></div><div style='position: absolute; top: 1px; bottom: 1px; left: " . ($autoload["time_percentage"] * 0.8) . "%; background-color: #666666; color: #ffffff; border-radius: 2px; font-size: 10px; padding: 2px 5px; line-height: 15px; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif;' title='Memory Usage\n" . number_format(($autoload["memory"] / 1024 / 1024), 5) . " MiB'>" . number_format($autoload["time"], 3) . "s</div></div></div>";
        }
        $result .= "       </div>
                           <div id='_whsky_dashboard_nub_database_content' style='position: absolute; top: 24px; left: 2px; right: 2px; bottom: 2px; background-color: rgb(255, 255, 255); border-radius: 2px; overflow: auto; z-index: 1;'>";
        if (count($database_connections))
        {
            $database_count = 0;
            foreach ($database_connections as $connection_name)
            {
                try
                {
                    $db_connected = Db::swap($connection_name)->connected();
                }
                catch (Exception $exception)
                {
                    $db_connected = false;
                }
                $result .= "       <div style='color: #333333; background-color: " . (($database_count++ % 2 == 0) ? "rgba(0, 0, 0, 0.05)" : "rgba(0, 0, 0, 0.1)") . "; padding: 3px; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'><span style=\"display: inline-block; vertical-align: middle; box-sizing: border-box; height: 10px; width: 10px; border-radius: 50%; border: 1px solid rgba(0, 0, 0, 0.1); margin: 0 10px 0 5px; background-color: rgb(" . ($db_connected ? "154, 205, 50" : "227, 0, 34") . ");\"></span>" . $connection_name . "</div>";
            }
            $result .= "       </div>";
        }
        else
        {
            $result .= "       <div style='color: #333333; background-color: rgba(0, 0, 0, 0.05); padding: 3px; font-family: \"Open Sans\", \"Century Gothic\", Arial, sans-serif; font-size: 14px;'>No database connections found</div>";
        }
        $result .= "       </div>
                       </div>
                   </div>";
        return $result;

    }


}


/*
 * Check to see if we've requested this page and are in the development
 * environment
 */
if (Router::isCurrentUrl(Dashboard::getPath()) AND
    ($_ENV['APP_ENVIRONMENT'] == "development" OR $_ENV['APP_DEBUG']))
{
    
    
    $response = Instance::_retrieve(Response::class);
    
    
    /*
     * Before anything else, serve up a 404 header, to make non-humans ignore
     * this page (in addition to the robots <meta> element)
     */
    $response->notFound(false);


    /*
     * Clear cache
     */
    if (isset($_GET["clear_cache"]))
    {

        Cache::clearAll();

        /*
         * Refresh the page
         */
        $response->redirect(Dashboard::getLinkRoot() . Dashboard::getPath() . "#cache_engines");

        exit;

    }


    /*
     * Add cron jobs
     */
    if (isset($_POST["cron_add"]))
    {

        if (isset($_POST["minute"]) AND
            isset($_POST["hour"]) AND
            isset($_POST["day"]) AND
            isset($_POST["month"]) AND
            isset($_POST["day_of_week"]) AND
            isset($_POST["command"]) AND
            $_POST["command"])
        {
            Cron::add($_POST["minute"],
                      $_POST["hour"],
                      $_POST["day"],
                      $_POST["month"],
                      $_POST["day_of_week"],
                      $_POST["command"]);
        }

        /*
         * Refresh the page
         */
        $response->redirect(Dashboard::getLinkRoot() . Dashboard::getPath() . "?cron");

        exit;

    }


    /*
     * Delete cron jobs
     */
    if (isset($_POST["cron_delete"]))
    {

        $cron_job = $_POST["cron_delete"];
        $cron_job = explode(" ", $cron_job);

        if (count($cron_job) >= 6)
        {
            Cron::remove(array_shift($cron_job),
                         array_shift($cron_job),
                         array_shift($cron_job),
                         array_shift($cron_job),
                         array_shift($cron_job),
                         implode(" ", $cron_job));
        }

        /*
         * Refresh the page
         */
        $response->redirect(Dashboard::getLinkRoot() . Dashboard::getPath() . "?cron");

        exit;

    }


    /*
     * Perform migrations
     */
    if (isset($_GET["migrate_to"]))
    {

        Migration::run((int)Input::get("migrate_to"));

        /*
         * Refresh the page
         */
        $response->redirect(Dashboard::getLinkRoot() . Dashboard::getPath() . "?migrations#migrations_list");

        exit;

    }


    /*
     * Create a new migration
     */
    if (isset($_GET["create_migration"]))
    {

        $result = Migration::create();

        $migration_name = "";

        if ($result)
        {
            $migration_name = $result;
            $migration_name = explode(".", $migration_name);
            $migration_name = array_shift($migration_name);
        }

        /*
         * Refresh the page
         */
        $response->redirect(Dashboard::getLinkRoot() . Dashboard::getPath() . "?migrations&new_migration=" . $migration_name);

        exit;

    }


    /*
     * Reset the migration pointer to 'origin'
     */
    if (isset($_GET["reset_migrations"]))
    {

        Migration::reset();

        /*
         * Refresh the page
         */
        $response->redirect(Dashboard::getLinkRoot() . Dashboard::getPath() . "?migrations&migrations_reset");

        exit;

    }


    $whiskey_icon = 'data:image/png;base64,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';

    ?>

    <!DOCTYPE html>

    <html lang='en'>

        <head>

            <title><?php echo "Dashboard :: Whiskey Framework v" . Bourbon::VERSION; ?></title>
            <meta name='robots' content='noindex, nofollow' />
            <meta charset='utf-8' />
            <link rel='shortcut icon' href='<?php echo $whiskey_icon; ?>' />
            <meta name='viewport' content='user-scalable=no, width=device-width' />
            <!--[if lt IE 9]>
                <script src='https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js'></script>
                <script src='https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js'></script>
            <![endif]-->

            <style>

                @font-face
                {
                    font-family: 'Open Sans';
                    font-style: normal;
                    font-weight: 300;
                    src: url(http://themes.googleusercontent.com/static/fonts/opensans/v8/DXI1ORHCpsQm3Vp6mXoaTXhCUOGz7vYGh680lGh-uXM.woff) format('woff');
                }

                *
                {
                    -moz-box-sizing: border-box;
                    box-sizing: border-box;
                    font-family: 'Open Sans', Arial, Verdana, sans-serif;
                    font-size: 14px;
                }

                body
                {
                    margin: 0;
                    padding: 63px 20px 20px 20px;
                    overflow-x: hidden;
                    background-color: rgb(244, 244, 244);
                    color: rgb(0, 0, 0);
                    text-align: center;
                }

                a
                {
                    color: rgb(0, 0, 0);
                }

                strong
                {
                    font-weight: bold;
                }

                em
                {
                    font-style: italic;
                }

                img
                {
                    border: 0;
                    display: inline-block;
                    vertical-align: top;
                }

                h1, h2, h3
                {
                    margin-left: 0;
                    margin-right: 0;
                    margin-top: 0;
                    margin-bottom: 10px;
                    display: inline-block;
                }

                h1
                {
                    font-size: 24px;
                    margin-bottom: 0;
                }

                h2
                {
                    font-size: 20px;
                }

                h3
                {
                    font-size: 14px;
                }

                p + h3
                {
                    margin-top: 20px;
                }

                header
                {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    padding: 15px;
                    height: 63px;
                    background-color: rgb(31, 31, 31);
                    color: rgb(255, 255, 255);
                    box-shadow: 0 0 5px rgba(0, 0, 0, 0.75);
                }

                header > a >img
                {
                    height: 33px;
                    margin-right: 10px;
                }

                section, nav
                {
                    margin: 20px auto 0 auto;
                    padding: 20px;
                    background-color: rgb(255, 255, 255);
                    box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.25);
                    width: 100%;
                    max-width: 1200px;
                    text-align: left;
                }

                nav
                {
                    padding: 0;
                }

                nav > a
                {
                    display: inline-block;
                    vertical-align: top;
                    width: 33%;
                    padding: 15px;
                    text-align: center;
                    font-weight: bold;
                    text-decoration: none;
                    border-left: 1px solid rgba(0, 0, 0, 0.05);
                }

                nav > a:first-child
                {
                    width: 34%;
                    border-left: none;
                }

                nav > a.active
                {
                    background-color: rgba(0, 0, 0, 0.05);
                    box-shadow: inset 5px 5px 10px rgba(0, 0, 0, 0.1)
                }

                p
                {
                    margin-left: 0;
                    margin-right: 0;
                    margin-top: 0;
                    margin-bottom: 10px;
                }

                section > p:last-of-type
                {
                    margin-bottom: 0;
                }

                .status
                {
                    display: inline-block;
                    vertical-align: middle;
                    height: 10px;
                    width: 10px;
                    border-radius: 50%;
                    border: 1px solid rgba(0, 0, 0, 0.1);
                    margin: 0 10px;
                }

                .active
                {
                    background-color: rgb(154, 205, 50);
                }

                .inactive
                {
                    background-color: rgb(227, 0, 34);
                }

                table
                {
                    border-collapse: collapse;
                    width: 100%;
                }

                th
                {
                    background-color: rgb(250, 250, 250);
                }

                th, td
                {
                    padding: 5px 10px;
                    text-align: left;
                    vertical-align: top;
                }

                th:first-child, td:first-child
                {
                    text-align: right;
                    font-weight: bold;
                    white-space: nowrap;
                }

                th:nth-child(2), td:nth-child(2)
                {
                    width: 100%;
                }

                th:nth-child(3), td:nth-child(3)
                {
                    text-align: center;
                }

                input[type='button'], input[type='submit'], button, .button
                {
                    background-image: linear-gradient(bottom, rgb(221,221,221) 21%, rgb(245,245,245) 61%, rgb(255,255,255) 81%);
                    background-image: -o-linear-gradient(bottom, rgb(221,221,221) 21%, rgb(245,245,245) 61%, rgb(255,255,255) 81%);
                    background-image: -moz-linear-gradient(bottom, rgb(221,221,221) 21%, rgb(245,245,245) 61%, rgb(255,255,255) 81%);
                    background-image: -webkit-linear-gradient(bottom, rgb(221,221,221) 21%, rgb(245,245,245) 61%, rgb(255,255,255) 81%);
                    background-image: -ms-linear-gradient(bottom, rgb(221,221,221) 21%, rgb(245,245,245) 61%, rgb(255,255,255) 81%);
                    background-image: -webkit-gradient(
                        linear,
                        left bottom,
                        left top,
                        color-stop(0.21, rgb(221,221,221)),
                        color-stop(0.61, rgb(245,245,245)),
                        color-stop(0.81, rgb(255,255,255))
                    );
                    border-radius: 3px;
                    border: 1px solid #BBBBBB;
                    padding-top: 5px;
                    padding-bottom: 5px;
                    padding-left: 10px;
                    padding-right: 10px;
                }

                input[type='button']:active, input[type='submit']:active, button:active, .button:active, .toggled_button
                {
                    background-image: linear-gradient(bottom, rgb(255,255,255) 21%, rgb(245,245,245) 61%, rgb(221,221,221) 81%);
                    background-image: -o-linear-gradient(bottom, rgb(255,255,255) 21%, rgb(245,245,245) 61%, rgb(221,221,221) 81%);
                    background-image: -moz-linear-gradient(bottom, rgb(255,255,255) 21%, rgb(245,245,245) 61%, rgb(221,221,221) 81%);
                    background-image: -webkit-linear-gradient(bottom, rgb(255,255,255) 21%, rgb(245,245,245) 61%, rgb(221,221,221) 81%);
                    background-image: -ms-linear-gradient(bottom, rgb(255,255,255) 21%, rgb(245,245,245) 61%, rgb(221,221,221) 81%);
                    background-image: -webkit-gradient(
                        linear,
                        left bottom,
                        left top,
                        color-stop(0.21, rgb(255,255,255)),
                        color-stop(0.61, rgb(245,245,245)),
                        color-stop(0.81, rgb(221,221,221))
                    );
                }

                input[type='button']:focus, input[type='submit']:focus, button:focus, .button:focus
                {
                    outline: none;
                }

                input[type='button']::-moz-focus-inner, input[type='submit']::-moz-focus-inner, button::-moz-focus-inner
                {
                    padding: 0;
                    border: 0;
                }

                input[type='text'], input[type='password'], input[type='number'], input[type='date']
                {
                    border-radius: 2px;
                    border: 1px solid rgba(0, 0, 0, 0.2);
                    padding: 3px;
                    box-shadow: inset 1px 1px 1px rgba(0, 0, 0, 0.1);
                    background-color: #FFFFFF;
                }

                select, textarea
                {
                    box-shadow: inset 1px 1px 1px rgba(0, 0, 0, 0.1);
                    padding: 3px;
                    border-radius: 2px;
                    border: 1px solid rgba(0, 0, 0, 0.2);
                    background-color: #FFFFFF;
                }

                #cron_job_list input
                {
                    margin: 0;
                }

                .cron_period_input
                {
                    width: 25px;
                }

                #cron_new_command_input
                {
                    width: 100%;
                }

                #cron_job_list th, #cron_job_list td
                {
                    vertical-align: middle;
                    padding: 5px;
                }

                p.border-bottom:not(:last-child)
                {
                    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
                    padding-bottom: 20px;
                    margin-bottom: 20px;
                }

                .pre
                {
                    white-space: pre-wrap;
                    display: inline-block;
                    font-family: monospace;
                }

                .actions-column
                {
                    text-align: right;
                }

                .no-wrap
                {
                    white-space: nowrap;
                }

                .no-wrap > form
                {
                    display: inline-block;
                }

                #main > section,
                #cron > section,
                #migrations > section
                {
                    overflow: hidden;
                }

            </style>

        </head>

        <body>

            <header>
                <a href='<?php echo Dashboard::getLinkRoot() . Dashboard::getPath(); ?>'><img src='<?php echo $whiskey_icon; ?>' alt='Whiskey Framework v<?php echo Bourbon::VERSION; ?>' /></a>
                <h1>Dashboard</h1>
            </header>

            <?php

            if (isset($_GET["migrations"]))
            {
                $active_section = "migrations";
            }

            else if (isset($_GET["cron"]))
            {
                $active_section = "cron";
            }

            else
            {
                $active_section = "";
            }

            ?>

            <nav><a<?php echo $active_section == "" ? " class='active'" : ""; ?> href='<?php echo Dashboard::getLinkRoot() . Dashboard::getPath(); ?>'>Info</a><a<?php echo $active_section == "migrations" ? " class='active'" : ""; ?> href='<?php echo Dashboard::getLinkRoot() . Dashboard::getPath(); ?>?migrations'>Migrations</a><a<?php echo $active_section == "cron" ? " class='active'" : ""; ?> href='<?php echo Dashboard::getLinkRoot() . Dashboard::getPath(); ?>?cron'>Cron</a></nav>

            <?php if ($active_section == "") { ?>

            <!-- Begin main section -->

            <div id='main'>

                <section>
                    <h2>What Is This?</h2>
                    <p>These pages display information about the server and allow you to manage certain aspects of the application. The widget that links here is only visible when in the <em>development</em> environment.</p>
                </section>

                <section>
                    <h2>Extensions</h2>
                    <p>The following extensions are required for all parts of Whiskey to function correctly:</p>
                    <p><span class='status <?php echo extension_loaded("mysqli") ? "" : "in"; ?>active'></span>MySQLi</p>
                    <p><span class='status <?php echo extension_loaded("mcrypt") ? "" : "in"; ?>active'></span>mcrypt</p>
                    <p><span class='status <?php echo extension_loaded("gd") ? "" : "in"; ?>active'></span>GD</p>
                    <p><span class='status <?php echo extension_loaded("curl") ? "" : "in"; ?>active'></span>cURL</p>
                    <p><span class='status <?php echo class_exists('\\finfo') ? "" : "in"; ?>active'></span>finfo</p>
                    <p><span class='status <?php echo function_exists("json_encode") ? "" : "in"; ?>active'></span>php5-json</p>
                    <?php
                    if (function_exists("apache_get_modules"))
                    {
                        ?>
                        <p><span class='status <?php echo in_array("mod_rewrite", apache_get_modules()) ? "" : "in"; ?>active'></span>mod_rewrite</p>
                        <p><span class='status <?php echo in_array("mod_mime", apache_get_modules()) ? "" : "in"; ?>active'></span>mod_mime</p>
                        <p><span class='status <?php echo in_array("mod_deflate", apache_get_modules()) ? "" : "in"; ?>active'></span>mod_deflate</p>
                        <p><span class='status <?php echo in_array("mod_expires", apache_get_modules()) ? "" : "in"; ?>active'></span>mod_expires</p>
                        <p><span class='status <?php echo in_array("mod_headers", apache_get_modules()) ? "" : "in"; ?>active'></span>mod_headers</p>
                        <?php
                    }
                    ?>
                </section>

                <section>
                    <h2>Databases</h2>
                    <?php
                    $database_connections = Db::getConnectionNames();
                    if (count($database_connections))
                    {
                        echo "<p>The following database connections are active:</p>";
                        foreach ($database_connections as $connection_name)
                        {
                            try
                            {
                                $db_connected = Db::swap($connection_name)->connected();
                            }
                            catch (Exception $exception)
                            {
                                $db_connected = false;
                            }
                            echo "<p><span class='status " . ($db_connected ? "" : "in") . "active'></span>" . $connection_name . "</p>";
                        }
                    }
                    else
                    {
                        echo "<p>No database connections present</p>";
                    }
                    ?>
                </section>

                <section id="templating_engines">
                    <h2>Templating Engines</h2>
                    <p>The following templating engines are enabled:</p>
                    <p><span class='status <?php echo class_exists(Ice::class) ? "" : "in"; ?>active'></span>Ice</p>
                    <p><span class='status <?php echo class_exists(Twig_Loader_Filesystem::class) ? "" : "in"; ?>active'></span>Twig</p>
                </section>

                <section>
                    <h2>Storage</h2>
                    <p>The following storage engines are enabled:</p>
                    <p><span class='status <?php echo class_exists(S3Client::class) ? "" : "in"; ?>active'></span>Amazon S3</p>
                    <p><span class='status <?php echo Storage::file()->isActive() ? "" : "in"; ?>active'></span>File</p>
                </section>

                <?php
                
                    $cache_dir = Bourbon::getInstance()->getDataCacheDirectory();
                
                ?>
                <section id="cache_engines">
                    <h2>Caching</h2>
                    <p>The following cache engines are enabled:</p>
                    <p><span class='status <?php echo extension_loaded("apc") ? "" : "in"; ?>active'></span>APC</p>
                    <p><span class='status <?php echo extension_loaded("memcached") ? "" : "in"; ?>active'></span>Memcached</p>
                    <p><span class='status <?php echo (is_readable($cache_dir) AND is_writable($cache_dir)) ? "" : "in"; ?>active'></span>File</p>
                    <?php
                    if (is_readable($cache_dir) AND is_writable($cache_dir))
                    {
                        ?>
                        <p>
                            <form action='<?php echo Dashboard::getLinkRoot() . Dashboard::getPath(); ?>?clear_cache' method='POST'>
                                <input type='submit' value='Flush Cache' />
                            </form>
                        </p>
                        <?php
                    }
                    ?>
                </section>

                <section>
                    <h2>E-mail</h2>
                    <p>The following e-mail engines are enabled:</p>
                    <p><span class='status <?php echo Email::swiftmailer()->isActive() ? "" : "in"; ?>active'></span>Swift Mailer</p>
                </section>

                <section>
                    <h2>Random Sources</h2>
                    <p>The following random sources are available:</p>
                    <p><span class='status <?php echo is_readable("/dev/random") ? "" : "in"; ?>active'></span>&#47;dev&#47;random</p>
                    <p><span class='status <?php echo is_readable("/dev/urandom") ? "" : "in"; ?>active'></span>&#47;dev&#47;urandom</p>
                </section>

                <section>
                    <h2>Environment</h2>
                    <p>The following information has been gathered from the server:</p>
                    <br />
                    <table>
                        <tbody>
                            <tr>
                                <td>System User</td><td><?php echo SafeString::sanitise(Server::whoAmI()); ?></td>
                            </tr>
                            <tr>
                                <td>System Info</td><td><?php echo SafeString::sanitise(php_uname()); ?></td>
                            </tr>
                            <tr>
                                <td>PHP Version</td><td><?php echo SafeString::sanitise(PHP_VERSION); ?></td>
                            </tr>
                            <tr>
                                <td>Server Version</td><td><?php echo SafeString::sanitise($_SERVER["SERVER_SOFTWARE"]); ?></td>
                            </tr>
                            <tr>
                                <td>Server Name</td><td><?php echo SafeString::sanitise($_SERVER["SERVER_NAME"]); ?></td>
                            </tr>
                            <tr>
                                <td>Server IP</td><td><?php echo SafeString::sanitise($_SERVER["SERVER_ADDR"]); ?></td>
                            </tr>
                            <tr>
                                <td>Domain Root</td><td><?php echo SafeString::sanitise($_SERVER["DOCUMENT_ROOT"]); ?></td>
                            </tr>
                            <tr>
                                <td>Server Admin</td><td><?php echo SafeString::sanitise($_SERVER["SERVER_ADMIN"]); ?></td>
                            </tr>
                            <tr>
                                <td>Disk Usage</td><td><?php echo Utils::friendlyFileSize(Server::disk()->used); ?> &#47; <?php echo Utils::friendlyFileSize(Server::disk()->total); ?></td>
                            </tr>
                            <?php
                            if (Server::memory()->total)
                            {
                                ?>
                                <tr>
                                    <td>Memory Usage</td><td><?php echo Utils::friendlyFileSize(Server::memory()->used); ?> &#47; <?php echo Utils::friendlyFileSize(Server::memory()->total); ?></td>
                                </tr>
                                <tr>
                                    <td>CPU Model</td><td><?php $cpu_models = Server::cpu()->names; $cpu_models = reset($cpu_models); echo SafeString::sanitise($cpu_models); ?></td>
                                </tr>
                                <tr>
                                    <td>CPU Cores</td><td><?php echo Server::cpu()->cores; ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </section>

            </div>

            <!-- End main section -->

            <?php } if ($active_section == "migrations") { ?>

            <!-- Begin migrations section -->

            <div id='migrations'>

                <?php
                /*
                 * If there's a migration notification, show a section at the
                 * top to house it
                 */
                if (isset($_GET["new_migration"]) OR isset($_GET["migrations_reset"]))
                {
                    ?>
                    <section>
                        <h2>Notification</h2>
                        <?php
                        /*
                         * Notification relating to migration creation attempt
                         */
                        if (isset($_GET["new_migration"]))
                        {

                            if ($_GET["new_migration"])
                            {

                                $migration_dir = mb_substr(Bourbon::getInstance()->getMigrationDirectory(), mb_strlen(Bourbon::getInstance()->getBaseDirectory()));

                                ?>
                                <p>Migration successfully created: <strong><?php echo "" . SafeString::sanitise($migration_dir . $_GET["new_migration"]); ?>.php</strong></p>
                                <?php

                            }

                            else
                            {
                                ?>
                                <p>An unknown error occurred when trying to create the migration</p>
                                <?php
                            }

                        }

                        /*
                         * Notification relating to migration index reset
                         */
                        if (isset($_GET["migrations_reset"]))
                        {
                            ?>
                            <p>Migration index successfully reset</p>
                            <?php
                        }
                        ?>
                      </section>
                      <?php
                  }
                ?>

                <section>
                    <h2>What Are Migrations?</h2>
                    <p>A migration is an action that can be used to upgrade or downgrade your application. If you have multiple developers working on an application and one of them makes a change to the database schema, they could create a migration that would action this change, allowing other developers to quickly and easily get up-to-date.</p>
                    <p>When you create a new migration a notification will let you know its filename. From there you have only to populate the file&#39;s <strong>up&#40;&#41;</strong> and <strong>down&#40;&#41;</strong> methods with the necessary actions to apply and undo the upgrade, respectively. You can also provide a description in the public <strong>$description</strong> property.</p>
                </section>

                <section>
                    <h2>Status</h2>
                    <?php
                    $migrations_enabled = Migration::isActive();
                    ?>
                    <p><span class='status <?php echo $migrations_enabled ? "" : "in"; ?>active'></span><?php echo $migrations_enabled ? "Enabled" : "Disabled"; ?></p>
                    <?php
                    if (!$migrations_enabled)
                    {
                        ?>
                        <p>The dashboard requires a database connection to work with migrations.</p>
                        <?php
                    }
                    ?>
                </section>

                <?php

                /*
                 * If migrations are enabled, show the rest of the page
                 */
                if ($migrations_enabled)
                {

                    ?>

                    <section>
                        <h2>Manage Migrations</h2>
                        <p><a class='confirm-link' href='<?php echo Dashboard::getLinkRoot() . Dashboard::getPath(); ?>?create_migration'>Create a new migration</a></p>
                        <p><a class='confirm-link' href='<?php echo Dashboard::getLinkRoot() . Dashboard::getPath(); ?>?reset_migrations'>Reset migration index</a></p>
                    </section>

                    <section id='migrations_list'>
                        <h2>Apply Migrations</h2>
                        <?php
                        /*
                         * Get a list of all migrations
                         */
                        $migrations = Migration::getAll();
                        /*
                         * Remember which migration was last actioned
                         */
                        try
                        {
                            $latest_migration = Migration::getLatest()->getId();
                        }
                        catch (Exception $exception)
                        {
                            $latest_migration = 0;
                        }
                        /*
                         * If only the origin exists, let us know
                         */
                        if (count($migrations) === 1)
                        {
                            ?>
                            <p>No migrations currently exist</p>
                            <?php
                        }
                        /*
                         * If we do have some migrations, carry on
                         */
                        else
                        {

                            foreach ($migrations as $migration)
                            {

                                $migration_name = ($migration->description != "") ? SafeString::sanitise($migration->description) : date("jS F Y, H:i:s", $migration->getId());

                                if ($migration->getId())
                                {
                                    $migration_description = ($migration->description != "") ? SafeString::sanitise($migration->description) : "";
                                }

                                else
                                {
                                    $migration_description = "Undo all migrations";
                                }

                                ?>
                                <p>
                                    <span class='pre'><?php echo (($latest_migration == $migration->getId()) ? "&raquo; " : "  "); ?></span>
                                    <a class='confirm-link' title='<?php echo $migration->getId() ? date("jS F Y, H:i:s", $migration->getId()) : $migration_description; ?>' href='<?php echo Dashboard::getLinkRoot() . Dashboard::getPath(); ?>?migrate_to=<?php echo $migration->getId(); ?>'><?php echo $migration_name; ?></a>
                                </p>
                                <?php
                            }

                        }
                        ?>
                    </section>

                    <?php
                }

                ?>

            </div>

            <!-- End migrations section -->

            <?php } if ($active_section == "cron") { ?>

            <!-- Begin cron section -->

            <div id='cron'>

                <section>
                    <h2>What Is Cron?</h2>
                    <p>If running on a *nix server, <strong>Cron</strong> can be used to schedule command-line tasks. <a href='http://en.wikipedia.org/wiki/Cron#Examples' target='_blank'>Wikipedia</a> explains it rather well.</p>
                    <p>Please note that cron jobs can only be set if the system user that the web server runs as has permission to do so &mdash; if a job cannot be created this is likely the reason why.</p>
                </section>

                <section>
                    <h2>Status</h2>
                    <?php
                    $cron_active = Cron::isActive();
                    ?>
                    <p><span class='status <?php echo $cron_active ? "" : "in"; ?>active'></span><?php echo $cron_active ? "Enabled" : "Disabled"; ?></p>
                </section>

                <?php
                if ($cron_active)
                {
                    ?>
                    <section>
                        <h2>Cron Jobs</h2>
                        <?php
                        /*
                         * Fetch a list of all cron jobs and sanitise them all
                         */
                        $cron_jobs = Cron::getAll();
                        $cron_jobs = SafeString::sanitise($cron_jobs);
                        ?>
                        <br />
                        <button id='cron_wget_button'>Create Job To Request URL</button>
                        <br />
                        <br />
                        <table id='cron_job_list'>
                            <form action='<?php echo Dashboard::getLinkRoot() . Dashboard::getPath(); ?>' method='POST'>
                                <thead>
                                    <tr>
                                        <th>Periodicity</th>
                                        <th>Command</th>
                                        <th>Action</th>
                                    </tr>
                                    <tr>
                                        <th>
                                            <input type='text' name='minute' placeholder='m' class='cron_period_input' />
                                            <input type='text' name='hour' placeholder='h' class='cron_period_input' />
                                            <input type='text' name='day' placeholder='d' class='cron_period_input' />
                                            <input type='text' name='month' placeholder='mth' class='cron_period_input' />
                                            <input type='text' name='day_of_week' placeholder='wk' class='cron_period_input' />
                                        </th>
                                        <th><input type='text' id='cron_new_command_input' name='command' placeholder='Command...' /></th>
                                        <th>
                                            <input name='cron_add' type='submit' value='Add' />
                                        </th>
                                    </tr>
                                </thead>
                            </form>
                          <tbody>
                              <?php
                              foreach ($cron_jobs as $cron_job)
                              {
                                  ?>
                                  <tr>
                                      <td><?php echo SafeString::sanitise($cron_job->getMinute()); ?> <?php echo SafeString::sanitise($cron_job->getHour()); ?> <?php echo SafeString::sanitise($cron_job->getDay()); ?> <?php echo SafeString::sanitise($cron_job->getMonth()); ?> <?php echo SafeString::sanitise($cron_job->getDayOfWeek()); ?></td>
                                      <td><?php echo SafeString::sanitise($cron_job->getCommand()); ?></td>
                                      <td>
                                          <form action='<?php echo Dashboard::getLinkRoot() . Dashboard::getPath(); ?>' method='POST' onSubmit='return confirm("Delete cron job?");'>
                                            <input type='hidden' name='cron_delete' value='<?php echo SafeString::sanitise($cron_job->getMinute()); ?> <?php echo SafeString::sanitise($cron_job->getHour()); ?> <?php echo SafeString::sanitise($cron_job->getDay()); ?> <?php echo SafeString::sanitise($cron_job->getMonth()); ?> <?php echo SafeString::sanitise($cron_job->getDayOfWeek()); ?> <?php echo SafeString::sanitise($cron_job->getCommand()); ?>' />
                                            <input type='submit' value='Del' />
                                          </form>
                                      </td>
                                  </tr>
                                  <?php
                              }
                              ?>
                          </tbody>
                        </table>
                    </section>
                    <?php
                }
                ?>

            </div>

            <!-- End cron section -->

            <?php } ?>

            <script>

                var links = document.getElementsByClassName('confirm-link');
                
                for (var i = 0; i < links.length; i++)
                {
                    links[i].addEventListener('click', function(event)
                                                       {

                                                           if (!confirm('Are you sure?'))
                                                           {
                                                              event.preventDefault();
                                                           }

                                                       }, false);
                }

                if (document.getElementById('cron_wget_button') !== null)
                {

                    document.getElementById('cron_wget_button').addEventListener('click', function()
                    {

                        var url = prompt('URL to request:');

                        if (url)
                        {
                            document.getElementById('cron_new_command_input').value = 'wget -O - -q -t 1 ' + url;
                        }

                    });

                }

            </script>

        </body>

    </html>

    <?php

    /*
     * Exit the script so that nothing further loads
     */
    exit;

}