<?php

$i2ce_site_user_access_init = null;
$script = array_shift( $argv );
if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'local' . DIRECTORY_SEPARATOR . 'config.values.php')) {
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'local' . DIRECTORY_SEPARATOR . 'config.values.php');
} else {
	require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.values.php');
}

$i2ce_site_i2ce_path = "/var/lib/iHRIS-Uganda/releases/4.3/i2ce";

require_once ($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'I2CE_config.inc.php');

@I2CE::initializeDSN($i2ce_site_dsn,   $i2ce_site_user_access_init,    $i2ce_site_module_config);

unset($i2ce_site_user_access_init);
unset($i2ce_site_dsn);
unset($i2ce_site_i2ce_path);
unset($i2ce_site_module_config);


global $user;

$user = new I2CE_User(1, false, false, false);
$db = I2CE::PDO();
if ( PEAR::isError( $db ) ) {
	die( $db->getMessage() );
}
$form_factory = I2CE_FormFactory::instance();

echo "Memory Limit: " . ini_get( "memory_limit" ) . "\n";
echo "Execution Time: " . ini_get( "max_execution_time" ) . "\n";
//get person position from person position
$person_position = I2CE_FormStorage::listFields( "person_position", false, false, array(), true ); 
	$count=0;
	$skipped=0;
	foreach ($person_position as $id) {
		//print_r($id[0]);
    		 $person_position = $form_factory->createContainer('person_position|' . $id[0]);
   		 $person_position->populate();
		 $position = $person_position->getField('position')->getDBValue();
		 $dutystation = $person_position->getField('duty_station')->getDisplayValue();
		 if($dutystation){
			$skipped++;
		 }else{
		 $position = $form_factory->createContainer($position);
   		 $position->populate();
		 $facility = $position->getField('facility')->getDBValue();
		 //I2CE::raiseError("Displaying " . $facility);
		 $person_position->getField('duty_station')->setFromDB( $facility );
    		 $person_position->save( $user );
		 $person_position->cleanup();
	    	 unset( $person_position );
		 $position->cleanup();
	    	 unset( $position );
		 $count++;
	
		 continue; 
		}
		}
	echo "DONE  ". $count." records have position information updated";
	echo "DONE  ". $skipped." records had duty station updated";
?>
