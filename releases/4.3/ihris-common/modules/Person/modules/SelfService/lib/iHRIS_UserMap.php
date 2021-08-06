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
* @package ihirs-common
* @subpackage person
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.7
* @since v4.0.7
* @filesource 
*/ 
/** 
* Class iHRIS_UserMap
* 
* @access public
*/


class iHRIS_UserMap extends I2CE_List{

    /**
     * Gets the id of her person associated to the fiven user
     * @param mixed $user I2CE_UserForm or I2CE_USER $user, or null for the logged in user
     * @returns string, the person id in the form of "person|XXX" or '|' or failure
     * 
     */
    public static function getPersonId($user=null) {
        if ($user === null) {
            $user = new I2CE_User();
        }
        if ($user instanceof I2CE_User) {
            $factory = I2CE_FormFactory::instance();            
            $user = $factory->createContainer( "user|".$user->username());
        }
        if (!$user instanceof I2CE_User_Form) {
            return '|';
        }
        $where = array(
            'operator'=>'FIELD_LIMIT', 
            'field'=>'username', 
            'style'=>'equals', 
            'data'=>array( 
                'value'=>$user->getNameId()  //will be user|bull or something
                ) 
            );
        $maps = I2CE_FormStorage::listFields('user_map',array('parent'),true,$where);
        if (count($maps) != 1) {
            return '|';
        }
        $map= array_shift($maps);
        if (!array_key_exists('parent',$map) || !is_string($map['parent']) || !substr($map['parent'],0,7) == 'person|') {
            return '|';
        }
        return $map['parent'];        
    }

    


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
