<?php 
/**
 * © Copyright 2008, 2009 IntraHealth International, Inc.
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
 *  iHRIS_Module_TrainingSimpleCompetency
 * @package iHRIS
 * @subpackage Common
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @version 3.2.3
 * @since 3.2.3
 * @access public
 */


class iHRIS_Module_TrainingSimpleCompetency extends I2CE_Module {


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

            I2CE_FormStorage::storeMigrateData( array( "training_course_competency_evaluation" 
                        => array( "competency_evaluation", "competency_type", "competency" ), 
                        "training_course" => array( "competency" ) ),
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

            if ( !I2CE_FormStorage::migrateField( "training_course_competency_evaluation", 
                        array( "competency_evaluation" => "competency_evaluation",
                            "competency" => "competency" ),
                        $migrate_path, $user ) ) {
                return false;
            }
            if ( !I2CE_FormStorage::migrateField( "training_course", 
                        array( "competency" => "competency" ),
                        $migrate_path, $user ) ) {
                return false;
            }

            // If everything migrated correctly, then remove the unused fields.
            unset( $class_config->iHRIS_Training_Course_Competency_Evaluation->fields->competency_type );
        }
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.6' ) ) {
            $evals =array('not_evaluated'=>'Not Evaluated');
            if (!I2CE_Module_Lists::remapFields('competency_evaluation',  $evals,'training_course_competency_evaluation')) {
                return false;
            }
        }
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.7' ) ) {
//             if (!I2CE_Module_Lists::deleteMappedValues('training_course_evaluation', $evals)) {
//                 return false;
//             }
        }

        return parent::upgrade( $old_vers, $new_vers );
    }




    /**
     * Get the competencies associated to an person scheduled training course
     * @param mixed $course.  Either a string, the pseron scheduled training course id, a shceduled_training course id, or a (populated) instancof iHRIS_Person_Scheduled_Training_Course 
     * or a iHRIS_Scheduled_Trianing_Course or a iRHIS_Training_Course
     * @returns array of string
     */
    public static function getAssociatedCompetencies($course) {
        $ff = I2CE_FormFactory::instance();
        if (is_string($course)) {
            $courseForm = $ff->createContainer($course);
            if (!$course instanceof I2CE_Form) {
                I2CE::raiseError("Could not instantiate $course");
                return array();
            }
            $course = $courseForm; 
            $course->populate();
        }
        if ($course instanceof iHRIS_Person_Scheduled_Training_Course) {
            if ( !$course->getField('scheduled_training_course') instanceof I2CE_FormField_MAP 
                 || ! ($courseForm = $ff->createContainer($course->scheduled_training_course)) instanceof iHRIS_Scheduled_Training_Course) {
                I2CE::raiseError("cannot associate scheduled training course to " . $course->getNameId() );
                return array();
            }
            $course = $courseForm;
            $course->populate();
        } 
        if ($course instanceof iHRIS_Scheduled_Training_Course) {
            if ( ! ($courseForm= $ff->createContainer($course->training_course)) instanceof iHRIS_Training_Course) {
                I2CE::raiseError("cannot associate  training course to " . $course->getNameId());
                return array();
            }
            $course = $courseForm;
            $course->populate();
        }
        if (!$course instanceof iHRIS_Training_Course) {
            I2CE::raiseError("Invalid data" );
            return array();
        }
        if ( ! ($comp_field = $course->getField('competency')) instanceof I2CE_FORMFIELD_MAP_MULT) {
            I2CE::raiseError("cannot associate competenices to  " . $course->getNameId());
            return false;
        }
        $comp_ids = $comp_field->getValue();
        foreach ($comp_ids as &$comp_id) {
            $comp_id = implode('|',$comp_id);
        }
        return $comp_ids;
    }

    
    /**
     * Assigns compentices to a person based on the evaluation saved in a person scheduled training course
     * @param iHRIS_Person $parent
     * @param iHRIS_Person_Scheduled_Training_Course $pstc
     * @param I2CE_DATE  $eval_date.  If null, then we use now as the date.
     * @returns boolean.  True on success
     */
    public static function assignCompetenciesFromCourseEval($person,$pstc,$eval_date = null) {
        //perhaps the following logic should be moved to iHRIS_Person_Scheduled_Training_Couse
        if (!$person instanceof iHRIS_Person || !$pstc instanceof iHRIS_Person_Scheduled_Training_Course) {
            I2CE::raiseError("Bad arguements");
            return false;
        }
        $ff = I2CE_FormFactory::instance();
        $comp_ids = self::getAssociatedCompetencies($pstc);
        //first we get the list of competencies assigned to the course
        if (count($comp_ids) == 0) {
            //no competencies to assign.  that's ok
            return true;
        }
        //now we know the training course has competencies.
        //we need to get the course evaluation and see if it maps to a competnecy_evaluation
        $tce = $pstc->getField('training_course_evaluation');
        if (!$tce instanceof I2CE_FormField_MAP 
            || $tce->getMappedId() == '0'
            || ! ($eval = $ff->createContainer($tce->getValue()))  instanceof iHRIS_Training_Course_Evaluation) {
            return true;
        }
        $eval->populate();
        $comp_eval = $eval->getField('competency_evaluation');
        if (
            !$comp_eval instanceof I2CE_FormField_Map 
            || $comp_eval->getMappedForm() != 'competency_evaluation'
            || $comp_eval->getMappedId() == '0') {            
            $eval->cleanup();
            return true;
        } 
        //now we have a valid competency_evaluation id to  assign to personal competencies
        $success = self::assignAndEvaluateCompetencies($person->getNameId(),$comp_ids, $comp_eval->getDBValue(),$eval_date);
        $eval->cleanup();
        return $success;
    }

    /**
     * Assigns compentices to a person based on the evaluation saved in a person scheduled training course
     * @param string $person_id the id of the person form e.g. 'person|12'
     * @param array $comp_ids of string.  The ids of competencies we want to esnure a pserson has.
     * @param string $comp_eval_id.  The competentcy evaluation we wish to assign to each person.
     * @param I2CE_DATE  $eval_date.  If null, then we use now as the date.
     * @param boolean $only_update.  If true (the default) we only update existing person competencies if the last evaluation date
     * is equal to or less than $eval_date.  
     * @returns boolean.  True on success
     */
    public static function assignAndEvaluateCompetencies($person_id,$comp_ids,$comp_eval_id, $eval_date = null, $only_update =true) {        
        //lets get ids of the existing person_competncies 
        $ff = I2CE_FormFactory::instance();
        $user = new I2CE_User();
        if (!$eval_date instanceof I2CE_Date) {
            $eval_date = I2CE_Date::now();
        }
        $person_comps = array();
        foreach( I2CE_FormStorage::listFields('person_competency','competency', $person_id)  as $pers_comp_id => $data) {
            if (!array_key_exists('competency',$data)      || !$data['competency']  ) {
                continue;
            }
            $person_comps[$data['competency']] = 'person_competency|' . $pers_comp_id;
        }
        foreach ($comp_ids as $comp_id) {
            if (!array_key_exists($comp_id,$person_comps)) {
                $pers_comp = $ff->createContainer('person_competency');
                if (!$pers_comp instanceof iHRIS_PersonCompetency) {
                    I2CE::raiseError("Could not instantiatne person_competency");
                    return false;
                }
                $pers_comp->setParent($person_id);                
                $pers_comp->getField('competency')->setFromDB($comp_id);
            } else {
                $pers_comp = $ff->createContainer($person_comps[$comp_id]);
                if (!$pers_comp instanceof iHRIS_PersonCompetency) {
                    I2CE::raiseError("Could not instantiatne person_competency: " . $person_comps[$comp_id]);
                    return false;
                }
                $pers_comp->populate();                
            }
            if ($only_update 
                && $pers_comp->getField('evaluation_date')->isSetValue() 
                && $eval_date->before($pers_comp->getField('evaluation_date')->getValue())
                && ! $eval_date->equals($pers_comp->getField('evaluation_date')->getValue())) {
                $pers_comp->cleanup();
                continue;
            }
            $pers_comp->getField('competency_evaluation')->setFromDB($comp_eval_id);
            $pers_comp->evaluation_date = $eval_date;
            $pers_comp->save($user);
            $person_comps[$comp_id] = $pers_comp->getNameId(); //just in case we have the same comp_id twice in the $comp_ids array, we won't create two instances
            $pers_comp->cleanup();
        }
        return true;
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
