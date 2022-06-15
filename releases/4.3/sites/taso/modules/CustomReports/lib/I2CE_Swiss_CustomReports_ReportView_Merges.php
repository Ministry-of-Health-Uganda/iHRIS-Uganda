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
* @subpackage custom reports
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.9
* @since v4.0.9
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_CustomRerorts_ReportView_Merges
* 
* @access public
*/


class I2CE_Swiss_CustomReports_ReportView_Merges extends I2CE_Swiss_CustomReports_ReportView_Base {
    
    public function getChildType($child) {
        return 'CustomReports_ReportView_Merge';
    }

    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_reportView_merges.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add merged reports");
            return false;
        }        
        $appendNode = $this->template->getElementById('merges_list',$mainNode);
        if (!$appendNode instanceof DOMNode) {
            I2CE::raiseError("Don't know where at add merged reports");
            return false;
        }
        $this->ensureLinkableReports();
        foreach ($this as $merge=>$swissMerge) {
            if (!$swissMerge instanceof I2CE_Swiss_CustomReports_ReportView_Merge ||   !$swissMerge->isValid()) {
                continue;
            }
            $mergeNode = $this->template->appendFileByNode('customReports_reportView_merges_each.html','li',$appendNode);            
            if (!$mergeNode instanceof DOMElement) {
                continue;
            }
            $this->template->setDisplayDataImmediate('merge_report_name',$swissMerge->getDisplayName(),$mergeNode);
            $swissMerge->addAjaxLink('merge_report_link','merge_report_contents','merge_report_ajax',$mergeNode,$action, $transient_options);            
        }
        return true;
    }



    public function hasLinkableReports() {
        $this->ensureLinkableReports();
        foreach ($this as $merge=>$swissMerge) {
            if (!$swissMerge instanceof I2CE_Swiss_CustomReports_ReportView_Merge ||   !$swissMerge->isValid()) {
                continue;
            }
            return true;
        }
        return false;
    }

    public function rewind() {
        $this->ensureLinkableReports();
        parent::rewind();
    }
    

    protected $ensured = false;
    protected function ensureLinkableReports() {
        //this will get the report that we are 
        if ($this->ensured) {
            return;
        }
        if ( ! ($reportFormField = $this->getSwissReportFormField()) instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
            return;
        }
        if (! ($swissReportMerges =  $reportFormField->getChild('merges')) instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merges) {
            //no merges have been set on  this field in the report
            $this->ensured = true;
            return ;
        }
        $childNames = $swissReportMerges->getChildNames();
        foreach ($childNames as $merge_key) {
            $swissReportMerge= $swissReportMerges->getChild($merge_key);
            //make sure all enabled merges from the report are present.
            if (!$swissReportMerge instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merge) {
                continue;
            }
            if (!$swissReportMerge->isEnabled()) {
                continue;
            }
            $child = $this->getChild($merge_key,true);
            if (!$child instanceof I2CE_Swiss_CustomReports_ReportView_Merge) {
                I2CE::raiseError("Bad creation");
                continue;
            }
            //$child->setEnabled(false);

        }
        $this->ensured = true;
        return;
    }


    public function getSwissReportFormField() {
        if (!$this->getParent() instanceof I2CE_Swiss_CustomReports_ReportView_Field) {
            return false;
        }
        return $this->getParent()->getReportField();
    }
    

    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
