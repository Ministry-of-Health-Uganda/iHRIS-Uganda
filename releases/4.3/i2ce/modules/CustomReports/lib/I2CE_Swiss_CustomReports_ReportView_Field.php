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
*  I2CE_Swiss_CustomReports_ReportView_Field
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_ReportView_Field extends I2CE_Swiss_CustomReports_ReportView_Base{

    public function getHeader() {
        $swissReportView = $this->getBaseReportView();
        if (!$swissReportView instanceof I2CE_Swiss_CustomReports_ReportView) {
            return $this->humanText($this->name);
        }
        $swissReport = $swissReportView->getSwissReport($swissReportView->getReport());
        if (!$swissReport instanceof I2CE_Swiss_CustomReports_Report) {
            return $this->humanText($this->name);
        }
        list($reportform,$field) = array_pad(explode('+',$this->name),2,'');
        if ($reportform) {
            $swissField = $swissReport->getSwissField($reportform,$field);
            if ($swissField instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
                return $swissField->getHeader();
            }
        } else {
            //its a reporting function
            $swissFunc = $swissReport->getSwissFunction($field);
            if ($swissFunc instanceof I2CE_Swiss_CustomReports_Report_ReportingFunction) {                
                return $swissFunc->getHeader();
            }
        }
        return $this->humanText($this->name);
    }

    protected $form;
    protected function getForm() {
        if ($this->form === null) {
            $reportView = $this->getBaseReportView();
            if (!$reportView instanceof I2CE_Swiss_CustomReports_ReportView) {
                return null;
            }
            if ($reportView->getReport() === false) {
                return null;
            }
            $report = $this->getSwissReport($reportView->getReport());
            if (!$report instanceof I2CE_Swiss_CustomReports_Report) {
                return null;
            }
            $relationship = $report->getSwissRelationship();
            if (!$relationship instanceof I2CE_Swiss_FormRelationship) {
                return null;
            }
            list($reportform,$field) = explode('+',$this->name);
            if ($reportform == 'primary_form') {
                $this->form = $relationship->getForm();
            } else {
                $swissForm = $relationship->getSwissForm($reportform);
                if ($swissForm instanceof I2CE_Swiss_FormRelationship) {
                    $this->form = $swissForm->getForm();
                }
            }
        }
        return $this->form;
    }
    public function isNumeric() {
        list($reportform,$field) = array_pad(explode('+',$this->name),2,'');
        if (!$field) {
            return false;
        }
        if ($reportform) {
            $form = $this->getForm();
            if (!$form) {
                return false;
            }
            $factory = I2CE_FormFactory::instance();
            $formObj = $factory->createForm($form);
            if (!$formObj instanceof I2CE_Form) {
                return false;
            }
            return  $formObj->isNumeric($field);         
        } else {
            //it is a reporting function
            $reportView = $this->getBaseReportView();
            if (!$reportView instanceof I2CE_Swiss_CustomReports_ReportView) {
                return false;
            }
            if ($reportView->getReport() === false) {
                return false;
            }
            $report = $this->getSwissReport($reportView->getReport());
            if (!$report instanceof I2CE_Swiss_CustomReports_Report) {
                return false;
            }
            $relationship = $report->getSwissRelationship();
            if (!$relationship instanceof I2CE_Swiss_FormRelationship) {
                return false;
            }
            $funcs = $relationship->getChild('reporting_functions');
            if (!$funcs instanceof I2CE_Swiss_FormRelationship_ReportingFunctions) {
                return false;
            }
            $func = $funcs->getChild($field);
            if (!$func instanceof I2CE_Swiss_SQLFunction) {
                return false;
            }
            return $func->getFieldObj()->isNumeric();
        }
    }

    protected $reportField = false;


    public function getDisplayName() {
        if (! ($reportField = $this->getReportField()) instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
            return parent::getDisplayName();
        }
        return $reportField->getDisplayName();
    }


    public function getReportField() {
        if ($this->reportField) {
            return $this->reportField;
        }
        if (! ($parent = $this->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Fields) {
            I2CE::raiseError("bad Parent");
            return false;
        }
        if (! ($meister = $parent->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Meister) {
            I2CE::raiseError("bad Parent");
            return false;
        }
        if ( !($report = $meister->getReport())) {
            I2CE::raiseError("Bad report");
            return false;
        }
        $swissReport = $this->getSwissReport($report);
        if (!$swissReport instanceof I2CE_Swiss_CustomReports_Report) {
            I2CE::raiseError("no report");
            return false;
        }
        list($reportform,$field) = array_pad(explode('+',$this->name),2,'');
        $swissInternals = $swissReport->getChild('reporting_internals');
        if ( $swissInternals instanceof I2CE_Swiss_CustomReports_Report_ReportingInternals && ($swissInternal = $swissInternals->getChild($this->name)) !== null ) {
            if ( !$swissInternal instanceof I2CE_Swiss_CustomReports_Report_ReportingInternal ) {
                return false;
            }
            $this->reportField = $swissInternal;
        } elseif ( !$field ) {
            return false;
        } elseif (strlen($reportform)>0) {
            $swissForms = $swissReport->getChild('reporting_forms');
            if (!$swissForms instanceof I2CE_Swiss) {
                return false;
            }
            $swissForm  = $swissForms->getChild($reportform);
            if (!$swissForm instanceof I2CE_Swiss) {
                return false;
            }
            $swissFields = $swissForm->getChild('fields');
            if (!$swissFields instanceof I2CE_Swiss) {
                return false;
            }
            $field = $swissFields->getChild($field);
            if (!$field instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
                return false;
            }
            $this->reportField = $field;            
        } else {
            $swissFunctions = $swissReport->getChild('reporting_functions');
            if (!$swissFunctions instanceof I2CE_Swiss) {
                return false;
            }
            $field  = $swissFunctions->getChild($field);            
            if (!$field instanceof I2CE_Swiss_CustomReports_Report_ReportingFunction) {
                return false;
            }
            $this->reportField = $field;            
        }
        return $this->reportField;
    }
        

    public function isEnabledInReport() {
        $field = $this->getReportField();
        if (!$field) {
            return false;
        }        
        return ($field->isInRelationship() && $field->isEnabled());
    }

    public function processValues($values) {
        if (array_key_exists('enabled_hide',$values)) {
            if (array_key_exists('enabled',$values) && $values['enabled']) {
                $this->setEnabled(true);
            } else {
                $this->setEnabled(false);
            }
        }
        if (array_key_exists('aggregate',$values)) {
            $this->setAggregation($values['aggregate']);
        }
        if (array_key_exists('merge_additional_exists',$values)) {
            if (array_key_exists('merge_additional',$values)) {
                $add = $values['merge_additional'];
            } else {
                $add =false;
            }
            $this->setAdditional($add);        
        }
        return true;
    }

    public function setAdditional($add) {
        if ($add) {
            $this->setField('merge_additional',$add);
        } else {
            $this->setField('merge_additional','');
        }
    }

    public function getAdditional() {
        return $this->getField('merge_additional');
    }
    
    public function isEnabled() {
        return ($this->hasField('enabled') && $this->getField('enabled') == 1);
    }

    public function setEnabled($enab) {
        if ($enab) {
            $this->setField('enabled','1');
        } else {
            $this->setField('enabled','0');
        }
    }
    
    public function setAggregation($agg) {
        $this->setField('aggregate',$agg);
    }

    public function getAggregation() {
        if (!$this->hasField('aggregate')) {
            return 'none';
        }
        return $this->getField('aggregate');
    }

    protected function getOptionalMerges() {
        if (! ($parent = $this->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Fields) {
            return array();
        }
        if (! ($gparent = $parent->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Merge) {
            return array();
        }
        $mergeReportField = $gparent->getSwissReportFormField();
        if (!$mergeReportField instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
            return array();
        }
        $mergeReportFieldPath = $mergeReportField->getPath();
        $optional = array();
        $path = $this->getPath();
        if ( ! ($rField = $this->getReportField()) instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
            return array();
        }
        if (! ($fieldObj = $rField->getFieldObj()) instanceof I2CE_FormField) {
            return array();
        }
        if ($fieldObj instanceof I2CE_FormField_MAPPED) {
            $mapped = $fieldObj->getSelectableForms();
        } else if ($fieldObj->getName() == 'id') {
            if (!$fieldObj->getContainer() instanceof I2CE_Form) {
                return array();
            }
            $mapped = array($fieldObj->getContainer()->getName());
        } else {            
            return array();
        }
        $allFields = $gparent->getAllParentFields(false);        


        foreach ($allFields as $fieldPath=>$swissField) {            
            if (!$swissField instanceof I2CE_Swiss_CustomReports_ReportView_Field) {
                continue;
            }
            $reportField = $swissField->getReportField();
            if (!$reportField instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
                continue;
            }        
            if ($reportField->getPath() == $mergeReportFieldPath) {
                continue;
            }
            $fObj = $reportField->getFieldObj();
            if (!$fObj instanceof I2CE_FormField) {
                continue;
            }                  
            if (!$fObj->getContainer() instanceof I2CE_Form) {
                continue;
            }
            if ($fObj instanceof I2CE_FormField_MAPPED) {
                $c_mapped = $fObj->getSelectableForms();
            } else if ($fObj->getName() == 'id') {
                $c_mapped = array($fObj->getContainer()->getName());                
            } else {
                continue;
            } 
            if (count(array_intersect($c_mapped,$mapped)) == 0) {
                continue;
            }
            $optional[] = $swissField;
        }
        return $optional;
    }

    public function displayValues($contentNode, $transient_options, $action) {
        if ($this->isNumeric()) {
            $template ="customReports_reportView_field_numeric.html";
        } else {
            $template ="customReports_reportView_field.html";
        }
        $mainNode = $this->template->appendFileByNode($template,'span',$contentNode);
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not load report views template");
            return false;
        }   
        $this->template->setDisplayDataImmediate('name', $this->name, $mainNode);
        $this->template->setDisplayDataImmediate('displayName', $this->getHeader(), $mainNode);
        $this->template->setDisplayDataImmediate('enabled',$this->isEnabled(), $mainNode);
        $this->template->selectOptionsImmediate('aggregate',$this->getAggregation(),$mainNode);
        $this->renameInputs(array('merge_additional','merge_additional_exists','enabled','enabled_hide','aggregate'), $mainNode);
        $opt = $this->getOptionalMerges();
        if (count($opt) > 0) {
            $this->template->setDisplayDataImmediate('merge_additional_container',1,$mainNode);            
            if ( ($addNode = $this->template->getElementById('merge_additional',$mainNode)) instanceof DOMElement) {
                $msgs = array(
                    'reportView_merged_field_display'=>'%s from the report view %s',
                    );
                foreach ($msgs as $k=>&$v) {
                    I2CE::getConfig()->setIfIsSet($v,"/modules/CustomReports/text/user_messages/$k");
                }
                $fromName = '';
                if (( ($parent = $this->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Fields)
                    && (($gparent = $parent->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Merge)
                    && (($ggparent = $gparent->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Merges) 
                    && (($gggparent = $ggparent->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Field)
                    && (($ggggparent = $gggparent->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Fields) 
                    && (($gggggparent = $ggggparent->getParent()) instanceof I2CE_Swiss_CustomReports_ReportView_Meister)) {
                    $fromName = $gggggparent->getDisplayName();
                }


                $add = $this->getAdditional();
                foreach ($opt as $swissField) {
                    $path = $swissField->getPath();
                    $vals = array('value'=>$path);
                    if ($add == $path) {
                        $vals['selected'] ='selected';
                    }
                    $name = $swissField->getDisplayName();
                    if ($fromName) {
                        $disp = sprintf($msgs['reportView_merged_field_display'],$name,$fromName);        
                    } else {
                        $displ = $name;
                    }
                    $addNode->appendChild($this->template->createElement('option',$vals,$disp));
                }
            }
        } else {
            $this->template->setDisplayDataImmediate('merge_additional_container',0,$mainNode);            
        }

        $swissMerges = $this->getChild('merges',true);
        if (($swissMerges instanceof I2CE_Swiss_CustomReports_ReportView_Merges) && ($swissMerges->hasLinkableReports())) {
            $swissMerges->addAjaxLink('merges_link','merges_content','merges_ajax',$mainNode,$action, $transient_options);                        
            $this->template->setDisplayDataImmediate('merge_link_container',1,$mainNode);
        } else {
            $this->template->setDisplayDataImmediate('merge_link_container',0,$mainNode);
        }

        return true;
    }



    

    public function getChildType($child) {
        switch($child) {
        case 'merges':
            return 'CustomReports_ReportView_Merges';
        default:
            return parent::getChildType($child);
        }
    }

    
    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
