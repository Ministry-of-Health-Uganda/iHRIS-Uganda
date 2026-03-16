<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
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
* @subpackage Manage
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2.6
* @since v3.2.6
* @filesource 
*/ 
/** 
* Class iHRIS_Module_Benefit
* 
* @access public
*/


class iHRIS_Module_ManageSiteDemo extends I2CE_Module {

    /**
     * Run the upgrade for this module.  
     * @param string $old_vers
     * @param string $new_vers
     * @return boolean
     */
    public function upgrade( $old_vers, $new_vers ) {
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '3.2.5' ) ) {
            I2CE::raiseError( "Disabling training module." );
            I2CE_ModuleFactory::instance()->disable( array( "manage-training-simple-competency", 
                        "manage-training-institution", "manage-training-course",
                        "training-simple-competency", "training-institution",
                        "training-course" ) );
        }
        return parent::upgrade( $old_vers, $new_vers );
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
