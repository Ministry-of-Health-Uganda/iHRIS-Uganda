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
* @package i2ce
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.7
* @since v4.0.7
* @filesource 
*/ 
/** 
* Class I2CE_Module_FormBasedPermissions
* 
* @access public
*/


class I2CE_Module_FormRelationshipBasedPermissions extends I2CE_Module {
    
    public static function getMethods() {
        return array(
            'I2CE_PermissionParser->hasPermission_satisfies' => 'hasPermission_satisfies'
            );
    }
            

    public function hasPermission_satisfies($node,$args) {
        if (count($args) != 4) {
            I2CE::raiseError("Two few arguments for permision satosfoes() method");
            return null;
        }
        if (  !I2CE_MagicDataNode::checkKey($relationship = array_shift($args))) {
            I2CE::raiseError("No valid relationship specified");
            return null;
        }
        try {
            $relationshipObj = new I2CE_FormRelationship($relationship);
        } catch (Exception $e) {
            I2CE::raiseError("Relationship $relationship is not valid");
            return null;
        }
        return call_user_func_array(array($relationshipObj,'formSatisfiesRelationship'),$args);
    }


    


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
