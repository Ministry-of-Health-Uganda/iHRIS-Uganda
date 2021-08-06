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
*
* @package ihris-train
* @version v4.1
* @since v4.1
* @filesource
*/
/**
* Class iHRIS_Module_CoreCompetencies
*
* @access public
*/


class iHRIS_Module_CoreCompetencies extends iHRIS_Module_Assessment {

    
    public static function getMethods() {
        return array(
            'iHRIS_PageViewAssessment->action_core_competencies' => 'action_core_competencies',
            );
    }

    
    public function action_core_competencies($obj) {
        if (!$obj instanceof iHRIS_PageViewAssessment) {
            return;
        }
        return $obj->addChildForms('core_competencies', 'siteContent');
    }
    
 
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
