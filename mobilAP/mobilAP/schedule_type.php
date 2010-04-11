<?php

/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

require_once('../mobilAP.php');

$product = isset($_GET['product']) ? $_GET['product'] : 'safari';

switch ($product) 
{
    case 'safari':
        $data = array(
            array('label'=>'List','value'=>'scheduleList'),
            array('label'=>'Calendar','value'=>'scheduleMonth'),
        );
        break;
        
    case 'mobile':
        $data = array(
            array('label'=>'List','value'=>'scheduleList'),
            array('label'=>'Day','value'=>'scheduleDay'),
            array('label'=>'Month','value'=>'scheduleMonth')
        );
        break;
}

header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>