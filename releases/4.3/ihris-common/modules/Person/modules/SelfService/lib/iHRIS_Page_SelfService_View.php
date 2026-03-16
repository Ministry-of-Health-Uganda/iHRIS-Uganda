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
* @subpackage self-service
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.7
* @since v4.0.7
* @filesource 
*/ 
/** 
* Class iHRIS_Page_SelfService_View
* 
* @access public
*/


class iHRIS_Page_SelfService_View extends I2CE_Page_ShowReport{



    /**
     *Determine the desired displays for the indicated report view
     * @param string $view
     *@returns array of string. 
     */
    public function getDesiredDisplays($view) {
        return array('Default');
    }


    /**
     *Determine all the allowed for the indicated report view
     * @param string $view
     *@returns array of string. 
     */
    public function getAllowedDisplays($view) {
        return array('Default');
    }


    /* Check to ensure we can view the indicated report
     * @param string $view
     * @returns boolean
     */
    public function canViewReport($view) {
        if (!I2CE_MagicDataNode::checkKey($view)) {
            return false;
        }        
        $config = I2CE::getConfig()->modules->CustomReports;
        if ($config->is_scalar("reportViews/$view/limit_view_to") && $config->reportViews->$view->limit_view_to) {
            if (!$this->hasPermission(' task(custom_reports_admin) or ' . $config->reportViews->$view->limit_view_to)) {
                return false;
            }
        }
        return iHRIS_Module_SelfService::hasReport($view);
        $registered_reports = array();
        I2CE::getConfig()->setIfIsSet($registered_reports,"/modules/SelfService/reports",true);
        return in_array($view,$registered_reports);
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
