<?php
/**
* © Copyright 2016 IntraHealth International, Inc.
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
* @package iHRIS
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.2.2
* @since v4.2.2
* @filesource 
*/ 
/** 
* Class iHRIS_Module_PersonReference
* 
* @access public
*/


class iHRIS_Module_PersonReference extends I2CE_Module {


    /**
     * Return the list of fuzzy methods supported by this module.
     * @return array
     */
    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_person_reference' => 'action_person_reference',
            );
    }

    
    /**
     * Add the personal reference forms to the page.
     * @param iHRIS_PageView $page
     * @return boolean
     */
    public function action_person_reference($page) {
        if (!$page instanceof iHRIS_PageView) {
            return false;
        }
        $page->addChildForms('person_reference'); 
        return true;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
