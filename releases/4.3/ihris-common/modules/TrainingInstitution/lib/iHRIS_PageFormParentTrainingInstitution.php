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
 * Manage adding or editing forms associated with a training institution to the database.
 * 
 * @package iHRIS
 * @subpackage Qualify
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * Page object to handle the adding or editing forms associated with a training institution to the database.
 * 
 * @package iHRIS
 * @subpackage Qualify
 * @access public
 */
class iHRIS_PageFormParentTrainingInstitution extends I2CE_PageForm {
        
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
     * Create a new instance of a page.
     * 
     * The default constructor should be called by any pages extending this object.  It creates the
     * {@link I2CE_Template} and {@link I2CE_User} objects and sets up the basic member variables.
     * @param array $args
     * @param array $request_remainder The remainder of the request path
     */
    public  function __construct( $args, $request_remainder) {
        parent::__construct( $args, $request_remainder );
        $form_name = $args['page_form'];
        if(empty($form_name)) {
            I2CE::raiseError("No form name specified", E_USER_ERROR);
        }
        $this->form_name = $form_name;
        $this->id = 0;
        if ( $this->isPost() && $this->post_exists( 'id' ) ) {
            $this->id = $this->post('id');
        } elseif ( $this->get_exists( 'id' ) ) {
            $this->id = $this->get('id');
        }
        if ( $this->isPost() && $this->post_exists( 'parent' ) ) {
            $this->parent_id = $this->post('parent');
        } elseif ( $this->get_exists( 'parent' ) ) {
            $this->parent_id = $this->get('parent');
        }
    }
        
    /**
     * Load the HTML template files for editing.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->appendFileById( "menu_view_ti.html", "li", "navBarUL", true );
        $this->template->appendFileById( "lists_form_" . $this->getForm() . ".html", "tbody", "training_institution_form" );
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
            $this->template->addFile( "button_confirm_ti.html" );     
        }
    }

    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the list object and if this is a form submission load
     * the data from the form data.  It determines the type based on the
     * {@link $type} member variable.
     */
    protected function loadObjects() {
        if ( $this->factory->exists( $this->getForm() ) ) {
            $this->setObject( $this->factory->createContainer( $this->getForm().'|'. $this->id ) );
            if ( $this->isPost() ) {
                $this->getPrimary()->load( $this->post );
                $this->parent_id = $this->getPrimary()->getParent();
            }
        }
        if ( $this->parent_id != '' && $this->getForm() != "training_institution" ) {
            $parent = $this->factory->createContainer( $this->parent_id );
            $parent->populate();
            $this->setObject( $parent, I2CE_PageForm::EDIT_PARENT );
        }
        parent::loadObjects();
    }
        
    /**
     * Set the data to be displayed for the page.
     */
    protected function setDisplayData() {
        parent::setDisplayData();
        $this->template->setDisplayData( "training_institution_header", $this->getTitle() );
        $this->template->setDisplayData( "training_institution_form", $this->getForm() );
    }
        
    /**
     * Override the default save method to redirect back the training institution page.
     */
    protected function save() {
        parent::save();
        $this->setRedirect( "view_list?type=training_institution&id=" . $this->getParent()->getNameId() );
    }
                
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
