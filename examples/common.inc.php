<?php

/**
 * Yahoo! PHP5 SDK
 *
 *  * Yahoo! Query Language
 *  * Yahoo! Social API
 *
 * Find documentation and support on Yahoo! Developer Network: http://developer.yahoo.com
 *
 * Hosted on GitHub: http://github.com/yahoo/yos-social-php5/tree/master
 *
 * @package    yos-social-php5
 * @subpackage yahoo
 *
 * @author     Dustin Whittle <dustin@yahoo-inc.com>
 * @copyright  Copyrights for code authored by Yahoo! Inc. is licensed under the following terms:
 * @license    BSD Open Source License
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *   THE SOFTWARE.
 **/

/*
 * Make sure you obtain oauth keys before continuing by visiting: http://developer.yahoo.com/dashboard
 */

# openid/oauth credentials
define('OAUTH_CONSUMER_KEY', '###');
define('OAUTH_CONSUMER_SECRET', '###');
define('OAUTH_DOMAIN', '###');
define('OAUTH_APP_ID', '###');

# date time
date_default_timezone_set('America/Los_Angeles');

# session storage
ini_set('session.save_handler', 'files');
session_save_path('/tmp/');
session_start();

# utf8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
ob_start('mb_output_handler');

# debug settings
error_reporting(E_ALL); #  | E_STRICT -- hide strict as openid lib is verbose
ini_set('display_errors', true);

# set include path (required for openid, oauth, opensocial libs)
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/../lib/OpenID/'.PATH_SEPARATOR.dirname(__FILE__).'/../lib/');

/**
 * OAuth - http://oauth.googlecode.com/svn/code/php/
 */
require_once 'OAuth/OAuth.php';


/**
 * OpenID - http://www.openidenabled.com/php-openid/
 */

/**
 * Require OpenID consumer.
 */
require_once "Auth/OpenID/Consumer.php";

/**
 * Require OpenID filestore.
 */
require_once "Auth/OpenID/FileStore.php";

/**
 * Require SReg Extension.
 */
require_once "Auth/OpenID/SReg.php";

/**
 * Require PAPE extension.
 */
require_once "Auth/OpenID/PAPE.php";


/**
 * Yahoo! PHP5 SDK - http://github.com/yahoo/yos-social-php5/tree/master
 */

/**
 * Require Yahoo! PHP5 SDK libraries
 */
require_once 'Yahoo/YahooOAuthApplication.class.php';

header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
