<?php
/**
* © Copyright 2017 IntraHealth International, Inc.
*
* This File is part of I2CE
*
* I2CE is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
* @package I2CE
* @author Nobert Mijumbi <nobertmn@gmail.com>
* @version v4.3.0
* @since v4.3.0
* @filesource
*/
/**
* Class I2CE_Dashboard
*
* @access public
*/
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
       if(isset($form->days_present)||isset($form->days_off_request)||isset($form->days_off_duty)||isset($form->days_leave)){
       if ( $form->month_year->isValid() ) {
           $values = explode('-',$form->getField("month_year")->getDBValue());
           $no_of_days=cal_days_in_month(CAL_GREGORIAN,$values[1],$values[0]);
           }
        $totalDays = 0;
	    $totalDays = $form->days_present+$form->days_or+$form->days_od+$form->days_leave;
        if( ($totalDays) > ($no_of_days) ){
            $form->setInvalidMessage('days_present', 'Total number of days exceeds maximum days of selected month');
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
		   if( count($results) != 0 ) {
 			$factory = I2CE_FormFactory::instance();
			$current_month_year = date("F Y", strtotime($form->getField('month_year')->getDBValue()));
               foreach( $results as $e_instance => $e_data ) {
                 $person_attendance_instance = $factory->createContainer("person_attendance|".$e_data['id']);
                 $person_attendance_instance->populate();
				 $instance_month_year = date("F Y", strtotime($person_attendance_instance->getField('month_year')->getDBValue()));
				if ($current_month_year === $instance_month_year){
			        $form->setInvalidMessage('month_year', 'Data for this month and year already exists');
			        return;
				    }
		          }  
		
		      }
          ///No of days absent
          $form->days_absent = ($no_of_days - $totalDays) ;
          $form->absenteeism_rate = (($form->days_absent / $no_of_days)*100);
          $month_year_split = explode('-',$form->getField("month_year")->getDBValue());
          $month_year_day = $month_year_split[0]."-".$month_year_split[1]."-"."01";
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
