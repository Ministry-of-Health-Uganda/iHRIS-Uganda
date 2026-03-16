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
 * Manage adding or editing identification details to the database.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * Page object to handle the adding or editing identification details to the database.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageFormMakeOffer extends iHRIS_PageFormParentPerson {
        
    /**
     * Return the form name for this page.
     * @param boolean $html Set to true if this is to be used for the html template page.
     * @return string
     * @see PersonPageForm
     */
    protected function getForm( $html=false ) {
        $parentObj = $this->getParent();
        if (!$parentObj instanceof iHRIS_Person) {
            I2CE::raiseError("Parent object is not a person");
            return '';
        }
        if ( $html &&  $parentObj->isActive() ) {
            return "make_promotion";
        }else {
            return "make_offer"; 
        }
    }

    /**
     * @var iHRIS_Position $old_position The old position this person held and is being promoted from.
     */
    private $old_position;
    /**
     * @var iHRIS_PersonPosition $old_person_position The old position this person held and is being promoted from.
     */
    private $old_person_position;

    /**
     * @var iHRIS_Position $new_position The new position being created if allowed.
     */
    private $new_position;


    /**
     * Check to see if the full position details should be displayed on the page.
     * @return boolean
     */
    protected function displayPositionForm() {
        return array_key_exists( 'allow_new_position', $this->args ) && $this->args['allow_new_position'];
    }

    /**
     * Load the HTML templates for this page.
     * @return boolean
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->addHeaderLink( 'make_offer_position.js' );
        $this->template->addHeaderLink( 'tabStyles.css' );
        $hide_tab = ( $this->isConfirm() ? 'position_tab' : '' );
        if ( $this->post_exists('position_option') && $this->post('position_option') == 'create' ) {
            $js = "window.addEvent('domready', function() { setupPositionNode( 'create_position', 'select_position', '$hide_tab' ); } );";
        } else {
            $js = "window.addEvent('domready', function() { setupPositionNode( 'select_position', 'create_position', '$hide_tab' ); } );";
        }
        $this->template->addHeaderLink( 'mootools-core.js' );
        $this->template->addHeaderText( $js, "script", true );
        return true;
    }

    /**
     * Set the display data for this page.
     */
    protected function setDisplayData() {
        parent::setDisplayData();
        if ( $this->post('can_edit_position') != "0" && !$this->get_exists('position') ) {
            $this->template->setDisplayData( "can_edit_position", "1" );
            $node = $this->template->getElementById('set_person_position');
            if ($node instanceof DOMElement) {
                $node->removeAttribute('noedit');
            }
        }
        if ( $this->new_position ) {
            $this->template->setDisplayDataImmediate( 'allow_new_position', true );
            $this->template->setDisplayDataImmediate( 'allow_new_position_tab', true );
        } else {
            $this->template->setDisplayDataImmediate( 'allow_new_position', false );
            $this->template->setDisplayDataImmediate( 'allow_new_position_tab', false );
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
        if (!$this->hasPermission('task(person_can_edit_child_form_person_position)')) {
            $this->userMessage("You can change a person's position");
            return false;
        }
        $pers_pos = $this->factory->createContainer('person_position|0');
        if (!$pers_pos instanceof I2CE_Form) {
            I2CE::raiseError("no person position");
            return;
        }
        $this->new_position = false;
        if ($this->isPost()) {
            if (array_key_exists('form',$this->post) && is_array($this->post['form']) 
                && array_key_exists('person_position',$this->post['form']) && is_array($this->post['form']['person_position'])
                && array_key_exists(0,$this->post['form']['person_position']) && is_array($this->post['form']['person_position'][0])
                && count($this->post['form']['person_position'][0]) == 1) {
                reset($this->post['form']['person_position'][0]);
                $pers_pos->setFromPost(current($this->post['form']['person_position'][0]));
            }
            if ( $this->post_exists('position_option') && $this->post('position_option') == 'create' ) {
                $pers_pos->getField('position')->setOption( 'required', false );
                $this->new_position = $this->factory->createContainer( 'position|0' );
                $this->new_position->setFromPost( current( $this->post['form']['position'][0] ) );
            }
            /*
            if (!$pers_pos->end_date->isValid() ) {
                $pers_pos->end_date = I2CE_Date::now();
            }
            */
        } elseif ( $this->get_exists('id') ) {
            $pers_pos->setID($this->get('id'));
            $pers_pos->populate();
        } elseif ( $this->get_exists( 'parent' ) ) {
            $pers_pos->setParent( $this->get('parent') );
        }
        if( $this->get_exists('position') ) {
            $pers_pos->getField("position")->setFromDB( $this->get('position') );
        }

        $person = $this->factory->createContainer($pers_pos->getParent());
        if (!$person instanceof I2CE_Form) {
            I2CE::raiseError("no person form :" . $pers_pos->getParent());
            return;
        }
        $person->populate();


        $this->setObject( $pers_pos , I2CE_PageForm::EDIT_PRIMARY, 'new_position' );
        $this->setObject( $person, I2CE_PageForm::EDIT_PARENT );

        I2CE_ModuleFactory::callHooks( "make_offer_display", $this,$pers_pos);
        

        $this->getParent()->populateLast( array(  "person_position" => "start_date" ) ); 
        $this->old_person_position = $this->getParent()->getLastPosition();
        if ( $this->getParent()->isActive() && $this->old_person_position instanceof iHRIS_PersonPosition ) {

            $this->old_position = $this->factory->createContainer( $this->old_person_position->getField('position')->getDBValue() );                    
            $this->old_position->populate();
            //$this->old_position->statusOnly();

            if ( $this->isPost() ) {
                $this->old_position->setFromPost( $this->post['form']['position'][ $this->old_position->getId() ] );
                //$this->old_position->setStatus( $_POST[ $this->old_position->getField('status')->getHTMLName() ] );
                //$this->old_person_position->reason = $this->getPrimary()->reason;
                $this->old_person_position->setFromPost( $this->post['form']['person_position'][ $this->old_person_position->getId() ] );
            }
            //$this->old_position->getField('status')->setMap( "position", "lookupStatus", "listDepartureOptions" );
        } else {
            $this->old_position = false;
        }

        if ( !$this->new_position && $this->displayPositionForm() && 
                ( $this->post('can_edit_position') != "0" && !$this->get_exists('position') ) ) {
            $this->new_position = $this->factory->createContainer( 'position|0' );
        }
        //I2CE::raiseMessage(print_r($this->post(),true));

    }
        
    /**
     * Set the I2CE_Form object in the page template.
     * 
     * This method will pass the edit object to the page template so that it can process all the form variables.
     */
    protected function setForm() {
        parent::setForm();
        if ( $this->old_position ) {
            $this->template->setForm( $this->old_position , 'current_position' );
            $this->template->setForm( $this->old_person_position, 'current_position' ); 
        }
        if ( $this->new_position ) {
            $this->template->setForm( $this->new_position, 'new_position' );
        }
   }

    /**
     * Checks to see if any of the forms on this page have invalid messages
     * @return boolean
     */
    public function hasInvalid() {
        $invalid = parent::hasInvalid();
        if ( $this->old_position ) {
            $invalid |= $this->old_position->hasInvalid();
            $invalid |= $this->old_person_position->hasInvalid();
        }
        if ( $this->new_position ) {
            $invalid |= $this->new_position->hasInvalid();
        }
        return $invalid;
    }


    /**
     * Run extra validation for the fields being edited.
     */
    protected function validate() {
        parent::validate();
        if ( $this->post_exists('position_option') && $this->post('position_option') == 'create' 
                && $this->new_position instanceof iHRIS_Position ) {
            $this->new_position->validate();
        }
        if ( $this->isPost() && $this->old_position instanceof iHRIS_Position) {
        	
            if ( $this->getPrimary()->start_date->compare( $this->old_person_position->start_date ) != -1 ) {
                $this->getPrimary()->setInvalidMessage('start_date','bad_date' );
            }
            if ( !$this->old_position->getField('status')->isValid() ) {
                $this->old_position->setInvalidMessage('status','required');
            }
            //I2CE::raiseMessage("in validate " . $this->old_position->getField('status')->getDBValue() );
            if (!$this->old_person_position->getField('reason')->isValid()) {
                $this->old_person_position->setInvalidMessage('reason','required');
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
        parent::displayControls( $save, $show_edit );
        $button_return = $this->template->getElementById( "button_return" );
        if ( $button_return ) {
            if ( I2CE_ModuleFactory::instance()->isEnabled( "ihris-manage-Application" ) && $this->get_exists('position') ) {
                $button_return->setAttribute( "name", "position" );
                $button_return->setAttribute( "href", "manage?action=review&position=" );
            }
        }
    }
        
    /**
     * Update the position for this to mark it as closed and then save the object.
     */ 

    public function save() {
        if (!$this->hasPermission('task(person_can_edit_child_form_person_position)')) {
            $this->userMessage("You can't change a person's position");
            return false;
        }
        $new_person_position = $this->getPrimary();
        if ( $this->post_exists('position_option') && $this->post('position_option') == 'create' 
                && $this->new_position instanceof iHRIS_Position ) {
            $this->new_position->setStatus( "position_status|closed" );
            $this->new_position->save( $this->user );
            $new_person_position->getField("position")->setFromDB( $this->new_position->getNameId() );
            $position = $this->new_position;
        } else {
            $position = $this->factory->createContainer( $new_person_position->getField("position")->getDBValue() );
            $position->statusOnly();
            $position->closePosition( $this->user, $this->getParent()->getId() );           
        }
        if ( $this->getParent()->isActive() && $this->old_position instanceof  iHRIS_Position ) {
            $this->old_position->statusOnly();
            $this->old_position->save( $this->user );

            $person = $this->getParent(); 
            if ($this->old_person_position instanceof iHRIS_PersonPosition) {
                $this->old_person_position->end_date = $new_person_position->start_date;
                $this->old_person_position->save( $this->user );
                I2CE_ModuleFactory::callHooks( "make_promotion_save", $this->old_person_position, $new_person_position );
            }          
            //$this->getPrimary()->reason = null;         
        }               
        I2CE_ModuleFactory::callHooks( "make_offer_save",  $new_person_position );
        foreach( $this->objects[ self::EDIT_CHILD] as $obj ) {
            if ( $obj->getId() == '0'  ) {
                $obj->setParent( $new_person_position);
            }
            I2CE_ModuleFactory::callHooks( "make_offer_save_" . $obj->getName(),  $new_person_position, $obj );
        }
        parent::save();
    }
                        
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
