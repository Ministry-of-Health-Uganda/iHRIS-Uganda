<?php
/*
 * © Copyright 2012 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 */
/**
 * Edit participants action for a training
 * @package iHRIS
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org> / Carl Leitner <litlfred@ibiblio.org> for pull from iHRIS Train into iHRIS Common
 * @copyright Copyright &copy; 2012 IntraHealth International, Inc. 
 * @since v4.1.3
 * @version v4.1.3
 */

/**
 * The action page class for editing particpants for a training
 * @package iHRIS
 * @subpackage Train
 * @access public
 */
class iHRIS_PageActionStudents extends I2CE_Page { 

    /**
     * @var iHRIS_Scheduled_Training_Course  The training course instance
     */
    protected $scheduled_training_course;

    /**
     * @var I2CE_FormFactory The form factory.
     */
    protected $factory;

    /**
     * Perform the main actions of the page.
     * @return boolean
     */
    protected function action() {
        if ( !parent::action() ) {
            I2CE::raiseError("Base action failed");
            return false;
        }
        if (!$this->hasPermission("task(person_can_edit_child_form_person_scheduled_training_course)")) {
            $no_edit = "You do not have permission to edit students for this course instance.";
            I2CE::getConfig()->setIfIsSet($no_edit,"/modules/training-course/translatable-strings/no_edit_students");
            $this->userMessage($no_edit);
            I2CE::raiseError("Cannot edit");
            return false;
        }
        $piObj = false;
        $person_instance = false;
        if ($this->request_exists('scheduled_training_course')) {
            if ( !$this->request_exists('person') ) {
                I2CE::raiseError("No person");
                return true;
            }
            $person_instance = $this->getPersonInstance( $this->request('person'), $this->request('scheduled_training_course') );
        } else if ($this->request_exists('person_scheduled_training_course')) {
            $person_instance = $this->request('person_scheduled_training_course');
        } 


        if ($person_instance &&  !( $piObj = I2CE_FormFactory::instance()->createContainer(  $person_instance )) instanceof iHRIS_Person_Scheduled_Training_Course) {
            I2CE::raiseError("No scheuled course associated to ". $this->request('person'));
            $this->template->addFile('action_students_error.html');
            return false;
        }
        if ( $person_instance ) {
            $piObj->populate();
            switch ($this->request('action')) {
            case 'student_module':
                if ( ($modField = $piObj->getField('training_course_mod')) instanceof I2CE_FormField_MAP_MULT
                     && ($mod = $this->request('training_course_mod'))
                    ) {
                    $e_mod = explode("|",$mod);
                    if ($e_mod[0] == 'training_course_mod' && $e_mod[1] != '0' && $e_mod[1] != '') {
                        $val = $modField->getValue();
                        $t_val = $val;
                        foreach ($t_val as &$v) {
                            $v = implode("|",$v);
                        }
                        unset($v);
                        if (($pos = array_search($mod,$t_val)) === false) {
                            $val[] = $e_mod;
                        } else {
                            unset($val[$pos]);
                        }
                        $modField->setValue($val);
                    }
                }
                break;
            case 'certify':
                if ( ($certField = $piObj->getField('certification_date')) instanceof I2CE_FormField_DATE_YMD) {
                    if ($this->request_exists('certification_date')) {
                        $date = I2CE_Date::fromDB($this->request('certification_date'));
                    } else {
                        $date = I2CE_Date::now();
                    }
                    $certField->setValue($date);
                }
                break;
            case 'evaluation':
                $piObj->getField('training_course_evaluation')->setValue(explode('|',$this->request('evaluation'),2));
            break;
            case 'remove':
                $piObj->attending = 0;
                $this->template->addFile("action_students_remove.html");                
                break;
            default:
                if ( $piObj->attending == 0 ) {
                    $piObj->attending = 1;
                    $this->template->addFile("action_students_add.html");
                } else {
                    $piObj->attending = 0;
                    $this->template->addFile("action_students_remove.html");
                }
                break;
            }
        } else {
            if ( ! ($piObj = I2CE_FormFactory::instance()->createContainer(  'person_scheduled_training_course' )) instanceof iHRIS_Person_Scheduled_Training_Course) {
                I2CE::raiseError("Could not create person_scheduled_training_course");
                return false;
            }
            $piObj->setParent($this->request('person'));
            $piObj->getField('scheduled_training_course')->setFromDB($this->request('scheduled_training_course'));
            $piObj->getField('attending')->setFromDB(1);
            $this->template->addFile("action_students_add.html");
        }
        I2CE::raiseError("Performing " . $this->request('action') . " on  student " . $piObj->getParent() . " from " . $piObj->getField('scheduled_training_course')->getDBValue());
        return $piObj->save( $this->user );
    }

    /**
     * Get the person instance ID if it exists
     * @param string $person
     * @param string $instance
     * @return integer
     */
    protected function getPersonInstance( $person, $instance ) {
        $where_data = array(
                'operator' => 'FIELD_LIMIT',
                'field' => 'scheduled_training_course',
                'style' => 'equals',
                'data' => array(
                    'value' => $instance,
                    )
                );
        $student = I2CE_FormStorage::search( 'person_scheduled_training_course', $person, $where_data, array(), 1 );
        if (! $student) {
            return false;
        }
        return 'person_scheduled_training_course|' . $student;
    }

    
}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
