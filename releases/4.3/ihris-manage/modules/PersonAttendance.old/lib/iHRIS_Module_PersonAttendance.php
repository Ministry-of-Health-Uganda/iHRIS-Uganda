<?php

class iHRIS_Module_PersonAttendance extends I2CE_Module {

    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_person_attendance' => 'action_person_attendance'
            );
    }

    public static function getHooks() {
        return array(
                'validate_form_person_attendance' => 'validate_form_person_attendance',
              
                );
    }


   public function action_person_attendance($page) {
        if (!$page instanceof iHRIS_PageView) {
            return false;
        }
        
        $person = $page->getPerson();
        if (!$person instanceof iHRIS_Person) {
            return false;
        }
        $template=$page->getTemplate();
        if (  ($lastAttendance = $this->getLastAttendance($person)) instanceof iHRIS_PersonAttendance) {
            $person->addChildForm($lastAttendance);
            $page->appendChildTemplate('person_attendance','siteContent');
        } else {
            $page->addLastChildForm('person_attendance','month_year', 'siteContent');
        }
        if (!array_key_exists('person_attendance',$person->children) || !is_array($person->children['person_attendance']) 
            || count($person->children['person_attendance']) != 1) {
            return true;
        }
        reset($person->children['person_attendance']);        
        $pers_attendance = current($person->children['person_attendance']);        
        if ( !$pers_attendance instanceof iHRIS_PersonAttendance)  {
            return true;
        }
        
        
        //return $page->addChildForms('person_attendance', 'siteContent');
    }
    
    /**
     * Find and return the most recent attendance taken by this person.
     * 
     * @return I2CE_Form or null
     */
    public function getLastAttendance($person) {
        if (!$person instanceof iHRIS_Person) {
            return null;
        }
        $where = array(
            'operator'=>'FIELD_LIMIT',
            'field'=>'month_year',
            'style'=>'max_parent_form',
	    'data'=>array()
            );
        $per_attendance_id = I2CE_FormStorage::search('person_attendance', $person->getNameId(),$where,'-start_date',1);
        if (!$per_attendance_id) {
            return null;
        }
        $attendance = I2CE_FormFactory::instance()->createContainer('person_attendance'.'|'.$per_attendance_id);
        if (!$attendance instanceof iHRIS_PersonAttendance) {
            return null;
        }
        $attendance->populate();
        return $attendance;
    }
    

  public function validate_form_person_attendance( $form ) {
       if(isset($form->days_present)||isset($form->days_or)||isset($form->days_od)||isset($form->days_leave)){

       if ( $form->month_year->isValid() ) {

		$current_year = date(" Y");

                $current_month = date(" n");
      
		$values = explode('-',$form->getField("month_year")->getDBValue());

		I2CE::raiseError($current_year."---".$values[0]);

		I2CE::raiseError($current_month."---".$values[1]);

		$no_of_days=cal_days_in_month(CAL_GREGORIAN,$values[1],$values[0]);
	
           }
        

	
            $totalDays = 0;
	    $totalDays = $form->days_present+$form->days_or+$form->days_od+$form->days_leave;

	I2CE::raiseMessage($totalDays);
	
      
        if( ($totalDays) > ($no_of_days) ){
                $form->setInvalidMessage('days_present', 'Total number of days exceeds maximum days of selected month');
               
            }elseif(($current_month < $values[1]) && ($current_year <= $values[0])){

		 $form->setInvalidMessage('month_year', 'You cannot upload a month in advance');


	}
       


	
         	   $where = array(
		       'operator' => 'AND',
		       'operand'=>array(
			   0 => array(
		               'operator'=>'FIELD_LIMIT',
		               'field'=>'parent',
		               'style'=>'equals',
		               'data'=>array(
		                    'value'=> $form->getParent()
		                    )
		               )
		           )
		       );
				
 				
		   $results = I2CE_FormStorage::listFields('person_attendance',array('month_year','parent','id'),false,$where);
		   
	// $results = I2CE_FormStorage::search('person_attendance',false,$where);
		   if( count($results) != 0 ) {
 			$factory = I2CE_FormFactory::instance();
			$current_month_year = date("F Y", strtotime($form->getField('month_year')->getDBValue()));
			 foreach( $results as $e_instance => $e_data ) {
				 $person_attendance_instance = $factory->createContainer("person_attendance|".$e_data['id']);
				 $person_attendance_instance->populate();
				 $instance_month_year = date("F Y", strtotime($person_attendance_instance->getField('month_year')->getDBValue()));
				//I2CE::raiseError(" dates ".$current_month_year ."  ".$instance_month_year);
				if ($current_month_year === $instance_month_year){
			        $form->setInvalidMessage('month_year', 'Data for this month and year already exists');
			        return;
				}
		    }  
		
		}
          ///No of days absent

	  if(isset($form->work_days)){
          $form->days_absent = ($form->work_days - ($form->days_present + $form->days_or + $form->days_leave)) ;
	  if($form->days_absent <= 0){
		$form->days_absent = 0;
		$form->absenteeism_rate =0;
	  }
	    $form->final_work_days = ($form->work_days - ( $form->days_or + $form->days_leave)) ;
	 
	    $form->absenteeism_rate = ($form->days_absent /( $form->work_days - ($form->days_or + $form->days_leave))*100);
	  }else{
		$form->days_absent = 0;
	 	$form->absenteeism_rate =0;
	  }
          //$form->absenteeism_rate = ($form->days_absent /( $form->work_days - ($form->days_or + $form->days_leave))*100);
          //$month_year_split = explode('-',$form->getField("month_year")->getDBValue());
          //$month_year_day = $month_year_split[0]."-".$month_year_split[1]."-"."01";

	  ///No of days absolutely absent
          $form->absolute_days_absent = ($no_of_days - $totalDays) ;
          $form->absolute_absenteeism_rate = (($form->absolute_days_absent / $no_of_days)*100);
          //$month_year_split = explode('-',$form->getField("month_year")->getDBValue());
          //$month_year_day = $month_year_split[0]."-".$month_year_split[1]."-"."01";

	  ///No of days not at facility
          $form->days_not_at_facility = ($no_of_days - $form->days_present) ;
          $form->per_days_not_at_facility = (($form->days_not_at_facility / $no_of_days)*100);
          $month_year_split = explode('-',$form->getField("month_year")->getDBValue());
          $month_year_day = $month_year_split[0]."-".$month_year_split[1]."-"."01";
          //I2CE::raiseError(" date ".$month_year );
	      $form->getField("month_year_day")->setFromDB( $month_year_day );
          }else{
		$form->setInvalidMessage('days_present', 'Enter a value in atleast one field');
		}
		
		
      }
    }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
