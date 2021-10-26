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
if ( PEAR::isError( $db ) ) {
	die( $db->getMessage() );
}
$form_factory = I2CE_FormFactory::instance();

echo "Memory Limit: " . ini_get( "memory_limit" ) . "\n";
echo "Execution Time: " . ini_get( "max_execution_time" ) . "\n";

        $find_id = array(
                    'operator' => 'FIELD_LIMIT',
                    'field' => 'position',
                    'style' => 'null'
                    );

        $person_attendance_id = I2CE_FormStorage::listFields( "person_attendance", array('id'), $find_id );
        
     if (!isset($person_attendance_id)){
        echo "No Ids \n";
      }else{
                $count = 0;
        foreach ( $person_attendance_id as $id=>$value  ){                //echo 'person_attendance'.'|'.$key ;
           
		        $person_attendance = $form_factory->createContainer( 'person_attendance'.'|'.$id );
		        $person_attendance->populate();


			
       if(isset($person_attendance->days_present)||isset($person_attendance->days_or)||isset($person_attendance->days_od)||isset($person_attendance->days_leave)){

       if ( $person_attendance->month_year->isValid() ) {

		//$current_year = date('Y', strtotime('$person_attendance->month_year_day'));

                //$current_month = date('F', strtotime('$person_attendance->month_year_day'));
      
		$values = explode('-',$person_attendance->getField("month_year")->getDBValue());

		//I2CE::raiseError($current_year."---".$values[0]);

		//I2CE::raiseError($current_month."---".$values[1]);

		$no_of_days=cal_days_in_month(CAL_GREGORIAN,$values[1],$values[0]);

	$count++;
	
           }
        

	
        $totalDays = 0;
	    $totalDays = $person_attendance->days_present+$person_attendance->days_or+$person_attendance->days_od+$person_attendance->days_leave;

	
	
      
            	
            	
         if(isset($person_attendance->work_days)){
          $person_attendance->days_absent = ($person_attendance->work_days - ($person_attendance->days_present + $person_attendance->days_or + $person_attendance->days_leave)) ;
	  if($person_attendance->days_absent < 0){
		$person_attendance->days_absent = 0;
	  }
	    $person_attendance->final_work_days = ($person_attendance->work_days - ( $person_attendance->days_or + $person_attendance->days_leave)) ;
	  }

	  if(!isset($person_attendance->work_days)){
           //$person_attendance->absenteeism_rate = NULL;
	   

	  }else{
        $divide=($person_attendance->work_days - ($person_attendance->days_or + $person_attendance->days_leave));
         if($divide==0){
          $person_attendance->absenteeism_rate=0;
         }
         else{
          $person_attendance->absenteeism_rate = ($person_attendance->days_absent/$divide)*100));
         }
          //$month_year_split = explode('-',$form->getField("month_year")->getDBValue());
          //$month_year_day = $month_year_split[0]."-".$month_year_split[1]."-"."01";
	  }
	  ///No of days absolutely absent
          $person_attendance->absolute_days_absent = ($no_of_days - $totalDays) ;
          $person_attendance->absolute_absenteeism_rate = (($person_attendance->absolute_days_absent / $no_of_days)*100);
          //$month_year_split = explode('-',$form->getField("month_year")->getDBValue());
          //$month_year_day = $month_year_split[0]."-".$month_year_split[1]."-"."01";

	  ///No of days not at facility
          $person_attendance->days_not_at_facility = ($no_of_days - $person_attendance->days_present) ;
          $person_attendance->per_days_not_at_facility = (($person_attendance->days_not_at_facility / $no_of_days)*100);
          $month_year_split = explode('-',$person_attendance->getField("month_year")->getDBValue());
          $month_year_day = $month_year_split[0]."-".$month_year_split[1]."-"."01";
          //I2CE::raiseError(" date ".$month_year );
	      $person_attendance->getField("month_year_day")->setFromDB( $month_year_day );
          }else{
		$person_attendance->setInvalidMessage('days_present', 'Enter a value in atleast one field');
		}
	   
	   }

		if(!isset($person_attendance->work_days)){

			$person_attendance->final_work_days = NULL;
		}
	    }
	    
	

echo "DONE  ". $count." records have attendance information updated  ";

?>
