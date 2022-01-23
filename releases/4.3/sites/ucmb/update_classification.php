<?php

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
if (PEAR::isError($db)) {
	die($db->getMessage());
}

$form_factory = I2CE_FormFactory::instance();

echo "Memory Limit: " . ini_get( "memory_limit" ) . "\n";
echo "Execution Time: " . ini_get( "max_execution_time" ) . "\n";
function rearrange($arr1){
    $arr2 = array();
    foreach(array_keys($arr1) as $k) {
        $id = explode( '|', $arr1[$k]['value'], 2 );
        $id2 = ($id[1]);
        $arr2[$id2] = &$arr1[$k]['display'];
    }
    return $arr2;
}	



		$find_job = array(
			'operator' => 'FIELD_LIMIT',
			'style' => 'equals',
			'field' => 'job',
	
		);
		$jobs = I2CE_FormStorage::search( "job", false, $find_job, array(), true );

	//   $cache = array();
	//   $cache['job'] = array_flip( rearrange(I2CE_List::listOptions( "job" ) ));

      $count =0;
	 
                
        foreach ($jobs as $job){               //
 
           
		        // $job = $form_factory->createContainer( 'job'.'|'.$id );
		        // $job->populate();
				// $classification_id = 'classification|3';
                // $job->getField('classification')->setFromDB($classification_id);
			    // $job->save( $user );
			    // $job->cleanup();
				print_r($job);
		    	 

		}
			
        		$count++;

          
	    

			  
	    
	

//echo "DONE  ". $count." records have position information updated  ";

?>
