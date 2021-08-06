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
* @package ihris manage
* @subpackage self service
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.7.1
* @since v4.0.7.1
* @filesource 
*/ 
/** 
* Class iHRIS_Module_ManageSelfServiceApplication
* 
* @access public
*/


class iHRIS_Module_ManageSelfServiceApplication extends I2CE_Module {


    public function hasOpenPositionReport() {
        $open_position_report = false;
        return (I2CE::getConfig()->setIfIsSet($open_position_report,'/modules/ManageSelfServiceApplication/open_position_report') && $open_position_report);
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
