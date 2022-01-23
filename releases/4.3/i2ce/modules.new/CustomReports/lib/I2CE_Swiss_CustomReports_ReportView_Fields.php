<?php
/**
 * @copyright © 2009 Intrahealth International, Inc.
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
*  I2CE_Swiss_CustomReports_ReportView_Fields
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_ReportView_Fields extends I2CE_Swiss_CustomReports_ReportView_Base{
    
    public function getChildType($child) {
        return 'CustomReports_ReportView_Field';
    }



    public function rewind() {
        $this->ensureFields();
        parent::rewind();

    }




    public function displayValues($contentNode, $transient_options, $action) {
        $this->ensureFields();
        $mainNode = $this->template->appendFileByNode('customReports_reportView_fields.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not load report views template");
            return false;
        }   
        $appendNode = $this->template->getElementById('fields_list',$mainNode);
        if (!$appendNode instanceof DOMNode) {
            I2CE::raiseError("Dont know where to add fields");
            return false;
        }   
        if (array_key_exists('field_order',$transient_options) && is_array($transient_options['field_order'])&& count($transient_options['field_order']) > 0) {
            $fields = $transient_options['field_order'];
        } else {
            $fields = $this->getKeys();
        }
        foreach($fields as $field) {
            $swissField = $this->getChild($field);
            if (!$swissField instanceof I2CE_Swiss_CustomReports_ReportView_Field) {
                continue;
            }
            if (!$swissField->isEnabledInReport()) {
                continue;
            }
            $fieldNode = $this->template->appendFileByNode('customReports_reportView_fields_each.html','li',$appendNode);
            if (!$fieldNode instanceof DOMNode) {
                continue;
            }
            $swissField->addLink('field_content','field_fill',$fieldNode,$action,$transient_options);
        }
        return true;
    }     


    protected $ensured = false;
    protected function ensureFields() {
        if ($this->ensured) {
            return true;
        }
        if (!$this->getParent() instanceof I2CE_Swiss_CustomReports_ReportView_Meister) {
            I2CE::raiseError("bad Parent");
            return false;
        }
        if ( !($report = $this->getParent()->getReport())) {
            I2CE::raiseError("Bad report");
            return false;
        }

        $swissReport = $this->getSwissReport($report);
        if (!$swissReport instanceof I2CE_Swiss_CustomReports_Report) {
            I2CE::raiseError("no report");
            return false;
        }
        $swissReportingForms = $swissReport->getChild("reporting_forms",true);
        if (!$swissReportingForms instanceof I2CE_Swiss_CustomReports_Report_ReportingForms) {
            I2CE::raiseError("no reporting forms");
            return false;
        }
        foreach ($swissReportingForms as $form=>$swissReportingForm) {
            if (!$swissReportingForm->isInRelationship()) {
                continue;
            }
            $swissFields = $swissReportingForm->getChild('fields');
            if (!$swissFields instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Fields) { 
                continue;
            }
            foreach ($swissFields as $field=>$swissField) {
                if (!$swissField->isEnabled() ) {
                    continue;
                }
                $this->getChild($form . '+' . $field,true);
            }
        }
        $swissReportingFunctions = $swissReport->getChild("reporting_functions",true);
        if (!$swissReportingFunctions instanceof I2CE_Swiss_CustomReports_Report_ReportingFunctions) {
            I2CE::raiseError("no reporting functions");
            return false;
        }
        foreach ($swissReportingFunctions as $func=>$swissReportingFunction) {
            if (!$swissReportingFunction->isInRelationship()) {
                continue;
            }
            if (!$swissReportingFunction->isEnabled()) {
                continue;
            }
            $this->getChild('+' . $func,true);
        }
        $swissReportingInternals = $swissReport->getChild("reporting_internals",true);
        if (!$swissReportingInternals instanceof I2CE_Swiss_CustomReports_Report_ReportingInternals) {
            I2CE::raiseError("no reporting internals");
            return false;
        }
        foreach ($swissReportingInternals as $int=>$swissReportingInternal) {
            if (!$swissReportingInternal->isEnabled()) {
                continue;
            }
            $this->getChild($int,true);
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
