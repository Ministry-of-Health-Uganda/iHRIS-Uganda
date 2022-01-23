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
 * Edit participants for a training
 * @package iHRIS
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org> / Carl Leitner <litlfred@ibiblio.org> for pulling form Train into Common
 * @copyright Copyright &copy; 2012 IntraHealth International, Inc. 
 * @since v4.1.3
 * @version v4.1.3
 */

/**
 * The page class for editing particpants for a training
 * @package iHRIS
 * @subpackage Train
 * @access public
 */
class iHRIS_PageEditStudents extends I2CE_PageReportAction { 

    /**
     * @var iHRIS_Scheduled_Training_Course  The training course instance
     */
    protected $scheduled_training_course;

    /**
     * @var I2CE_FormFactory The form factory.
     */
    protected $factory;

    /**
     * @var array The list of students for this training.
     */
    protected $students;

    /**
     * Return the action text to display in each cell based on the fields passed.
     * @param array $fields The field values for this row.
     * @return string
     */
    public function getActionText( $fields ) {
        if ( array_key_exists( $fields[0], $this->students ) && $this->students[$fields[0]] == 1  ) {
            $action = "Remove Student";
            I2CE::getConfig()->setIfIsSet($action,"/modules/training-course/translatable-strings/remove_student");
        } else {
            $action = "Add Student";
            I2CE::getConfig()->setIfIsSet($action,"/modules/training-course/translatable-strings/add_student");
        }
        return $action;
    }

    /**
     * Return the arguments to pass to the action method.
     * These arguments should be ready to pass directly to the javascript
     * method so must be quoted and escaped if needed.
     * @return array
     */
    public function getActionArguments() {
        return array( 'this', "'" . $this->scheduled_training_course->getNameId() . "'" );
    }

    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        if ( !parent::action() ) {
            return false;
        }
        if (!$this->hasPermission("task(person_can_edit_child_form_person_scheduled_training_course)")) {
            $no_edit = "You do not have permission to edit students for this course instance.";
            I2CE::getConfig()->setIfIsSet($no_edit,"/modules/training-course/translatable-strings/no_edit_students");
            $this->userMessage($no_edit);
            return false;
        }

        $this->template->addHeaderLink("view.js");
        //$this->template->appendFileById( "menu_view.html", "li", "navBarUL", true );
        $this->factory = I2CE_FormFactory::instance();
        if (!$this->get_exists('id')) {
            $this->userMessage("Invalid Scheduled Training Course  Requested");
            return false;
        }
        if ($this->get_exists('id')) {
            $id = $this->get('id');
            if (strpos($id,'|')=== false) {
                I2CE::raiseError("Deprecated use of id variable");
                $id = 'scheduled_training_course|' . $id;
            }
        } else {
            $id = 'scheduled_training_course|0';
        }
        if (! ($this->scheduled_training_course = $this->factory->createContainer( $id )) instanceof I2CE_Form) {
            return false;
        }

        $this->scheduled_training_course->populate();
        $this->template->setForm( $this->scheduled_training_course );

        /*$job = $this->sh->getField('job');
        // Note, this will fail if the request() method has been called on this page since that
        // caches all the get/post details and it can't be changed after that without changing core code.
        // lduncan@intrahealth.org
        //
        
        if ( !$this->post_exists('limits') &&
                !$this->get_exists('limits') ) {
            if ( $job && $job instanceof I2CE_FormField_MAP_MULT ) {
                if ( $job->isValid() ) {
                    $this->get['limits']['position+job']['in']['value'] = explode( ',', $job->getDBValue() );
                }
            } else {
                I2CE::raiseError("Invalid job from provider instance to limit students.");
            }
        }
        */
        $this->setupStudents();
        $this->showStudents();
        if (! ( $this->actionReport( "id=$id" ))) {
            return false;
        }
        $this->template->appendFileByID("edit_students_return.html", "div", "siteContent");
        return true;
    }

    protected function showStudents() {
        if (! ($listNode = $this->template->getElementByID("existing_student_list")) instanceof DOMNode) {
            return ;
        }
        foreach ($this->students as $formid=>$data) {
            list($form,$id) = array_pad(explode("|",$formid,2),2,"");
            if ($form != "person" || ! $id) { 
                continue;
            }
            $student_name = I2CE_FormStorage::lookupField("person",$id,array('firstname' , 'surname'), " ");
            $aNode =$this->template->createElement("a",array(href=>"view?id=" . $formid),$student_name);
            $liNode =$this->template->createElement("li");
            $this->template->appendNode($aNode,$liNode);
            $this->template->appendNode($liNode,$listNode);
        }
    }

    /**
     * Setup the list of all students for this instance.
     */
    protected function setupStudents() {
        if ( !is_array( $this->students ) ) {
            $this->students = array();
            $where_data = array(
                    'operator' => 'FIELD_LIMIT',
                    'field' => 'scheduled_training_course',
                    'style' => 'equals',
                    'data' => array(
                        'value' => $this->scheduled_training_course->getNameId()
                        )
                    );
            $students = I2CE_FormStorage::listFields( 'person_scheduled_training_course', array('parent','attending'), false, $where_data );
            foreach( $students as $s_instance => $s_data ) {
                $this->students[$s_data['parent']] = ($s_data['attending'] == 1 || $s_data['attending'] === null);
            }
        } else {
            I2CE::raiseError( "setupStudents was called twice, so nothing was done the second time." );
        }

    }

    
}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
