<?php
/*
 * © Copyright 2007-11 IntraHealth International, Inc.
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
 * @since v4.1.0
 * @version v4.1.0
 */

/**
 * Page object to handle the adding or editing forms associated with a user to the database.
 * 
 * @package I2CE
 * @access public
 */
class I2CE_PageFormParentUser extends I2CE_PageForm {
        
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
                I2CE::raiseError( "not a form" );
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
                $parent =  'user|' . $id;            
            }
            $primary->setParent($parent);
        }
        if ($this->isGet()) {
            $primary->load($this->get());
        }
        $view_user = $this->factory->createContainer(  $primary->getParent());
        if (!$view_user instanceof I2CE_User_Form) {
            I2CE::raiseError("Could not create user form from " . $primary->getParent());
            return;
        }
        $view_user->populate();
        $this->setObject($primary);
        $this->setObject($view_user,I2CE_PageForm::EDIT_PARENT);        
        return true;
        //parent::loadObjects();
    }
        
        
    /**
     * Load the HTML template files for editing.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->addFile( "user_form_" . $this->getForm( true ) . ".html", "tbody" );
    }

    /**
     * Display the save or confirm buttons as needed.
     * @param boolean $save Flag to show the save button.
     * @param boolean $show_edit (defaults to true)
     */
    protected function displayControls( $save = false, $show_edit =true ) {
        if ( $save ) {
            $this->template->addFile( "button_save.html" );
        } else {
            $this->template->addFile( "button_confirm_user.html" );     
        }
    }
 
    /**
     * Set the data to be displayed for the page.
     */
    protected function setDisplayData() {
        parent::setDisplayData();
        $this->template->setDisplayData( "user_header", $this->getTitle() );
        if ( !($form_link = $this->form_link)) {
            if ($this->module == 'I2CE') {
                $form_link  = $this->page;
            } else {
                $form_link  = $this->module .'/' . $this->page;
            }
        }
        $this->template->setDisplayData( "user_form", $form_link);
    }


    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited and return to the view page.
     * @global array
     */
    protected function save() {
        parent::save();
        $this->setRedirect(  "view_user?username=" . $this->getParent()->username );
    }
                
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
