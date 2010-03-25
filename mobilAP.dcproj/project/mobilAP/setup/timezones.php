<?php

require_once('../../mobilAP.php');

$timezone_file = 'timezones.sqlite';
$data = array();

$continent = isset($_GET['continent']) ? $_GET['continent'] : '';
$area = isset($_GET['area']) ? $_GET['area'] : '';

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
	$data = new MobilAP_Error($error->getMessage(), $error->getCode(), $error);
}


header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>