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
 * Manage adding or editing forms associated with a person to the database.
 * 
 * @package iHRIS
 * @subpackage Common
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * Page object to handle the adding or editing forms associated with a person to the database.
 * 
 * @package iHRIS
 * @subpackage Common
 * @access public
 */
class iHRIS_PageFormParentPerson extends I2CE_PageForm {
        
    /**
     * Return the form name for this page.
     * 
     * It will be used for the default form template and php page for the form submission.
     * @param boolean $html Set to true if this is to be used for the html template page to load.
     * @return string
     */
    protected function getForm( $html=false ) { return $this->form_name; }

    /**
     * @var integer The record id number of the object being edited.
     */
    protected $id;
    /**
     * @var integer The recored if number of the parent of the object being edited
     */
    protected $parent_id;
    /**
     * The form name being edited by this page.
     * @var string
     */
    protected $form_name;

    /**
     * The link used to access this form
     * $var protected string $form_link
     */
    protected $form_link = null;
        
    /**
     * Sets the form link 
     * @param string $link
     */
    public function setFormLink($link) {
        $this->form_link = $link;
    }
        


    /**
     * Checks to see if there are any permissions in the page's args for the given action.
     * If so, it evaluates them.  If not returns true.
     * @returns boolean
     */
    protected function checkActionPermission($action) {
        if (!$this->form_name) {
            return false; //weirdness.  should just stop whatever is happening
        }
        if (!parent::checkActionPermission($action)) {
            return false;
        }
        $task =   "person_can_" . $action . "_child_form_" . $this->form_name ;
        return $this->hasPermission("task($task)");
    }


    /**
     * Create a new instance of this page.
     * 
     * This will call the parent constructor and then setup the base
     * template pages for the {@link I2CE_Template template}.  It also sets up the values
     * for the member variables.
     * @param string $title The title for this page.
     * @param string $form_name The form name of the form being edited.
     * @param mixed $access The role required to access this page.
     * @param array $files The list of template files to load for this page.
     */
    public function __construct( $args,$request_remainder){
        parent::__construct($args,$request_remainder);
        $form_name = $args['page_form'];
        if(empty($form_name)) {
            I2CE::raiseError("No form name specified", E_USER_ERROR);
        }
        $this->form_name = $form_name;
    }

    /**
     * Creates and populates the parent (person) object on request variables
     * and sets the form in the page for use by any permission checking methods.
     * @return iHRIS_Person
     */
    protected function loadPerson( $parent = null ) {
        if ( !$parent && $this->request_exists( 'parent' ) ) {
            $parent = $this->request( 'parent' );
        }
        if ( strpos( $parent, '|' ) === false ) {
            I2CE::raiseError( "Deprecated use of parent variable");
            $parent = "person|$parent";
        }
        $person = $this->factory->createContainer( $parent );
        if ( !$person instanceof iHRIS_Person ) {
            I2CE::raiseError( "Could not create person form from " . $parent );
            return null;
        }
        $person->populate();
        //$this->template->setForm( $person );
        return $person;
    }
                
    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the list object and if this is a form submission load
     * the data from the form data.  It determines the type based on the
     * {@link $type} member variable.
     */
    protected function loadObjects() {
        if ($this->isPost()) {
            $primary = $this->factory->createContainer($this->getForm());
            if (!$primary instanceof I2CE_Form) {
                return false;
            }
            $primary->load($this->post);
        } elseif ( $this->get_exists('id') ) {
            if ($this->get_exists('id')) {
                $id = $this->get('id');
                if (strpos($id,'|')=== false) {
                    I2CE::raiseError("Deprecated use of id variable");
                    $id = $this->getForm() . '|' . $id;
                }
            } else {
                $id = $this->getForm() . '|0';
            }
            $primary = $this->factory->createContainer($id);
            if (!$primary instanceof I2CE_Form || $primary->getName() != $this->getForm()) {
                I2CE::raiseError("Could not create valid " . $this->getForm() . "form from id:$id");
                return false;
            }
            $primary->populate();
        } elseif ( $this->get_exists('parent') ) {
            $primary = $this->factory->createContainer($this->getForm());
            if (!$primary instanceof I2CE_Form) {
                return;
            }
            $parent = $this->get('parent');
            if (strpos($parent,'|')=== false) {
                I2CE::raiseError("Deprecated use of parent variable");
                $parent =  'person|' . $parent;            
            }
            $primary->setParent($parent);
        }
        if ($this->isGet()) {
            $primary->load($this->get());
        }
        $person = $this->loadPerson(  $primary->getParent() );
        if (!$person instanceof iHRIS_Person) {
            I2CE::raiseError("Could not create person form from " . $primary->getParent());
            return;
        }
        //$person->populate();
        $this->setObject($primary, I2CE_PageForm::EDIT_PRIMARY, null, true);
        $this->setObject($person, I2CE_PageForm::EDIT_PARENT, null, true);
        return true;
        //parent::loadObjects();
    }
        
        
    /**
     * Load the HTML template files for editing.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->appendFileById( "menu_view_link.html", "li", "navBarUL", true );
        $this->template->appendFileById( "form_" . $this->getForm( true ) . ".html", "tbody", "person_form" );
    }


    /**
     * Set the data to be displayed for the page.
     */
    protected function setDisplayData() {
        parent::setDisplayData();
        $this->template->setDisplayData( "person_header", $this->getTitle() );
        if ( !($form_link = $this->form_link)) {
            if ($this->module == 'I2CE') {
                $form_link  = $this->page;
            } else {
                $form_link  = $this->module .'/' . $this->page;
            }
        }
        $this->template->setDisplayData( "person_form", $form_link);
    }

    /**
     * Perform the action for this page.
     */
    protected function action() {
        if ( $this->get_exists('delete') && $this->get('delete') == 1 ) {
            $parent_id = $this->getPrimary()->getParent();
            $form_id = $this->getPrimary()->getNameId();
            $child_forms = $this->getPrimary()->getChildForms();
            if ( count($child_forms) > 0 ) {
                I2CE::raiseMessage("Tried to delete a child form $form_id when there are possible child forms.");
                $message = "This form can not be deleted.";
                I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_child_delete_not_allowed" );
            } else {
                $allowable = false;
                I2CE::getConfig()->setIfIsSet( $allowable, "/modules/Person/deleteable_children/" . $this->form_name );
                I2CE::raiseMessage("deleting $form_id under $parent_id requested by user " . $this->getUser()->getId());
                if ( $allowable ) {
                    if ( $this->checkActionPermission('delete') ) {
                        if ( $this->getPrimary()->delete() ) {
                            $message = "The requested form has been deleted.";
                            I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_child_delete_success" );
                        } else {
                            $message = "An error occurred deleting this form.";
                            I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_child_delete_fail" );
                        }
                    } else {
                        $message = "You do not have permission to delete this form.";
                        I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_child_delete_not_permitted" );
                    }
                } else {
                    $message = "This form can not be deleted.";
                    I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_child_delete_not_allowed" );
                } 
            }
            $this->userMessage($message);
            $this->setRedirect( "view?id=" . $parent_id );
            return true;
        } 
        return parent::action();
    }



    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited and return to the view page.
     * @global array
     */
    protected function save() {
        $saved = parent::save();
        if ($saved !== false) {
            $message = "This record has been saved.";
            I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_child_save" );
        } else {
            $message = "This record has not been saved.";
            I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_child_not_save" );            
        }
        $this->userMessage($message);
        $this->setRedirect(  "view?id=" . $this->getPrimary()->getParent() );
        return $saved;
    }
                
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
