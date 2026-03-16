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
 * Manage editing person position departure in the database.
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
 * Page object to handle the editing of person position departure in the database.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageFormDeparture extends iHRIS_PageFormParentPerson  {
        
    /**
     * @var I2CE_Form The position object this person has been assigned to.
     */
    private $position;


    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the list object and if this is a form submission load
     * the data from the form data.  It determines the type based on the
     * {@link $type} member variable.
     */
    protected function loadObjects() {
        $pers_pos = $this->factory->createContainer('person_position|0');
        if (!$pers_pos instanceof I2CE_Form) {
            return;
        }
        if ($this->isPost()) {
            $pers_pos->load($this->post);
            if (!$pers_pos->end_date->isValid() ) {
                $pers_pos->end_date = I2CE_Date::now();
            }
        } else {
            $pers_pos->setID($this->get('id'));
            $pers_pos->populate();
        }
        $person = $this->factory->createContainer( $pers_pos->getParent());
        if (!$person instanceof I2CE_Form) {
            return;
        }
        $person->populate();
        $this->setObject($pers_pos);
        $this->setObject($person,I2CE_PageForm::EDIT_PARENT);        

        $this->position = $this->factory->createContainer( $this->getPrimary()->getField("position")->getDBValue(), true );
        $this->position->statusOnly();
        if ( $this->isPost() ) {
            //$this->position->setStatus( $_POST[ $this->position->getField('status')->getHTMLName() ] );
            $this->position->setFromPost( $this->post );
        }
    }

    /**
     * Set the I2CE_Form object in the page template.
     * 
     * This method will pass the edit object to the page template so that it can process all the form variables.
     */
    protected function setForm() {
        parent::setForm();
        $this->template->setForm( $this->position );
    }

    /**
     * Run extra validation for the fields being edited.
     */
    protected function validate() {
        parent::validate();
        if ( $this->isPost() ) {
            if ( !$this->getPrimary()->getField('end_date')->isValid() ) {
                $this->getPrimary()->setInvalidMessage('end_date','required');
            }
            if ( !$this->position->getField('status')->isValid() ) {
                $this->position->setInvalidMessage('status','required');
            }
            if ( !$this->getPrimary()->getField('reason')->isValid() ) {
                $this->getPrimary()->setInvalidMessage('reason','required' );                            
            }
        }
    }

    /**
     * Update the position for this to mark it as closed and then save the object.
     */ 
    public function save() {
        $this->position->save( $this->user );
        if ( ($personPosition = $this->getPrimary()) instanceof iHRIS_PersonPosition) {
            I2CE_ModuleFactory::callHooks( "depart_position_save", $personPosition );
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
