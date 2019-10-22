<?php


namespace Whiskey\Bourbon\Config;


use Whiskey\Bourbon\Config\Type\General;
use Itg\ErrorReport;


$config = new General();


/*
 * Miscellaneous application settings
 */
$config->set('site_name', 'ITG - Dunelm');


/*
 * Define a key for use in hashing and encryption -- this should be unique for
 * each project
 */
$config->set('project_key', '8c730f0ebc35aaab3d252fba8fe926fd33582cfe473d60f8b904b7b898585ebebdb879dac0432526876789d56562cc8a29640421365df678761a8af386949f2b');


/*
 * Local timezone (consult http://php.net/manual/en/timezones.php if unsure)
 */
$config->set('timezone', 'Europe/London');


/*
 * Name of application environment
 */
$config->set('environment', $_ENV['APP_ENVIRONMENT']);


/*
 * Whether to enable debug settings if not in the production environment
 */
$config->set('debug', $_ENV['APP_DEBUG']);


/*
 * Register the ITG API error logger
 */
if ($_ENV['APP_ENVIRONMENT'] == 'production' AND isset($_ENV['ITG_API_KEY']))
{
    ErrorReport::init($_ENV['ITG_API_KEY']);
}


/*
 * Give us more memory and time to play with
 */
ini_set('memory_limit',      '256M');
ini_set('max_execution_time', 300);


return $config;