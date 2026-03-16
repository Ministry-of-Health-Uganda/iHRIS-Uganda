<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
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
 * Manage adding or editing salary details to the database.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>/Carl Leitner<litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * Page object to handle the adding or editing salary details to the database.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageFormSalary extends I2CE_PageForm {
    /**
     * Return the default HTML file used by this page.
     * @return string
     */
    protected function getDefaultHTMLFile() { return "form_person_base.html"; }
        
    /**
     * Return the title for this page.
     * @return string
     */
    public function getTitle() { return "Salary Change"; }


    /**
     * @var integer The id of the previous salary record
     */
    private $last_id;
    /**
     * @var I2CE_Form the {@link iHRIS_Salary} object for the previous record.
     */
    private $last;
    /**
     * Return the form name for this page.
     * @param boolean $html Set to true if this is to be used for the html template page.
     * @return string
     * @see PersonPageForm
     */
    protected function getForm( $html=false ) {
        if ( $html && $this->last instanceof I2CE_Form ) {
            return "salary_change";
        } else {
            return "salary"; 
        }
    }
    /**
     * @var integer The record id number of the object being edited.
     */
    protected $id;
    /**
     * @var integer The record id number of the parent person_position of the object being edited
     */
    protected $position_id;

    /**
     * @var integer The record id number of the person of the object being edited
     */
    protected $person_id;


    /**
     * @var Person person  The person of the object being edited
     */
    protected $person;

    /**
     * @var PersonPosition $personPosition The record id number of the person of the object being edited
     */
    protected $person_position;

    /**
     * Create a new instance of this page.
     * 
     * This will call the parent constructor and then setup the base
     * template pages for the {@link Template template}.  It also sets up the values
     * for the member variables.
     */
    /**
     * Create a new instance of this page.
     * 
     * This will call the parent constructor and then setup the base
     * template pages for the {@link Template template}.  It also sets up the values
     * for the member variables.
     */
    public function __construct($args,$request_remainder) {
        parent::__construct($args,$request_remainder);
        $this->id = 0;
        if ( $this->isPost() && $this->post_exists( 'id' ) ) {
            $this->id = $this->post('id');
        } elseif ( $this->get_exists( 'id' ) ) {
            $this->id = $this->get('id');
        }
        $this->position_id = "";
        $this->last_id = "";
        $this->person_id = "";

        if ( $this->isPost() ) {
            $salary = $this->factory->createContainer( $this->getForm() );
            if ( $salary instanceof I2CE_Form ) {
                $salary->load( $this->post );
                $this->person_position = $this->factory->createContainer( $salary->getParent() );
                $this->last_id = $salary->last;
            }
        } elseif ( $this->get_exists( 'parent' ) ) {
            $this->position_id = $this->get('parent');
            if (strlen($this->position_id) > 0) {
                $this->person_position = $this->factory->createContainer($this->position_id);
            }
            if ( $this->get_exists( 'last' ) ) {
                $this->last_id = $this->get('last');
            }
        } 
        if ( strlen($this->last_id) > 0 ) {
            $this->last = $this->factory->createContainer( $this->last_id );
            $this->last->populate();
        }
        if ($this->person_position instanceof iHRIS_PersonPosition) {
            $this->person_position->populate();
            $this->person_id = $this->person_position->getParentID();
        } 

    }

    /**
     * Load the HTML template files for editing.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->appendFileById( "menu_view_link.html", "li", "navBarUL", true );
        $this->template->appendFileById( "form_" . $this->getForm( true ) . ".html", "tbody", "person_form" );
    }


    
    protected $position = null;
    /**
     * Initializes any data for the page
     * @returns boolean.  True on sucess. False on failture
     */
    protected function initPage() {
        if ($this->person_position instanceof iHRIS_PersonPosition) {
            $this->person_position->populate();
            $this->setObject($this->person_position, I2CE_PageForm::EDIT_PARENT);
        }
        $this->person = $this->factory->createContainer( "person".'|'. $this->person_id );
        if ($this->person instanceof iHRIS_Person) {
            $this->person->populate();
        }
        if ( $this->factory->exists( $this->getForm() ) ) {
            $this->setObject( $this->factory->createContainer( $this->getForm().'|'. $this->id ), I2CE_PageForm::EDIT_PRIMARY, 'new_salary' );
            $this->getPrimary()->populate(); 
        }
        if ( strlen($this->last_id) > 0 ) {
            $this->getPrimary()->last = $this->last_id;
        }
        if ( $this->last instanceof iHRIS_Salary ) {
            $this->template->setForm( $this->last,'current_salary' );
        } 
        if ($this->person instanceof iHRIS_Person) {
            $this->template->setForm($this->person);
        }
        if (!$this->hasPermission('task(person_can_edit_child_form_salary)')) {
            $this->userMessage("You do not have access to edit the salary",'notice',false);
            return false;
        }
        if (!parent::initPage()) {
            return false;
        }
        if ( $this->isPost()) {
            if ($this->last instanceof iHRIS_Salary ) {
                $this->last->end_date = $this->getPrimary()->start_date;
            } else { //you are trying to change an existing salary
                if (!$this->hasPermission('task(person_can_change_child_form_salary)')) {
                    $this->userMessage("You do not have access to edit the salary",'notice',false);
                    return false;
                }
            }
        }
        if ($this->last instanceof iHRIS_Salary) {
            if ($this->person_position instanceof iHRIS_PersonPosition) {
                $end_date = $this->person_position->getField('end_date');
                if ($end_date->isValid()) {
                    $this->getPrimary()->end_date = $end_date;
                }
            }
        }
        return true;
    }
        
        
    /**
     * Set the data to be displayed for the page.
     */
    protected function setDisplayData() {
        parent::setDisplayData();
        $this->template->setDisplayData( "person_header", $this->getTitle() );
        $this->template->setDisplayData( "person_form", $this->getForm()  );
    }




    /**
     * Run the validation methods for all the objects being edited.
     * 
     * If this is a form submit then run the validation methods for the default object being edited.  The default method
     * calls the {@link I2CE_Form::validate() validate} method on the {@link $edit_obj} object.
     */
    protected function validate() {
        parent::validate();
        if ( $this->isPost() ) {
            if ( $this->last instanceof iHRIS_Salary) {
                if ( $this->getPrimary()->start_date->before( $this->last->start_date ) ) {
                    $this->getPrimary()->setInvalidMessage('start_date','bad_date');
                }
            }
        }
    }



    /**
     * Display the save or confirm buttons as needed.
     * 
     * If the page is a confirmation view then the save / edit button template will be displayed.  
     * Otherwise the confirm and return buttons will be shown.
     * @param boolean $save Flag to show the save button. (Defaults to false)
     * @param boolean $show_edit (defaults to true)
     * @global array
     */
    protected  function displayControls( $save = false, $show_edit = true ) {
        parent::displayControls($save,$show_edit);
        $node = $this->template->getELementById('button_return');
        if ($node instanceof DOMElement) {
            $node->setAttribute('href','view?id=person|' . $this->person_id . '&person_position='); //cheating a bit here.
        }
    }


    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited and return to the view page.  If the action needs to be 
     * logged then the {@link log} method is also called.  Any pages overriding this default save method
     * will need to include any logging necessary.
     */
    protected function save() { 
        $save = true;
        if ( $this->last instanceof iHRIS_Salary ) {
            if (!$this->hasPermission('task(person_can_edit_child_form_salary)')) {
                $this->userMessage("You cannot change a person's salary",'notice',true);
                $save = false;
            } 
            $this->last->save( $this->user );
        } else {
            if (!$this->hasPermission('task(person_can_change_child_form_salary)')) {
                $this->userMessage("You cannot change a person's existing salary",'notice',true);
                $save = false;
            } 
        }
        if ($save) {
            parent::save();
        }
        $this->setRedirect( "view?id=person|" . $this->person_id );
    }   

}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
