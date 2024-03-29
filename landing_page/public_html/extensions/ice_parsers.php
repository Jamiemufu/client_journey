<?php


use Whiskey\Bourbon\App\Facade\Ice;


/*
 * Any custom Ice parsers should reside here
 */

/*
 * CSRF token
 * {csrf}
 */
Ice::registerParser(function($html)
{
    return str_replace('{csrf}', '{{ \Whiskey\Bourbon\App\Facade\Csrf::generateToken() }}', $html);
});


/*
 * CAPTCHA
 * {captcha}
 */
Ice::registerParser(function($html)
{
    return str_replace('{captcha}', '{{ \Whiskey\Bourbon\App\Facade\Captcha::display() }}', $html);
});


/*
 * 'Lorem ipsum' text
 * {loremipsum:45}
 */
Ice::registerParser(function($html)
{
    return preg_replace('/(\s*){loremipsum\:(.*)}(\s*)/', '$1{{ \Whiskey\Bourbon\App\Facade\Utils::loremIpsum($2)$3 }}', $html);
});