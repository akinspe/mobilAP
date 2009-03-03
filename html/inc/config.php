<?php

/*

* Copyright (c) 2008, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

//ini_set('display_errors', 'off');
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


$_CONFIG = array(
    'db_host'=>'localhost',
    'db_user'=>'mobilAP',
    'db_password'=>'mobilAP',
    'db_database'=>'mobilAP_test',
	'use_passwords' =>true, //false means no password, true means password
    'default_password'=>'mobilAP', //in truth there's always passwords, this is used if you choose not to use passwords. i.e. everyone has the same password
    'MYSQL_BIN_FOLDER'=>'/usr/local/mysql/bin/', //this is if you use a MySQL package
//  'MYSQL_BIN_FOLDER'=>'/usr/bin/' //this is for OS X Server (by default)
    'DEFAULT_LOGIN_URL'=>'index.php',
    'DEFAULT_LOGOUT_URL'=>'index.php'
);

define('TABLE_PREFIX', '');

function getConfig($var)
{
	global $_CONFIG;
	return isset($_CONFIG[$var]) ? $_CONFIG[$var] : null;
}