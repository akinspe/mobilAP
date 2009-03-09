<?php

/*

* Copyright (c) 2008, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

ini_set('display_errors', 'off');
set_magic_quotes_runtime(0);

if (get_magic_quotes_gpc()) {
    function stripslashes_deep($value)
    {
        $value = is_array($value) ?
                    array_map('stripslashes_deep', $value) :
                    stripslashes($value);

        return $value;
    }

    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}

$_DBCONFIG = array(
    'db_host'=>'localhost',
    'db_user'=>'mobilAP',
    'db_password'=>'mobilAP',
    'db_database'=>'mobilAP',
);

$_BASECONFIG = array (
    'default_password'=>'mobilAP', //in truth there's always passwords, this is used if you choose not to use passwords. i.e. everyone has the same password
    'MYSQL_BIN_FOLDER'=>'/usr/local/mysql/bin/', //set to the path of the mysql binary. Used for SQL exports
    'DEFAULT_LOGIN_URL'=>'index.php', 
    'DEFAULT_LOGOUT_URL'=>'index.php',
    'thumb_width'=>150, // directory thumbnail width
    'thumb_height'=>150 // directory thumbnail height
);

/* CONFIGURATION VARIABLES
	These values are overwritten in the database config table. The values here are defaults. You can reset
	to the defaults using the admin interface
*/

$_CONFIG = array(
    'USE_PASSWORDS'=>false,
    'SHOW_ATTENDEE_DIRECTORY'=>true, 
    'SHOW_AD_PHOTOS'=>true,
    'SHOW_AD_TITLE'=>true,
    'SHOW_AD_ORG'=>true,
    'SHOW_AD_DEPT'=>true,
    'SHOW_AD_EMAIL'=>true,
    'SHOW_AD_PHONE'=>false,
    'SHOW_AD_LOCATION'=>false,
    'SHOW_AD_BIO'=>true,
);

define('TABLE_PREFIX', '');

function getDBConfig($var)
{
	global $_DBCONFIG;
	return isset($_DBCONFIG[$var]) ? $_DBCONFIG[$var] : null;
}

function getConfig($var)
{
	global $_BASECONFIG, $_CONFIG;
	$config = array_merge($_BASECONFIG, $_CONFIG, mobilAP::getConfigs());
	return isset($config[$var]) ? $config[$var] : null;
}
