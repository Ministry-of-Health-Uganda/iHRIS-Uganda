<?php
/**
* © Copyright 2007 IntraHealth International, Inc.
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
*  iHRIS_PageFormPerson
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @copyright Copyright &copy; 2007 IntraHealth International, Inc. 
* This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
* the terms of the GNU General Public License as published by the Free Software Foundation; either 
* version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
* that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
* received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
* @version 2.1
* @access public
*/


class iHRIS_PageFormPerson extends I2CE_PageForm{



    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the list object and if this is a form submission load
     * the data from the form data.
     */
    protected function loadObjects() {
        $factory = I2CE_FormFactory::instance();
        if ($this->isPost()) {
            $person = $factory->createContainer('person');
            if (!$person instanceof iHRIS_Person) {
                I2CE::raiseError("Could not create person form");
                return;
            }
            $person->load($this->post);
            if (($surname_ignore = $person->getField('surname_ignore')) instanceof I2CE_FormField
                && ($surname = $person->getField('surname')) instanceof I2CE_FormField
                ) {
                $ignore_paths = array( $surname->getHTMLName('ignore') , $surname_ignore->getHTMLName());
                foreach ($ignore_paths as $ignore_path) {
                    $ignore_path = explode('[', $ignore_path );
                    foreach ($ignore_path as &$comp) {
                        $comp = rtrim($comp,']');
                    }
                    unset($comp);
                    if ( $this->post_exists($ignore_path)) {                    
                        $surname_ignore->setFromDB($this->post($ignore_path));
                    }
                }
            }
        } else {
            if ($this->get_exists('id')) {
                $id = $this->get('id');
                if (strpos($id,'|')=== false) {
                    I2CE::raiseError("Depcreated use of id variable");
                    $id = 'person|' . $id;
                }
            } else {
                $id = 'person|0';
            }
            $person = $factory->createContainer($id);
            if (!$person instanceof iHRIS_Person) {
                I2CE::raiseError("Could not create valid person form from id:$id");
                return;
            }
            $person->populate();
            $person->load($this->request());
        }
        $this->setObject( $person, I2CE_PageForm::EDIT_PRIMARY, null, true );
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
            I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_save" );
        } else {
            $message = "This record has not been saved.";
            I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_not_save" );            
        }
        $this->userMessage($message);
        $this->setRedirect(  "view?id=" . $this->getPrimary()->getNameId() );
        return $saved;
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
            $this->template->addFile( 'button_confirm_notchild.html' );
        }               
    }               


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
