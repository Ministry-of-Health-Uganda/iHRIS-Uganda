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
 * Manage adding or editing licenses to the database.
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
 * Page object to handle the adding or editing licenses to the database.
 * 
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageFormPersonAttendance extends iHRIS_PageFormParentPerson {
                
    /**
     * Create and load data for the objects used for this form.
     * 
     * Set the default person_position to be the same as the position for this
     * person.
     */
    protected function loadObjects() {
        parent::loadObjects();       
      if ( !$this->isPost()) {
            $parent = $this->getParent();
            $parent->populateLast( array( "person_position" => "start_date" ));
            $person_position_form = current( $parent->children['person_position'] );
                if ( $person_position_form ){
	                $this->getPrimary()->position = $person_position_form->position;  
            	}
         
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
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
