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
*  iHRIS_PageForm_Person_Scheduled_Training_Course
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


class iHRIS_PageForm_Person_Scheduled_Training_Course extends I2CE_PageForm{

    /**
     * @var integer The record id number of the object being edited. -- the person_scheduled_training_course
     */
    protected $id;


    /**
     * @var integer $person_id
     */
    protected $person_id;

    /**
     * Create a new instance of this page.
     * 
     * This will call the parent constructor and then setup the base
     * template pages for the {@link I2CE_Template template}.  It also sets up the values
     * for the member variables.
     * @param string $title The title for this page.
     * @param string $defaultHTMLFile The default HTML file for this page.
     * @param mixed $access The role required to access this page.
     * @param array $files The list of template files to load for this page.
     */
    public function __construct( $args,$request_remainder) {
        parent::__construct( $args,$request_remainder);
        $this->person_id = false;
        $this->id = "person_scheduled_training_course|0";
        if ( $this->isPost() ) {
            if ( $this->post_exists( 'parent' ) ) {
                $this->person_id = $this->post('parent');
            }           
            if ( $this->post_exists( 'id' ) ) {
                $this->id = $this->post('id');
            }
        } else {
            if ( $this->get_exists( 'parent' ) ) {
                $this->person_id = $this->get('parent');
            }
            if ( $this->get_exists( 'id' ) ) {
                $this->id = $this->get('id');
            }
        }
    }

    
    

    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the list object and if this is a form submission load
     * the data from the form data.
     */
    protected function loadObjects() {
        if (!$this->permissionParser->hasTask('person_can_edit_child_form_person_scheduled_training_course') ) {
            $this->userMessage("You do not have permission to add or edit a person's scheduleing of training course",'notice',true);
            $this->setRedirect('noaccess');
            return false;
        }
        $factory = I2CE_FormFactory::instance();
        $personScheduledCourse = $factory->createContainer( $this->id );
        if (!$personScheduledCourse instanceof I2CE_Form) {
            return false;
        }
        $this->setObject( $personScheduledCourse);
        if ($personScheduledCourse->getId() != '0') {
            $personScheduledCourse->populate();
            $this->person_id =  $personScheduledCourse->getParent();
            if ($personScheduledCourse->getField('request_date')->issetValue()) {
                $personScheduledCourse->request_date = I2CE_Date::now();
            }
        }

        if ($this->person_id === false) {
            return false;
        }
        $parent = $factory->createContainer($this->person_id );
        if ($parent instanceof I2CE_Form) {
            $parent->populate();
            $this->setObject( $parent , I2CE_PageForm::EDIT_PARENT );
        }
        parent::loadObjects();
    }

    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited and return to the view page.
     * @global array
     */
    protected function save() {
        if (!$this->permissionParser->hasTask('person_can_edit_child_form_person_scheduled_training_course') ) {
            $this->userMessage("You do not have permission to add or edit a person's scheduleing of training course",'notice',true);
            $this->setRedirect('noaccess');
            return false;
        }
        parent::save();
        $this->setRedirect(  "view?id=" . $this->getParent()->getNameId() );
        if (!I2CE_ModuleFactory::instance()->isEnabled('training-simple-competency')) {
            return;
        }
        if (!iHRIS_Module_TrainingSimpleCompetency::assignCompetenciesFromCourseEval($this->getParent(),$this->getPrimary())) {
            I2CE::raiseError("Could not update person competncies for" .  $this->getParent()->getNameId() . " from " . $this->getPrimary()->getNameId());
        }      
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
