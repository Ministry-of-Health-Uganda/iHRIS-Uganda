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
* @subpackage customreports
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.9
* @since v4.0.9
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_CustomReports_ReportView_Merge
* 
* @access public
*/


class I2CE_Swiss_CustomReports_ReportView_Merge extends I2CE_Swiss_CustomReports_ReportView_Meister {




    
    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_reportView_merge_' . $action .'.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not load report views template");
            return false;
        }        

        if ($this->isEnabled()) {
            $this->template->setDisplayDataImmediate('enabled', 1,$mainNode);
        } else {
            $this->template->setDisplayDataImmediate('enabled', 0,$mainNode);
        }
        if ($this->showBlanks()) {
            $this->template->setDisplayDataImmediate('show_blanks', 1,$mainNode);
        } else {
            $this->template->setDisplayDataImmediate('show_blanks', 0,$mainNode);
        }

        $this->template->setDisplayDataImmediate('merge_report_name',$this->getDisplayName(),$mainNode);
        $header_node = $this->template->getElementById('merge_header',$mainNode);
        if ( $header_node instanceof DOMNode) {
            $this->template->setDisplayData('linked_report',$this->getLinkedReport(),$header_node);
        }
        $inputs = array('enabled','show_blanks','merge_exists');
        if ($action == 'edit') {
            $this->renameInputs($inputs,$mainNode);        
        }

        return $this->displayFields($mainNode,$transient_options,$action);
    }



    public function processValues($vals) {
        $this->setEnabled(array_key_exists('enabled',$vals) && $vals['enabled']);
        $this->setShowBlanks(array_key_exists('show_blanks',$vals) && $vals['show_blanks']);
        return parent::processValues($vals);
    }


    public function setEnabled($enabled) {
        if ($enabled) {
            $this->setField('enabled',1);
        } else {
            $this->setField('enabled',0);
        }
    }

    public function setShowBlanks($enabled) {
        if ($enabled) {
            $this->setField('show_blanks',1);
        } else {
            $this->setField('show_blanks',0);
        }
    }

    public function isEnabled() {
        if  ( ($this->hasField('enabled') && $this->getField('enabled') == 1)) {
            return $this->isValid();
        } else {
            return false;
        }
    }

    public function showBlanks() {
        //defaults to true
        if  ( ($this->hasField('show_blanks') && $this->getField('show_blanks') == 0)) {
            return false;
        } else {
            return true;
        }
    }



    public function isValid() {
        if (! ($reportMerge = $this->getSwissReportMerge()) instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merge) {
            return false;
        }
        return $reportMerge->isEnabled();//this checks to see if it valid(e.g. linkable)
    }


    public function getSwissReportFormField() {
        if (!$this->getParent() instanceof I2CE_Swiss_CustomReports_ReportView_Merges) {
            return false;
        }
        return $this->getParent()->getSwissReportFormField();
    }

    
    public function getSwissReportMerge() {
        if  ( !($reportFormField = $this->getSwissReportFormField()) instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
            return false;
        }
        if (! ($swissReportMerges =  $reportFormField->getChild('merges')) instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merges) {
            return false;
        }
        if ( !($child = $swissReportMerges->getChild($this->name))  instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merge) {
            return false;
        }
        return $child;
    }

    public function getDisplayName() {
        if ( ! ($swissReportMerge = $this->getSwissReportMerge()) instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merge) {
            return parent::getDisplayName();
        } else {
            return $swissReportMerge->getDisplayName();
        }
    }

    public function getLinkedReport() {
        if ( ! ($swissReportMerge = $this->getSwissReportMerge()) instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merge) {
            return '';
        }
        if (! ($swissReportMerge->getBaseReport() instanceof I2CE_Swiss_CustomReports_Report)) {
            return '';
        }
        return $swissReportMerge->getBaseReport()->getDisplayName();
    }


    public function getReport() {
        if ( ! ($swissReportMerge = $this->getSwissReportMerge()) instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merge) {
            return false;
        } else {
            return $swissReportMerge->getField('report');
        }
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
