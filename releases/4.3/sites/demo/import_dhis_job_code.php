<?php

/**
 * The best way to run this is:
 * php importCSV.php 2> convert.log
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





define( 'iHRIS_JOB', 0);
define( 'iHRIS_DHIS_JOB',1 );




$i2ce_site_user_access_init = null;
$script = array_shift( $argv );
if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/local' . DIRECTORY_SEPARATOR . 'config.values.php')) {
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/local' . DIRECTORY_SEPARATOR . 'config.values.php');
} else {
	require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/config.values.php');
}

$i2ce_site_i2ce_path = "/var/lib/iHRIS/releases/4.2/i2ce";

require_once ($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'I2CE_config.inc.php');

@I2CE::initializeDSN($i2ce_site_dsn,   $i2ce_site_user_access_init,    $i2ce_site_module_config);

unset($i2ce_site_user_access_init);
unset($i2ce_site_dsn);
unset($i2ce_site_i2ce_path);
unset($i2ce_site_module_config);


global $user;

$user = new I2CE_User(1, false, false, false);
$db = PDO::singleton();
if ( PEAR::isError( $db ) ) {
	die( $db->getMessage() );
}
$form_factory = I2CE_FormFactory::instance();

echo "Memory Limit: " . ini_get( "memory_limit" ) . "\n";
echo "Execution Time: " . ini_get( "max_execution_time" ) . "\n";

 



$fh = fopen( $argv[0], "r" );
if ( $fh === false ) {
    die( "Couldn't update file: $argv[0].  Syntax: importCSV.php [erase] file.csv\n" );
}
$row = 0;
$skip_no_post = 0;
$not = 0;
while ( ( $data = fgetcsv( $fh ) ) !== false ) {

   		$find_job = array(
                                         'operator' => 'FIELD_LIMIT',
                                         'style' => 'equals',
                                         'field' => 'id',
                                         'data' => array(
                                                         'value' => $data[iHRIS_JOB],
                                      
                                                )
                                  );
                $job_id = I2CE_FormStorage::search( "job", false, $find_job, array(), true );
		if($job_id){
		$job = $form_factory->createContainer( 'job|'. $data[iHRIS_JOB] );
                $job->populate();
                $job->code = $data[iHRIS_DHIS_JOB];
		$job->save( $user );
		$job->cleanup();
                unset( $job );

		$row++;
                }else{

		$not++;
		}
}
fclose($fh);
echo "Done ".$row++." Records.\n";
echo "Skipped ".$not++." Records.\n";
?>
