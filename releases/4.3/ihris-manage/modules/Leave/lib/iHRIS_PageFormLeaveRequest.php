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


class iHRIS_PageFormLeaveRequest extends iHRIS_PageFormParentPerson{



    /**
     * Create and load data for the objects used for this form.
     *
     * Create the list object and if this is a form submission load
     * the data from the form data.
     */
    protected function loadObjects() {
        $this->factory = I2CE_FormFactory::instance();
        if ($this->isPost()) {
            $primary = $this->factory->createContainer($this->getForm());
            if (!$primary instanceof I2CE_Form) {
                I2CE::raiseError("Could not create leave_request form");
                return;
            }
            $primary->load($this->post);
            $start_date = strtotime($primary->getField("start_date")->getDBValue());
            $end_date = strtotime($primary->getField("end_date")->getDBValue());
            $days = floor(($end_date-$start_date)/(60*60*24)) + 1;
            $primary->getField("leave_days")->setValue($days);
            $primary->getField("leave_status")->setFromDB("leave_status|3");
        }
        else if ($this->get_exists('id')) {
          $id = $this->get('id');
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
        }
        elseif ( $this->get_exists('parent') ) {
          $primary = $this->factory->createContainer($this->getForm());
          if (!$primary instanceof I2CE_Form) {
              return;
          }
          $parent = $this->get('parent');
          if (strpos($parent,'|')=== false) {
              I2CE::raiseError("Deprecated use of parent variable");
              $parent =  'person|' . $parent;
              echo "returning6";
          }
          $primary->setParent($parent);
        }
        if ($this->isGet()) {
            $primary->load($this->get());
        }
        $person = parent::loadPerson(  $primary->getParent() );
        $this->setObject( $person, I2CE_PageForm::EDIT_PARENT);
        $this->setObject( $primary, I2CE_PageForm::EDIT_PRIMARY);
    }

    /**
     * Save the objects to the database.
     *
     * Save the default object being edited and return to the view page.
     * @global array
     */
    protected function save() {
        $leave_request = $this->factory->createContainer("leave_request");
        $leave_request->load($this->post);
        parent::save();
        $this->userMessage("Leave Request Successfully Submitted To Supervisor");
        $this->setRedirect(  "view?id=" . $leave_request->getParent() );
        $leave_request->cleanup();
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
