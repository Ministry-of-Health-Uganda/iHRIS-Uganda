<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
*  I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Limits
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Limits extends I2CE_Swiss_CustomReports_Report_Base {

    public function getChildType($child) {
        return 'CustomReports_Report_ReportingForm_Field_Limit';
    }



    
    
    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_report_limits.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add reported form template");
            return false;
        }        
        $appendNode = $this->template->getElementById('limits_contents',$mainNode);
        if( !$appendNode instanceof DOMNode) {
            I2CE::raiseError("Could not find id=limit_options element");
            return false;
        }
        //$this->displayLimitDefaults($mainNode,$action);
        if ($action == 'edit') {
            $this->renameInputs('*',$mainNode);
        }
        foreach ($this as $limit=>$swissLimit) {
            $limitNode = $this->template->appendFileByNode('customReports_report_limits_each.html','tbody',$appendNode);                
            $swissLimit->addLink('limit_contents','limit_fill',$limitNode,$action, $transient_options);            
        }
        return true;
    }


    public function rewind() {
        $this->ensureLimits();
        parent::rewind();
    }

    protected $ensured = false;

    protected function ensureLimits() {
        if ($this->ensured) {
            return;
        }        
        if ($this->storage->is_scalar()) {
            return false;
        }
        if (!$this->parent instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
            return false;
        }
        $factory = I2CE_FormFactory::instance();
        $formName = $this->parent->getForm();
        $formObj = $factory->createForm($formName);
        if (!$formObj instanceof I2CE_Form) {
            I2CE::raiseError("Could not instantiate the form $formName  at " . $this->configPath);
            return false;
        }
        $field = $this->parent->getName();        
        $allowed_limits = $formObj->getLimitStyles($field);
        $excludes =   I2CE::getConfig()->getAsArray("/modules/CustomReports/limit_excludes/displayed");
        foreach ($allowed_limits as $limit=>$data) {
            if (in_array($limit,$excludes)) {
                continue;
            }
            $swissLimit = $this->getChild($limit,true);
            if (is_array($data) && count($data) == 1 && in_array('value',$data)) {
                $swissLimit->setAllowPivot(true);
            } else {
                $swissLimit->setAllowPivot(false);
            }

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
