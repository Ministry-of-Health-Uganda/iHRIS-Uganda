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
* @package I2CE
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.6
* @since v4.0.6
* @filesource 
*/ 
/** 
* Class I2CE_Module_Fields
* 
* @access public
*/


class I2CE_Module_Fields extends I2CE_Module {

    public static function getHooks() {
        return array(
            'validate_formfield'=>'validate_formfield'
            );         
    }

    

    /**
     * Hooked Function to check if a fieldObj is valid
     * @param I2CE_FormField $fieldObj
     */
    public function validate_formfield($fieldObj) {
        if ($fieldObj->hasOption('required') && $fieldObj->getOption('required') && !$fieldObj->isValid()) {
            $fieldObj->setInvalidMessage("required");
        }
    }





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
