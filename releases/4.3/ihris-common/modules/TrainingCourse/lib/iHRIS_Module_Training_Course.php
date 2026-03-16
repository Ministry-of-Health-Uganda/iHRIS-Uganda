<?php
/**
* © Copyright 2008 IntraHealth International, Inc.
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
*/
/**
*  iHRIS_Module_Trainng_Course
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @copyright Copyright &copy; 2008 IntraHealth International, Inc. 
* This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
* the terms of the GNU General Public License as published by the Free Software Foundation; either 
* version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
* that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
* received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
* @version 2.1
* @access public
*/


class iHRIS_Module_Training_Course extends I2CE_Module{

    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_person_scheduled_training_course' => 'action_person_scheduled_training_course',
            );
    }


    public function action_person_scheduled_training_course($page) {
        if (!$page instanceof iHRIS_PageView) {
            return false;
        }
        $template = $page->getTemplate();
        $appendNode = $template->getElementById('person_scheduled_training_course');
        if (!$appendNode instanceof DOMNode) {
            return true;
        }
        $person = $page->getPerson();
        if (!$person instanceof iHRIS_Person) {
            return false;
        }
        $factory = I2CE_FormFactory::instance();
        $trainingIds = $this->getEnrolledCourseIds($person);
        $pstcs = array();
        foreach ($trainingIds as $trainingId) {
            $trainingForm = $factory->createContainer('person_scheduled_training_course'.'|'.$trainingId);
            if (!$trainingForm instanceof iHRIS_Person_Scheduled_Training_Course) {
                continue;
            }
            $trainingForm->populate();            
            $pstcs[] = $trainingForm;
        }
        if (count($pstcs) == 0) {
            return true;
        }
        $do_exam = I2CE_ModuleFactory::instance()->isEnabled("training-exam");
        foreach ($pstcs as $child) {
            $node = $template->appendFileByNode('view_person_scheduled_training_course.html', 'div',  $appendNode );
            if (!$node instanceof DOMNode) {
                I2CE::raiseError("Could not find template $template for child form $form of person");
                return false;
            }
            $template->setForm($child,$node);
            if ($do_exam) {
                $child->populateChildren('training_course_exam',array('evaluation_date'));
                $exams = $child->getChildren('training_course_exam');
                if (count($exams) > 0) {
                    $template->setDisplayDataImmediate('has_exam_results',1,$node);
                    foreach ($exams as $exam) {
                        if (!  ($examNode = $template->appendFileById('view_training_course_exam.html','tbody','exam_results',false,$node)) instanceof DOMNode) {
                            continue;
                        }
                        $template->setForm($exam,$examNode);
                    }
                } else {
                    $template->setDisplayDataImmediate('has_exam_results',0,$node);
                }
            }
            
            $scheduled_course = $factory->createContainer($child->scheduled_training_course);
            if (!$scheduled_course instanceof iHRIS_Scheduled_Training_Course || $scheduled_course->getId() =='0') {
                I2CE::raiseError( "Bad Scheduled Training Course:" . $child->scheduled_training_course );
                continue;
            }
            $scheduled_course->populate();
            $template->setForm($scheduled_course,$node);
            $course = $factory->createContainer($scheduled_course->training_course);
            if (!$course instanceof iHRIS_Training_Course || $course->getId() == '0') {
                I2CE::raiseError( "Bad Couse:" . $scheduled_course->getParent());
                continue;
            }
            $course->populate();
            $template->setForm($course,$node);
            
        }

        return true;
    }


    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'validate_form_scheduled_training_course' => 'validate_form_scheduled_training_course',
                'validate_form_person_scheduled_training_course_field_training_course_mod' => 'validate_form_person_scheduled_training_course_field_training_course_mod'
                );
    }


    /**
     * Perform any extra validation for the license.
     * @param I2CE_Form $form
     */
    public function validate_form_scheduled_training_course( $form ) {
        if ( I2CE_Validate::checkDate( $form->end_date ) && I2CE_Validate::checkDate( $form->start_date ) ) {
            $compare = $form->end_date->compare( $form->start_date );
            if ( $compare  > 0) {
                $form->setInvalidMessage( "end_date" ,'bad_date');
            }
        }
        
    }


    /**
     * Perform any extra validation for the license.
     * @param I2CE_Form $form
     */
    public function validate_form_person_scheduled_training_course_field_training_course_mod( $fieldObj ) {
        if ((!$fieldObj instanceof I2CE_FormField_MAP_MULT)
            || (!($pstcObj = $fieldObj->getContainer()) instanceof iHRIS_Person_Scheduled_Training_Course)
            || ( $pstcObj->hasAttribute('validate_by_course') && !$pstcObj->getAttribute('validate_by_course'))
            || (!($stcField = $pstcObj->getField('scheduled_training_course')) instanceof I2CE_FormField_MAP) 
            || (! ($stcObj = $stcField->getMappedFormObject()) instanceof iHRIS_Scheduled_Training_Course) 
            || (! ($tcField = $stcObj->getField('training_course')) instanceof I2CE_FormField_MAP)
            || (! ($tcObj = $tcField->getMappedFormObject()) instanceof iHRIS_Training_Course)
            || (! ($modField = $tcObj->getField('training_course_mod')) instanceof I2CE_FormField_MAP_MULT)
            ) {
            return;
        }
        $selected = $fieldObj->getValue();
        if (!is_array($selected)) {
            $selected = array();
        }
        $t_allowed = $modField->getValue();
        if (!is_array($t_allowed)) {
            return;
        }
        $allowed = array();
        foreach ($t_allowed as $allow) {
            if (!is_array($allow)  
                || !count($allow) == 2) {
                continue;
            }
            list($allow_form,$allow_id) = $allow;
            $allowed[] = $allow_id;
        }
        $ok = true;
        $bads = array();
        foreach($selected as $sel) {
            if (!is_array($sel)  
                || !count($sel) == 2) {
                continue;
            }
            list($sel_form,$sel_id) = $sel;
            if (in_array($sel_id,$allowed)) {
                continue;
                
            }
            $bads[] = I2CE_List::lookup($sel_id,'training_course_mod');
        }
        if (count($bads) > 0) {            
            $fieldObj->setInvalidMessage( 'bad_mod','',implode(" ",$bads));
        }
    }



    /**
     * Run the pre upgrade for this module.  This can use the old config data before it
     * has been changed from the config.
     * @param string $old_vers
     * @param string $new_vers
     * @param I2CE_MagicDataNode $new_storage
     * @return boolean
     */
    public function pre_upgrade( $old_vers, $new_vers, $new_storage ) {
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.3' ) ) {
        /**
        * In 3.2.3 some lists were moved to magicdata storage so we need to save
        * any old record ids for the old lists for later reference before any field
        * types get changed in magic data.
        */
        $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";
        I2CE_FormStorage::storeMigrateData( array( "training_course" => 
                    array( "training_institution", "training_funder",
                        "continuing_education_course", "training_course_status",
                        "training_course_category", ),
                    "person_scheduled_training_course" => array( "training_course_evaluation",
                        "training_course_requestor", "training_course", "scheduled_training_course" ), 
                    "scheduled_training_course" => array( "country", "district", "county" ) ), 
                $migrate_path );
        }   
        return parent::pre_upgrade( $old_vers, $new_vers, $new_storage );
    }   


    /**
     * Upgrade this module if necessary
     * @param string $old_vers
     * @param string $new_vers
     * @return boolean
     */
    public function upgrade( $old_vers, $new_vers ) {
        /*
        * In 3.2.3 we moved some lists from entry to magicdata storage so we need to get the
        * old data from entry and save them to the new form storage.
        */
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.3' ) ) {
            $user = new I2CE_User( 1, false, false, false );
            $class_config = I2CE::getConfig()->modules->forms->formClasses;
            $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";
            if ( !I2CE_FormStorage::migrateForm( "training_course_status", "entry", $user, $migrate_path, 
                        false, array( "type" ) ) ) {
                return false;
            }
            if ( !I2CE_FormStorage::migrateForm( "training_course_category", "entry", $user, $migrate_path, 
                        false, array( "type" ) ) ) {
                return false;
            }
            if ( !I2CE_FormStorage::migrateForm( "training_course_evaluation", "entry", $user, $migrate_path, 
                        false, array( "type" ) ) ) {
                return false;
            }
            if ( !I2CE_FormStorage::migrateForm( "training_course_requestor", "entry", $user, $migrate_path, 
                        false, array( "type" ) ) ) {
                return false;
            }

            if ( !I2CE_FormStorage::migrateField( "training_course",
                        array( "training_institution" => "training_institution",
                            "training_funder" => "training_funder",
                            "continuing_education_course" => "continuing_education_course",
                            "training_course_status" => "training_course_status",
                            "training_course_category" => "training_course_category" ),
                        $migrate_path, $user ) ) {
                return false;
            }
            if ( !I2CE_FormStorage::migrateField( "person_scheduled_training_course",
                        array( "training_course_evaluation" => "training_course_evaluation",
                            "training_course_requestor" => "training_course_requestor", 
                            "scheduled_training_course" => "scheduled_training_course" ), 
                        $migrate_path, $user ) ) {
                return false;
            }
            if ( !I2CE_FormStorage::migrateField( "scheduled_training_course",
                        array( "location" => array( "county" => "county", 
                                "district" => "district", "country" => "country" ) ),
                        $migrate_path, $user ) ) {
                return false;
            }

            unset( $class_config->iHRIS_Person_Scheduled_Training_Course->fields->training_course );
            unset( $class_config->iHRIS_Scheduled_Training_Course->fields->country );
            unset( $class_config->iHRIS_Scheduled_Training_Course->fields->district );
            unset( $class_config->iHRIS_Scheduled_Training_Course->fields->county );

        } elseif ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.35' ) ) {
            $user = new I2CE_User( 1, false, false, false );
            $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";
            if ( !I2CE_FormStorage::migrateField( "person_scheduled_training_course",
                        array( "scheduled_training_course" => "scheduled_training_course" ), 
                        $migrate_path, $user ) ) {
                return false;
            }
        }
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.18' ) ) {
            $evals =array('pass'=>'Pass','fail'=>'Fail','incomplete'=>'Incomplete'); 
            if (!I2CE_Module_Lists::remapFields('training_course_evaluation',  $evals,'person_scheduled_training_course')) {
                return false;
            }
            if (!I2CE_Module_Lists::deleteMappedValues('training_course_evaluation', $evals)) {
                return false;
            }
        }
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.30' ) ) {
            $evals = array('open'=>'Open','closed'=>'Closed');
            if (!I2CE_Module_Lists::remapFields('training_course_status',$evals, 'training_course')) {
                return false;
            }
            if (!I2CE_Module_Lists::deleteMappedValues('training_course_status', $evals)) {
                return false;
            }
        }
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.32' ) ) {
            $user = new I2CE_User( 1, false, false, false );
            $class_config = I2CE::getConfig()->modules->forms->formClasses;
            $migrate_path = "/I2CE/formsData/migrate_data/3.2.3";

            if ( !I2CE_FormStorage::migrateField( 
                     "scheduled_training_course", 
                     array("location" => 
                           array( "county" => "county", 
                                  "district" => "district", 
                                  "country" => "country" ) ),
                     $migrate_path, $user ) ) {
                return false;
            }

        }
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.63' ) ) {
            if (!$this->moveScheduledTrainingCourseToMap()){
                return false;
            }
        }
        return parent::upgrade( $old_vers, $new_vers );
    }

    /**
     * Make ehceduled training courses a map value of training course
     * rather than a child.
     */
    protected function moveScheduledTrainingCourseToMap() {
        $fields = array('parent');
        $stcs = I2CE_FormStorage::listFields('scheduled_training_course',array('parent'));
        $user = new I2CE_User( 1, false, false, false );
        $factory = I2CE_FormFactory::instance();
        foreach ($stcs as $stc_id=>$data) {
            if (!array_key_exists('parent',$data)) {
                continue;
            }
            $parent = $data['parent'];
            if (empty($parent) || $parent == '|'  || $parent == 'training_course|' || $parent =='training_course' 
                || !is_string($parent) || substr($parent,0,16) != 'training_course|') {
                continue;
            }
            $stcObj = $factory->createContainer('scheduled_training_course|' . $stc_id);
            if  (!$stcObj instanceof I2CE_Form) {
                I2CE::raiseError("Cannot instanciatanve  'scheduled_training_course|" . $stc_id);
                return false;
            }
            $stcObj->populate();
            $fieldObj = $stcObj->getField('training_course');
            if (!$fieldObj instanceof I2CE_FormField) {
                I2CE::raiseError("Could not get training course");
                return false;
            }
            $fieldObj->setFromDB($parent);
            $stcObj->setParent('|');
            $stcObj->save($user);
        }
        if (!I2CE::getConfig()->is_parent("/modules/forms/forms/training_course/child_forms")) {
            return true;
        }
        $child_forms = I2CE::getConfig()->traverse("/modules/forms/forms/training_course/child_forms");
        foreach ($child_forms as $k=>$val) {
            if ($val !== 'scheduled_training_course') {
                continue;
            }
            unset($child_forms->$k);
        }
        return true;
    }




    protected function getEnrolledCourseIDs($personForm) {
        if (!$personForm instanceof iHRIS_Person || $personForm->getId() == '0') {
            echo "A";
            return array();
        }
        $where = array(
            'operator' => 'OR',
            'operand'=>array(
                0=>array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'attending',
                    'style'=>'equals',
                    'data'=>array(
                        'value'=>1
                        )),
                1=>array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'attending',
                    'style'=>'null',
                    'data'=>array()
                    )
                )
            );
        return $personForm->getChildIds('person_scheduled_training_course','-request_date',$where); //get all trainings.

    }
    

    public function showEnrolledCourses($node,$template,$args=array()) {
        $personForm = $template->getData('FORM','person',$node);
        $trainingIds = $this->getEnrolledCourseIds($personForm);
        if (count($trainingIds) == 0) {
            $node->parentNode->removeChild($node);
            return;
        }
        $node->removeAttribute('type');
        $node->removeAttribute('name');
        $factory = I2CE_FormFactory::instance();
        foreach ($trainingIds as $trainingId) {
            $trainingForm = $factory->createContainer('person_scheduled_training_course'.'|'.$trainingId);
            if (!$trainingForm instanceof I2CE_Form) {
                continue;
            }
            $trainingForm->populate();
            if ($trainingForm->training_course < 1) {
                continue;
            }
            if ($trainingForm->scheduled_training_course < 1) {
                continue;
            }
            $courseForm = $factory->createContainer('training_course'.'|'.$trainingForm->training_course);
            $scheduledCourseForm = $factory->createContainer('scheduled_training_course'.'|'.$trainingForm->scheduled_training_course);
            if (!$courseForm instanceof I2CE_Form) {
                continue;
            }
            if (!$scheduledCourseForm instanceof I2CE_Form) {
                continue;
            }
            $courseForm->populate();
            $scheduledCourseForm->populate();
            
            $trainingNode = $template->appendFileByNode('person_scheduled_training_list.html','div',$node);
            if (!$trainingNode instanceof DOMNode) {
                continue;
            }
            $template->setForm($trainingForm,$trainingNode);
            $template->setForm($courseForm,$trainingNode);
            $template->setForm($scheduledCourseForm,$trainingNode);
        }
        
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
