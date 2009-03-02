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
    'db_database'=>'mobilAP',
    'default_password'=>'mobilAP',
    'DEFAULT_LOGIN_URL'=>'/',
    'DEFAULT_LOGOUT_URL'=>'/'
);

function getConfig($var)
{
	global $_CONFIG;
	return isset($_CONFIG[$var]) ? $_CONFIG[$var] : null;
}