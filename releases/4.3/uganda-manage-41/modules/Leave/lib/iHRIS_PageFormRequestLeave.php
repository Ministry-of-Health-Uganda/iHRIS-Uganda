<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
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
*  iHRIS_PageFormRequestLeave
* @package I2CE
* @subpackage NMS Manage
* @author Luke Duncan <lduncan@intrahealth.org>
* @copyright Copyright &copy; 2014 IntraHealth International, Inc. 
* This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
* the terms of the GNU General Public License as published by the Free Software Foundation; either 
* version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
* that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
* received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
* @version 4.2
* @access public
*/


class iHRIS_PageFormRequestLeave extends I2CE_PageForm {


    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the list object and if this is a form submission load
     * the data from the form data.
     */
    protected function loadObjects() {
        $factory = I2CE_FormFactory::instance();
        if ($this->isPost()) {
            $leave = $factory->createContainer('personleave');
            if (!$leave instanceof iHRIS_PersonLeave) {
                I2CE::raiseError("Could not create leave form");
                return;
            }
            $leave->load($this->post);
        } else {
            $leave = $factory->createContainer("personleave");
            if (!$leave instanceof iHRIS_PersonLeave) {
                I2CE::raiseError("Could not create valid leave form.");
                return;
            }
            //$leave->load($this->request());
        }
        $this->setObject( $leave, I2CE_PageForm::EDIT_PRIMARY, null, true );
    }

    /**
     * Validate the forms for this page.
     */
    protected function validate() {
        if ( $this->isPost() ) {
            $leave = $this->getPrimary();

            if ( !I2CE_Validate::checkString( $leave->person_id ) ) {
                $leave->getField('person_id')->setInvalid( "You must enter your employee ID to request leave." );
            } else {
                $find_person = array(
                        'operator' => 'AND',
                        'operand' => array( 
                            array( 
                                'operator' => 'FIELD_LIMIT',
                                'style' => 'lowerequals',
                                'field' => 'id_num',
                                'data' => array( 'value' => $leave->person_id ),
                                ),
                            array(
                                'operator' => 'FIELD_LIMIT',
                                'style' => 'equals',
                                'field' => 'id_type',
                                'data' => array( 'value' => 'id_type|1' ),
                                ),
                            ),
                        );
                $pid = I2CE_FormStorage::search( "person_id", false, $find_person, array(), true );
                if ( $pid ) {
                    $person_id = $this->factory->createContainer( "person_id|$pid" );
                    $person_id->populate();
                    $parent = $person_id->getParent();
                    $leave->setParent( $parent );
                } else {
                    $leave->getField('person_id')->setInvalid( "Unable to match your employee ID.  Please check for errors." );
                }
            }
        }
        parent::validate();
    }

    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited and return to the view page.
     * @global array
     */
    protected function save() {
        parent::save();
        //$message = "Your leave has been requested.";
        //I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_save" );
        //$this->userMessage($message);
        $this->template->addFile( "request_leave_success.html" );
        //$this->setRedirect(  "view?id=" . $this->getPrimary()->getNameId() );
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
        if ( $save ) {
            parent::displayControls( $save, $show_edit );
        }  else {       
            $this->template->addFile( 'button_confirm_only.html' );
        }               
    }               


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
