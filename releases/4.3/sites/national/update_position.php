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

        $find_id = array(
                    'operator' => 'FIELD_LIMIT',
                    'field' => 'position',
                    'style' => 'null'
                    );

        $position_id = I2CE_FormStorage::listFields( "position", array('id'), $find_id );
        
     if (!isset($position_id)){
        echo "No Ids \n";
      }else{
                $count = 0;
                
        foreach ( $position_id as $id=>$value  ){               //
 
           
		        $position = $form_factory->createContainer( 'position'.'|'.$id );
		        $position->populate();
                        $facility_id = $position->getField("facility")->getDBValue();
			$facility = $form_factory->createContainer( $facility_id );
			$facility->populate();
			$facility_id = $facility->getField("facility_type")->getDBValue();

			if( $facility_id == 'facility_type|DHO'){
			   //echo $position->getField("facility_office")->getDBValue();
			   $facility_office_id = "facility_office|1";
		           $position->getField('facility_office')->setFromDB( $facility_office_id );
			   $position->save( $user );
			   $position->cleanup();
		    	   unset( $position );
			   $facility->cleanup();
		    	   unset( $facility );
			   
			   
			}elseif($facility_id == 'facility_type|Ghospital'){
			   //echo $position->getField("facility_office")->getDBValue();
			   $facility_office_id = "facility_office|4";
			   $position->getField('facility_office')->setFromDB( $facility_office_id );
			   $position->save( $user );
			   $position->cleanup();
		    	   unset( $position );
			   $facility->cleanup();
		    	   unset( $facility );
			   
			}elseif($facility_id == 'facility_type|HCII'){
			   //echo $position->getField("facility_office")->getDBValue();
			   $facility_office_id = "facility_office|3";
			   $position->getField('facility_office')->setFromDB( $facility_office_id );
			   $position->save( $user );
			   $position->cleanup();
		    	   unset( $position );
			   $facility->cleanup();
		    	   unset( $facility );

			}elseif($facility_id == 'facility_type|HCIII'){
			   //echo $position->getField("facility_office")->getDBValue();
			   $facility_office_id = "facility_office|4";
			   $position->getField('facility_office')->setFromDB( $facility_office_id );
			   $position->save( $user );
			   $position->cleanup();
		    	   unset( $position );
			   $facility->cleanup();
		    	   unset( $facility );

			}elseif($facility_id == 'facility_type|HCIV'){
			   //echo $position->getField("facility_office")->getDBValue();
			   $facility_office_id = "facility_office|5";
			   $position->getField('facility_office')->setFromDB( $facility_office_id );
			   $position->save( $user );
			   $position->cleanup();
		    	   unset( $position );
			   $facility->cleanup();
		    	   unset( $facility );

			}elseif($facility_id == 'facility_type|6'){
			   //echo $position->getField("facility_office")->getDBValue();
			   $facility_office_id = "facility_office|6";
			   $position->getField('facility_office')->setFromDB( $facility_office_id );
			   $position->save( $user );
			   $position->cleanup();
		    	   unset( $position );
			   $facility->cleanup();
		    	   unset( $facility );

			}elseif($facility_id == 'facility_type|5'){
			   //echo $position->getField("facility_office")->getDBValue();
			   $facility_office_id = "facility_office|7";
			   $position->getField('facility_office')->setFromDB( $facility_office_id );
			   $position->save( $user );
			   $position->cleanup();
		    	   unset( $position );
			   $facility->cleanup();
		    	   unset( $facility );

			}elseif($facility_id == 'facility_type|8'){
			   //echo $position->getField("facility_office")->getDBValue();
			   $facility_office_id = "facility_office|8";
			   $position->getField('facility_office')->setFromDB( $facility_office_id );
			   $position->save( $user );
			   $position->cleanup();
		    	   unset( $position );
			   $facility->cleanup();
		    	   unset( $facility );

			}elseif($facility_id == 'facility_type|Town'){
			   //echo $position->getField("facility_office")->getDBValue();
			   $facility_office_id = "facility_office|9";
			   $position->getField('facility_office')->setFromDB( $facility_office_id );
			   $position->save( $user );
			   $position->cleanup();
		    	   unset( $position );
			   $facility->cleanup();
		    	   unset( $facility );

			}
			
        		$count++;

          
	    }

			  
	    
	}

//echo "DONE  ". $count." records have position information updated  ";

?>
