<?php

/* 
	mobilAP will parse this file for database configuration information 
	if you do not permit the webserver to write to this file you can edit it manually
*/


$_DBCONFIG = array(
	'db_type'=>'', // should be either 'mysql' or 'sqlite'
	'db_host'=>'', // set to MySQL host 
	'db_user'=>'', // set to MySQL user
	'db_password'=>'', // set to MySQL password
	'db_database'=>'', // set to MySQL database
	'db_folder'=>'' // folder to store the SQLite database. Defaults to mobilAP/data folder. The webserver must be able to write to this folder.
);
	
?>