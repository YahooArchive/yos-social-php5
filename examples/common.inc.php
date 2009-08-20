<?php

# session storage
ini_set('session.save_handler', 'files');
session_save_path('/tmp/');
session_start();

# utf8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
ob_start('mb_output_handler');

# debug settings
error_reporting(E_ALL); # | E_STRICT


# set include path (required for openid libs)
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/../../lib/openid/'.PATH_SEPARATOR.dirname(__FILE__).'/../../lib/');

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
 * Require OAuth extension.
 */
require_once "Auth/OpenID/OAuth.php";


/**
 * Require Yahoo! PHP5 SDK libraries
 */
require_once 'Yahoo/YahooOAuthApplication.class.php';

