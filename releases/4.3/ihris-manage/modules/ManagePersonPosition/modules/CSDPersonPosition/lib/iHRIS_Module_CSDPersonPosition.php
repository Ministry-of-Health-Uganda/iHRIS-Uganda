<?php 
/**
 * © Copyright 2018-present IntraHealth International, Inc.
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
 *  iHRIS_Module_CSDPersonPosition
 * @package iHRIS
 * @subpackage Manage
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @version 4.3.0
 * @since 4.3.0
 * @access public
 */


class iHRIS_Module_CSDPersonPosition extends I2CE_Module {


    public function post_update( $old_vers, $new_vers ) {
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '4.3.0' ) ) {
            if ( !$this->update_person_position_uuids()) {
                return false;
            }
        }
        return true;
    }


    protected function update_person_position_uuids() {
        return iHRIS_Module_CSDCache::add_uuids('person_position');
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
