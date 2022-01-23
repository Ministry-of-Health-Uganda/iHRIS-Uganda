<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @package botswana
* @subpackage 
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.1
* @since v4.0.1
* @filesource 
*/ 
/** 
* Class Botswana_ProfDev
* 
* @access public
*/


class iHRIS_Module_RecordStatus extends I2CE_Module {

    
    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_person_record_status' => 'action_person_record_status',
            );
    }


    public function action_person_record_status($obj) {
        if (!$obj instanceof iHRIS_PageView) {
            return;
        }
        $person = $obj->getPerson();
        if (!$person instanceof iHRIS_Person) {
            return false;
        }
        $obj->addChildForms('person_record_status');
        $template = $obj->getTemplate();
        $recordStatusObjs = $person->getChildren('person_record_status');
        if (count($recordStatusObjs) == 1) {
            return true;
        }  else if (count($recordStatusObjs) > 1) {
            I2CE::raiseError("Too many record status forms  for " . $person->getId());
            return false;
        } else if ($obj->hasPermission("task(person_can_edit_child_form_person_record_status)")) {
            $template->appendFileById("view_person_record_status_link.html", 'span','record_status_links');
        }
        return true; 

    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
