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

global $dictionary;
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

function getAttendance(){
	$endpoint ='https://hris2.health.go.ug/attendance/api/person_attend/2021-06-01/2021-10-31';
	$attdata   = $this->sendRequest($url);
return $attdata;
}
function  sendRequest($url){

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);

	return json_decode($output,true);
}


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


$datas=getAttendance();
print_r($datas);
// $skip_no_post = 0;
// $found = 0;
// $created = 0;
// foreach ($datas as $data) {
    
//     $month_year_day = $data['duty_date'];

//     $month_year = $data['duty_date'];


// 		   if ( !$data['ihris_pid'] ) {
//                         I2CE::raiseError("Unable find person.");
// 			$skip_no_post++;
//                        continue;
//             }
			
//    	    $person = $form_factory->createContainer( $data['ihris_pid'] );
//             $person->populate();
//             $person->populateLast( array( "person_position" => "start_date" ));
//             $person_position_form = current( $person->children['person_position'] );

	    


// 	    $find_pers = array(
//                                 'operator' => 'AND',
//                                 'operand' => array(
//                                               0 => array(
//                                                     'operator' => 'FIELD_LIMIT',
//                                                     'style' => 'equals',
//                                                     'field' => 'parent',
//                                                     'data' => array(
//                                                                     'value' => $data['ihris_pid'],
//                                                                     ),
//                                                     ),
//                                               1 => array(
//                                                          'operator' => 'FIELD_LIMIT',
//                                                          'style' => 'equals',
//                                                          'field' => 'month_year_day',
//                                                          'data' => array(
//                                                                          'value' => $month_year_day,
//                                                                          ),
//                                                          ),
//                                                 )
// 												);

//                    $person_attendance_id = I2CE_FormStorage::search( "person_attendance", false, $find_pers, array(), true ); 

		  
		 
			
// 		  if($person_attendance_id){

// 			 $person_attendance = $form_factory->createContainer( 'person_attendance|'.$person_attendance_id );
// 			 $person_attendance->populate();
	     
// 		    	 $person_attendance->days_present = $data['P'];
// 			 $person_attendance->days_od = $data['O'];
// 			 $person_attendance->days_or = $data['R'];
// 			 $person_attendance->days_leave = $data['L'];

// 			 if ( $person_attendance->month_year->isValid() ) {

// 			$current_year = date(" Y");

// 		        $current_month = date(" n");
	      
// 			$values = explode('-',$person_attendance->getField("month_year")->getDBValue());

// 			I2CE::raiseError($current_year."---".$values[0]);

// 			I2CE::raiseError($current_month."---".$values[1]);

// 			$no_of_days=cal_days_in_month(CAL_GREGORIAN,$values[1],$values[0]);
	
// 		        }

// 			 $totalDays = 0;
// 			 $totalDays = $person_attendance->days_present+$person_attendance->days_or+$person_attendance->days_od+$person_attendance->days_leave;

	
	
      
// 			if( ($totalDays) > ($no_of_days) ){
// 				$person_attendance->setInvalidMessage('days_present', 'Total number of days exceeds maximum days of selected month');
			       
// 			    }elseif(($current_month < $values[1]) && ($current_year <= $values[0])){

// 				 $person_attendance->setInvalidMessage('month_year', 'You cannot upload a month in advance');


// 			}	
			 
			  
			  
			  
// 			  ///No of days absolutely absent
// 			  $person_attendance->absolute_days_absent = ($no_of_days - $totalDays) ;
// 			  $person_attendance->absolute_absenteeism_rate = (($person_attendance->absolute_days_absent / $no_of_days)*100);
			  

// 			  ///No of days not at facility
// 			  $person_attendance->days_not_at_facility = ($no_of_days - $person_attendance->days_present) ;
// 			  $person_attendance->per_days_not_at_facility = (($person_attendance->days_not_at_facility / $no_of_days)*100);
// 			  $month_year_split = explode('-',$person_attendance->getField("month_year")->getDBValue());
// 			  $month_year_day = $month_year_split[0]."-".$month_year_split[1]."-"."01";
// 			  //I2CE::raiseError(" date ".$month_year );
			  
// 			 $person_attendance->setParent( $data[iHRIS_person_id] );
// 		    	 $person_attendance->save( $user );
// 		    	 $person_attendance->cleanup();
// 		    	 unset( $person_attendance );
// 			 $found++;

// 		    }else{

//                          $person_attendance = $form_factory->createContainer( "person_attendance" );
// 			 $person_attendance->position = $person_position_form->position;
// 			 $person_attendance->getField('month_year')->setFromDB( $month_year );
// 		    	 $person_attendance->days_present = $data['P'];
// 			 $person_attendance->days_od = $data['O'];
// 			 $person_attendance->days_or = $data['R'];
// 			 $person_attendance->days_leave = $data['L'];
// 			 $person_attendance->getField("month_year_day")->setFromDB( $month_year_day );

// 			 if ( $person_attendance->month_year->isValid() ) {

// 			$current_year = date(" Y");

// 		        $current_month = date(" n");
	      
// 			$values = explode('-',$person_attendance->getField("month_year")->getDBValue());

// 			I2CE::raiseError($current_year."---".$values[0]);

// 			I2CE::raiseError($current_month."---".$values[1]);

// 			$no_of_days=cal_days_in_month(CAL_GREGORIAN,$values[1],$values[0]);
	
// 		        }

// 			 $totalDays = 0;
// 			 $totalDays = $person_attendance->days_present+$person_attendance->days_or+$person_attendance->days_od+$person_attendance->days_leave;

	
	
      
// 			if( ($totalDays) > ($no_of_days) ){
// 				$person_attendance->setInvalidMessage('days_present', 'Total number of days exceeds maximum days of selected month');
			       
// 			    }elseif(($current_month < $values[1]) && ($current_year <= $values[0])){

// 				 $person_attendance->setInvalidMessage('month_year', 'You cannot upload a month in advance');


// 			}	
			 
			  
			  
			  
// 			  ///No of days absolutely absent
// 			  $person_attendance->absolute_days_absent = ($no_of_days - $totalDays) ;
// 			  $person_attendance->absolute_absenteeism_rate = (($person_attendance->absolute_days_absent / $no_of_days)*100);
			  

// 			  ///No of days not at facility
// 			  $person_attendance->days_not_at_facility = ($no_of_days - $person_attendance->days_present) ;
// 			  $person_attendance->per_days_not_at_facility = (($person_attendance->days_not_at_facility / $no_of_days)*100);
// 			  $month_year_split = explode('-',$person_attendance->getField("month_year")->getDBValue());
// 			  $month_year_day = $month_year_split[0]."-".$month_year_split[1]."-"."01";
// 			  //I2CE::raiseError(" date ".$month_year );
// 			 $person_attendance->setParent( $data['ihris_pid'] );
// 		    	 $person_attendance->save( $user );
// 			 $person_attendance->cleanup();
// 		    	 unset( $person_attendance );
// 			 $created++;
   
		    	 
// 		}

			 
	
// }

// echo "Skipped $skip_no_post records.\n";
// echo "Found $found records.\n";
// echo "Created $created records.\n";

?>
