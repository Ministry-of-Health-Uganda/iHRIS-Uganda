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
* @package ihris-common
* @subpackage person
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.5
* @since v4.0.5
* @filesource 
*/ 
/** 
* Class iHRIS_Module_Dependents
* 
* @access public
*/


class iHRIS_Module_Dependents extends I2CE_Module {
    /** register a fuzzy method to display dependents on the view person page   */
    public static function getMethods() {
        return array('iHRIS_PageView->action_dependent'=>'show_dependents');
    }

    /**
     * Method to display the dependent child forms on the view person page.
     * @param iHRIS_PageView $pageObject
     **/
    public function show_dependents($pageObject) {
        $pageObject->addChildForms('dependent');
        return true; 
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
