<?php

/**
 * The way to run this is:
 * php updateCSV.php 2> convert.log
 * You'll need to change the include file to find the right config file
 * as well as the path to I2CE which may not work right using the one
 * from the config file.
 * The ID for the User object should be valid in your user table.
 * Am only trying to update the  registration date, license Start and End dates
 */

global $dictionary;
$dictionary = array();

define('iHRIS_DHIS2_FACILITY_ID', 0);
define('iHRIS_HRIS_FACILITY_ID', 1);

$i2ce_site_user_access_init = null;
$script = array_shift($argv);
if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/local' . DIRECTORY_SEPARATOR . 'config.values.php')) {
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/local' . DIRECTORY_SEPARATOR . 'config.values.php';
} else {
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/config.values.php';
}

$i2ce_site_i2ce_path = "/var/lib/iHRIS/releases/4.2/i2ce";

require_once $i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'I2CE_config.inc.php';

@I2CE::initializeDSN($i2ce_site_dsn, $i2ce_site_user_access_init, $i2ce_site_module_config);

unset($i2ce_site_user_access_init);
unset($i2ce_site_dsn);
unset($i2ce_site_i2ce_path);
unset($i2ce_site_module_config);

global $user;

$user = new I2CE_User(1, false, false, false);
//$db = I2CE::PDO();
//$db = MDB2::singleton();
if (PEAR::isError($db)) {
	die($db->getMessage());
}
$form_factory = I2CE_FormFactory::instance();

echo "Memory Limit: " . ini_get("memory_limit") . "\n";
echo "Execution Time: " . ini_get("max_execution_time") . "\n";

function dotrim(&$value) {
	$value = trim($value);
}

function rearrange($arr1) {
	$arr2 = array();
	foreach (array_keys($arr1) as $k) {
		$id = explode('|', $arr1[$k]['value'], 2);
		$id2 = ($id[1]);
		$arr2[$id2] = &$arr1[$k]['display'];
	}
	return $arr2;
}

$cache = array();
$cache['facility'] = array_flip(rearrange(I2CE_List::listOptions("facility")));

$fh = fopen($argv[0], "r");
if ($fh === false) {
	die("Couldn't update file: $argv[0].  Syntax: mapDHIS2toiHRIS.php file.csv\n");
}

$row = 0;
$skip_no_reg = 0;
while (($data = fgetcsv($fh)) !== false) {

	array_walk($data, "dotrim");
	$row++;

	if ($data[iHRIS_HRIS_FACILITY_ID] == "") {
		echo "Couldn't do anything for " . $data[iHRIS_HRIS_FACILITY_ID]
			. " because no facility ID!\n";
		$skip_no_reg++;
		continue;
	}

	if ($data[iHRIS_HRIS_FACILITY_ID] != "" && $data[iHRIS_DHIS2_FACILITY_ID] != "") {
		$facility = $form_factory->createContainer($data[iHRIS_HRIS_FACILITY_ID]);
		$facility->populate();
		$facility->getField('dhis_orgunit')->setFromDB($data[iHRIS_DHIS2_FACILITY_ID]);
		$facility->save($user);
	}
	echo "Facility name " . $facility->getField('name')->getDBValue();
	echo "Facility dhis_orgunit " . $facility->getField('dhis_orgunit')->getDBValue();
	echo "Row $row: Updated Facility ID " . $facility->getId() . "\n";
	$facility->cleanup();
	unset($facility);
}
fclose($fh);
echo "DONE \n";

?>
