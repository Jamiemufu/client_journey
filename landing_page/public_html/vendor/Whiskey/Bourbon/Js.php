<?php


namespace Whiskey\Bourbon;


use Whiskey\Bourbon\App\Bootstrap as Bourbon;
use Whiskey\Bourbon\App\Http\Response;
use Whiskey\Bourbon\App\Facade\Router;


/**
 * Js class
 * @package Whiskey\Bourbon
 */
class Js
{


    protected static $_js_path   = '_whsky/scripts.min.js';
    protected static $_link_root = null;


    /**
     * Get the URL fragment to access the dashboard
     * @param  bool   $full Whether to include the link root
     * @return string       URL path
     */
    public static function getPath($full = true)
    {

        return ($full ? self::getLinkRoot() : '') . static::$_js_path;

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


}


/*

    // Any changes to the below should be minified and placed after
    // var _whsky=' . json_encode($_whsky_js_info) . '; in the response body.
    //
    // Please note that, in the code '[].slice.call(arguments)', the 'arguments'
    // variable will need to be changed from its minified name back to
    // 'arguments', as this is a reserved word which minifiers do not appear to
    // take into account.
    //
    // Please also note that the double-backslashes in the default controller
    // namespace will need to be replaced with quadruple-backslashes once
    // pasted into the response body, as well as in the call to lTrim() in
    // checkController().

    _whsky.lTrim = function(string, substring)
    {

        var temp_result = string;

        if (temp_result.substr(0, substring.length) == substring)
        {
            temp_result = temp_result.slice(substring.length);
        }

        if (temp_result != string)
        {
            temp_result = _whsky.lTrim(temp_result, substring);
        }

        return temp_result;

    };


    _whsky.rTrim = function(string, substring)
    {

        var temp_result = string;

        if (temp_result.substr(0 - substring.length) == substring)
        {
            temp_result = temp_result.slice(0, (0 - substring.length));
        }

        if (temp_result != string)
        {
            temp_result = _whsky.rTrim(temp_result, substring);
        }

        return temp_result;

    };

    _whsky.checkController = function(controller)
    {

        var found_match = false;
        controller      = _whsky.lTrim(controller, '\\');

        for (var url in _whsky.routes)
        {

            if (_whsky.routes.hasOwnProperty(url))
            {

                for (http_method in _whsky.routes[url])
                {

                    if (_whsky.routes[url].hasOwnProperty(http_method))
                    {

                        var route = _whsky.routes[url][http_method];

                        if (controller.toLowerCase() == route.controller.toLowerCase())
                        {
                            found_match = true;
                        }

                    }

                }

            }

        }

        if (!found_match)
        {
            controller = 'Whiskey\\Bourbon\\App\\Http\\Controller\\' + controller;
        }

        return controller;
    
    };

    _whsky.link = function()
    {

        var arguments  = [].slice.call(arguments);
        var controller = arguments.shift();
        var action     = arguments.shift().toLowerCase();
        var slugs      = arguments;

        controller = _whsky.checkController(controller).toLowerCase();

        for (var url in _whsky.routes)
        {

            if (_whsky.routes.hasOwnProperty(url))
            {

                for (http_method in _whsky.routes[url])
                {

                    if (_whsky.routes[url].hasOwnProperty(http_method))
                    {

                        var route            = _whsky.routes[url][http_method];
                        var route_controller = route.controller.toLowerCase();
                        var route_action     = route.action.toLowerCase();
                        var route_slugs      = slugs;

                        if (route_controller != '' && route_action != '' && route_controller == controller && route_action == action)
                        {

                            var route_url     = '';
                            var url_fragments = _whsky.rTrim(_whsky.lTrim(route.url, '/'), '/');
                            url_fragments     = url_fragments.split('/');

                            for (var fragment in url_fragments)
                            {

                                if (url_fragments.hasOwnProperty(fragment))
                                {

                                    route_url += '/';

                                    if (url_fragments[fragment] == '*' || (typeof _whsky.regexes[url_fragments[fragment]] != 'undefined'))
                                    {
                                        route_url += encodeURIComponent(route_slugs.shift());
                                    }

                                    else if (url_fragments[fragment] == ':')
                                    {
                                        route_url += route_slugs.map(encodeURIComponent).join('/');
                                    }

                                    else
                                    {
                                        route_url += encodeURIComponent(url_fragments[fragment]);
                                    }

                                }

                            }

                            route_url = _whsky.rTrim(_whsky.link_root, '/') + '/' + _whsky.lTrim(route_url, '/');

                            return route_url;

                        }

                    }

                }

            }

        }

        return '';

    };

 */


/*
 * Check to see if we've requested this page
 */
if (Router::isCurrentUrl(Js::getPath(false)))
{

    $response = Instance::_retrieve(Response::class);

    /*
     * Set a JavaScript content type
     */
    $response->setContentType('javascript');

    /*
     * Compile an array of information that will be required by the JavaScript
     * library
     */
    $_whsky_js_info               = [];
    $_whsky_js_info['link_root']  = Bourbon::getInstance()->getLinkRootPath();
    $_whsky_js_info['public_dir'] = Bourbon::getInstance()->getPublicPath();
    $_whsky_js_info['image_dir']  = Bourbon::getInstance()->getPublicPath('images');
    $_whsky_js_info['css_dir']    = Bourbon::getInstance()->getPublicPath('css');
    $_whsky_js_info['js_dir']     = Bourbon::getInstance()->getPublicPath('js');
    $_whsky_js_info['routes']     = Router::getAll(true, true);
    $_whsky_js_info['regexes']    = Router::getRegexes();

    /*
     * Set and output the body
     */
    $response->body = 'var _whsky=' . json_encode($_whsky_js_info) . ';_whsky.lTrim=function(r,t){var e=r;return e.substr(0,t.length)==t&&(e=e.slice(t.length)),e!=r&&(e=_whsky.lTrim(e,t)),e},_whsky.rTrim=function(r,t){var e=r;return e.substr(0-t.length)==t&&(e=e.slice(0,0-t.length)),e!=r&&(e=_whsky.rTrim(e,t)),e},_whsky.checkController=function(r){var t=!1;r=_whsky.lTrim(r,"\\\\");for(var e in _whsky.routes)if(_whsky.routes.hasOwnProperty(e))for(http_method in _whsky.routes[e])if(_whsky.routes[e].hasOwnProperty(http_method)){var o=_whsky.routes[e][http_method];r.toLowerCase()==o.controller.toLowerCase()&&(t=!0)}return t||(r="Whiskey\\\\Bourbon\\\\App\\\\Http\\\\Controller\\\\"+r),r},_whsky.link=function(){var r=[].slice.call(arguments),t=r.shift(),e=r.shift().toLowerCase(),o=r;t=_whsky.checkController(t).toLowerCase();for(var s in _whsky.routes)if(_whsky.routes.hasOwnProperty(s))for(http_method in _whsky.routes[s])if(_whsky.routes[s].hasOwnProperty(http_method)){var h=_whsky.routes[s][http_method],n=h.controller.toLowerCase(),i=h.action.toLowerCase(),w=o;if(""!=n&&""!=i&&n==t&&i==e){var y="",_=_whsky.rTrim(_whsky.lTrim(h.url,"/"),"/");_=_.split("/");for(var k in _)_.hasOwnProperty(k)&&(y+="/",y+="*"==_[k]||"undefined"!=typeof _whsky.regexes[_[k]]?encodeURIComponent(w.shift()):":"==_[k]?w.map(encodeURIComponent).join("/"):encodeURIComponent(_[k]));return y=_whsky.rTrim(_whsky.link_root,"/")+"/"+_whsky.lTrim(y,"/")}}return""};';

    $response->output();

    exit;

}