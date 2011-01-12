<?php

require_once('../../mobilAP.php');

$timezone_file = 'timezones.sqlite';
$data = array();

$continent = isset($_GET['continent']) ? $_GET['continent'] : '';
$area = isset($_GET['area']) ? $_GET['area'] : '';
$encode = true;

try {
	$conn = new PDO('sqlite:' . $timezone_file);
	$field = 'continent';
	$params = array();
	$where = '';
	if ($continent) {
		$field = 'area';
		$where = " WHERE continent=?";
		$params = array($continent);
		if ($area) {
			$field = 'detail';
			$where = " WHERE area=? AND LENGTH(detail)>0";
			$params = array($area);
		}
	}
	
	$sql = sprintf("SELECT DISTINCT %s FROM %s%s ORDER BY %s", $field, 'timezones', $where, $field);
	if ($stmt = $conn->prepare($sql)) {
		if ($stmt->execute($params)) {
			while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
				$data[] = $row[0];
			}
		}
	}
	
} catch (Exception $error) {
	if ($continent) {
		if ($area) {
            $file = sprintf("./timezones/%s-%s.json", $continent, $area);
            if (!file_exists($file)) {
                $file = './timezones/empty.json';
            }
		} else{
	        $file = sprintf("./timezones/%s.json", $continent);
	    }
	} else {
	    $file = './timezones/continents.json';
	}

    if (file_exists($file)) {
        $data = file_get_contents($file);
        $encode = false;        
    } else {
        $data = new MobilAP_Error("Unable to locate timezone file $file");
    }
}


header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo $encode ? json_encode($data) : $data;

?>