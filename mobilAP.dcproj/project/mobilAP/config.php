<?php

require_once('../mobilAP.php');

if (isset($_POST['post'])) {
    $post_action = $_POST['post'];
    switch ($post_action)
    {
        case 'saveDB':
            $dbconfig = isset($_POST['dbconfig']) ? $_POST['dbconfig'] : array();
            $data = true;
            foreach ($dbconfig as $var=>$value) {
                $result = mobilAP::setDBConfig($var, $value);
                if (mobilAP_Error::isError($result)) {
                    $data = $result;
                    break;
                }
            }

            $data = mobilAP_db::testConnection(mobilAP::getDBConfig('db_type'), mobilAP::getDBConfig('db_host'), mobilAP::getDBConfig('db_username'), mobilAP::getDBConfig('db_password'), mobilAP::getDBConfig('db_database'));
            if (!mobilAP_Error::isError($data)) {
                $data = mobilAP_db::createTables();
            }

            break;
        case 'save':
            $config = isset($_POST['config']) ? $_POST['config'] : array();
            $data = true;
            foreach ($config as $type=>$vars) {
                foreach ($vars as $var=>$value) {
                    $result = mobilAP::setConfig($var, $value, $type);
                    if (mobilAP_Error::isError($result)) {
                        $data = $result;
                        break;
                    }
                }
            }
            break;
        case 'dbtest':

            $db_type = isset($_POST['db_type']) ? $_POST['db_type'] : '';
            $db_host = isset($_POST['db_host']) ? $_POST['db_host'] : '';
            $db_username = isset($_POST['db_username']) ? $_POST['db_username'] : '';
            $db_password = isset($_POST['db_password']) ? $_POST['db_password'] : '';
            $db_database = isset($_POST['db_database']) ? $_POST['db_database'] : '';

            $data = mobilAP_db::testConnection($db_type, $db_host, $db_username, $db_password, $db_database);
            break;
    }
} else {
    $data = mobilAP::getConfigs();
}

header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>