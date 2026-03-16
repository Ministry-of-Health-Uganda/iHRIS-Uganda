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
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2
* @since v3.2
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_CustomReports_Report_ReportingFunction_Limits
* 
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingFunction_Limits extends I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Limits{

//     public function getChildType($child) {
//         return 'CustomReports_Report_ReportingForm_Field_Limit';
//     }


    

    protected function ensureLimits() {
        if ($this->ensured) {
            return;
        }        
        if ($this->storage->is_scalar()) {
            return false;
        }
        if (!$this->parent instanceof I2CE_Swiss_CustomReports_Report_ReportingFunction) {
            return false;
        }
        $fieldObj = $this->parent->getFieldObj();
        if (!$fieldObj instanceof I2CE_FormField) {
            return false;
        }
        $allowed_limits = array_keys($fieldObj->getLimitStyles());
        $excludes =   I2CE::getConfig()->getAsArray("/modules/CustomReports/limit_excludes/displayed");
        $allowed_limits = array_diff($allowed_limits,$excludes);
        foreach ($allowed_limits as $limit) {
            $swissLimit = $this->getChild($limit,true);
        }
        $this->ensured =true;
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
