<?php

/**
 * The best way to run this is:
 * php import_facility_ug.php 2> convert.log
 * There's lots of notice messages you probably want to ignore for the most
 * part.
 * You'll need to change the include file to find the right config file
 * as well as the path to I2CE which may not work right using the one
 * from the config file.
 * The ID for the User object should be valid in your user table.
 * The $forms array is an associative array with the value being
 * an array of forms that are required for the given form to work e.g. 
 * region needs country first since it uses country as a map for a field.
 *
 * 
 *
 */
 global $dictionary;
$dictionary = array();
 
 
//define( 'iHRIS_DEFAULT_COUNTRY', 'Uganda' );

define( 'iHRIS_ESTABLISHMENT', 0 );
define( 'iHRIS_FACILITY', 1 );
define( 'iHRIS_JOB', 2 );
define( 'iHRIS_AMOUNT', 3 );



 $i2ce_site_user_access_init = null;
$script = array_shift( $argv );
if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/local' . DIRECTORY_SEPARATOR . 'config.values.php')) {
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/local' . DIRECTORY_SEPARATOR . 'config.values.php');
} else {
	require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/config.values.php');
}

$i2ce_site_i2ce_path = "/var/lib/iHRIS/releases/4.3/i2ce";

require_once ($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'I2CE_config.inc.php');

@I2CE::initializeDSN($i2ce_site_dsn,   $i2ce_site_user_access_init,    $i2ce_site_module_config);

unset($i2ce_site_user_access_init);
unset($i2ce_site_dsn);
unset($i2ce_site_i2ce_path);
unset($i2ce_site_module_config);



global $user;

$user = new I2CE_User(1, false, false, false);
//$db = MDB2::singleton();
$db = I2CE::PDO();
if ( PEAR::isError( $db ) ) {
	die( $db->getMessage() );
}
$form_factory = I2CE_FormFactory::instance();

echo "Memory Limit: " . ini_get( "memory_limit" ) . "\n";
echo "Execution Time: " . ini_get( "max_execution_time" ) . "\n";

function dotrim(&$value){
  $value = trim($value);
}

$fh = fopen( $argv[0], "r" );
if ( $fh === false ) {
    die( "Couldn't update file: $argv[0].  Syntax: import_facility_ug.php file.csv\n" );
}



$row = 0;
$Skip_no_facilty = 0;
$no_facility = 0;
while ( ( $data = fgetcsv( $fh ) ) !== false ) {

    array_walk( $data, "dotrim" );
	    // Creating New Establishment
		$row++;
		if( $data[iHRIS_ESTABLISHMENT] != "" ) 
		{
		   
		  
			    $facility_obj = $form_factory->createContainer("establishment");
			    $facility_obj->getField("establishment_period")->setFromDB($data[iHRIS_ESTABLISHMENT]);
			    $facility_obj->getField("job_cadre")->setFromDB($data[iHRIS_JOB]);
			    $facility_obj->getField("location")->setFromDB( $data[iHRIS_FACILITY] );
			    $facility_obj->amount = $data[iHRIS_AMOUNT];
			    
			    $facility_obj->save( $user );
			
			    $no_facility++;
		   
		} else 
		{
		   $Skip_no_facilty++;
		}
	}
fclose($fh);
echo "\n Created $no_facility Establisments  \n Skipped $Skip_no_facilty because of no Establisment .\n";

?>
