<?php

/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

//where in the filesystem are we
define('MOBILAP_BASE', realpath(dirname(__FILE__)));
//try to figure out the base URL. If DOCUMENT_ROOT isn't set, then this probably isn't going to work. Apache is fine, other servers???
define('MOBILAP_URL_BASE', isset($_SERVER['DOCUMENT_ROOT']) ? substr(MOBILAP_BASE, strlen($_SERVER['DOCUMENT_ROOT'])) : '/');

//set include path
ini_set('include_path', '.' . PATH_SEPARATOR . MOBILAP_BASE .'/mobilAP');
ini_set('display_errors', 'off');


/* some compatability checks */
if (!function_exists('json_encode')) {
    require_once('classes/JSON/JSON.php');
}

require_once('classes/mobilAP.php');
@include_once(mobilAP::dbConfigFile());
require_once('classes/mobilAP_utils.php');
require_once('classes/mobilAP_db.php');
require_once('classes/mobilAP_user.php');
require_once('classes/Debug.php');

?>