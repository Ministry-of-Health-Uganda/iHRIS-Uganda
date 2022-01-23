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


class iHRIS_PageForm_Evaluate_Course_Competencies extends I2CE_PageForm{

    /**
     * @var integer The record id number of the object being edited. -- the person_scheduled_training_course
     */
    protected $id;


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
        $this->id = false;
        if ( $this->isPost() ) {
            if ( $this->post_exists( 'id' ) ) {
                $this->id = $this->post('id');
            }
        } else {
            if ( $this->get_exists( 'id' ) ) {
                $this->id = $this->get('id');
            }
        }
    }

    


    protected $evaluaions;

    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the list object and if this is a form submission load
     * the data from the form data.
     */
    protected function loadObjects() {
        $this->evaluations = array();
        //make sure we have valid objects
        $ff = I2CE_FormFactory::instance();
        if ($this->id !== false) {
            $personScheduledCourse = $ff->createContainer( $this->id );       
            if (!$personScheduledCourse instanceof iHRIS_Person_Scheduled_Training_Course) {
                I2CE::raiseError("Cannot instantitatne person scheudled training course " . $this->id);
                return false;
            }
        } else {
            $personScheduledCourse = $ff->createContainer( 'person_scheduled_training_course' );       
            if (!$personScheduledCourse instanceof iHRIS_Person_Scheduled_Training_Course) {
                I2CE::raiseError("Cannot instantitatne person scheudled training course " . $this->id);
                return false;
            }
            $personScheduledCourse->load($this->post);
            $this->id = $personScheduledCourse->getNameId();
        }
        if ($personScheduledCourse->getId() == '0') {
            I2CE::raiseError("Bad ID");
            return false;
        }
        $personScheduledCourse->populate();
        $person_id =  $personScheduledCourse->getParent();
        $person = $ff->createContainer($person_id);
        if (!$person instanceof iHRIS_Person) {
            I2CE::raiseError("No person:" .$person_id);
            return false;
        }
        $person->populate();
        $this->setObject( $personScheduledCourse , I2CE_PageForm::EDIT_SECONDARY );
        $this->setObject( $person , I2CE_PageForm::EDIT_PARENT );
        $comp_ids = iHRIS_Module_TrainingSimpleCompetency::getAssociatedCompetencies($personScheduledCourse);
        if ( count( $comp_ids ) > 0 ) {
            $person_comps = array();
            foreach( I2CE_FormStorage::listFields('person_competency','competency', $person_id)  as $pers_comp_id => $data) {
                if (!array_key_exists('competency',$data)      || !$data['competency']  ) {
                    continue;
                }
                $person_comps[$data['competency']] = 'person_competency|' . $pers_comp_id;
            }
            $person_comp_objs = array();
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
                    //don't need to populate here b/c taken care of by parent method
                }            
                $node = $this->template->loadFile("training_course_evaluation_form.html",'span');
                $pers_comp->load($this->post);
                $this->setObject($pers_comp,I2CE_PageForm::EDIT_SECONDARY,$node);
            }
        }
    }


    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited b
     * @global array
     */
    protected function save() {
        foreach( $this->objects[ self::EDIT_SECONDARY] as $obj ) {
            if ($obj instanceof iHRIS_PersonCompetency) {
                if (!$obj->save( $this->user )) {
                    return false;
                }
            }
        }
        $this->setRedirect( "view?id=" . $this->getParent()->getNameId() );
    }

    /**
     * Load the  template (HTML or XML) files to the template object.
     *  
     * 
     */  
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $evaluationsNode = $this->template->getElementById('list_evaluations');
        if (!$evaluationsNode instanceof DOMNode) {
            I2CE::raiseError("Don't know where to list evaluations");
            return false;
        }
        $comp_added = false;
        foreach ($this->node_ids[I2CE_PageForm::EDIT_SECONDARY] as $node) {
            if ($node instanceof DOMNode) {
                $comp_added = true;
                $evaluationsNode->appendChild($node);
            }
        }
        if ( !$comp_added ) {
            $this->template->addFile( "training_course_evaluation_no_competency.html", "div" );
        }
        
    }
    


    /**
     * Display the save or confirm button templates as needed.
     * @param boolean $save Flag to show the save button.
     * @param boolean $show_edit (defaults to true)
     */
    protected function displayControls( $save = false, $show_edit = true ) {
        if ( $save ) {
            $this->template->addFile( "button_save.html" );
        } else {
            $this->template->addFile( "button_confirm_notchild.html" );     
        }
    }
                


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
