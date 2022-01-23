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
*  I2CE_SwissConfig_CustomReports_Report_ReportingForm_Field
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingForm_Field extends I2CE_Swiss_CustomReports_Report_Base{

    public function getChildType($child) {
        switch($child) {
        case 'merges':
            return 'CustomReports_Report_ReportingForm_Field_Merges';
        case 'limits':
            return 'CustomReports_Report_ReportingForm_Field_Limits';
        case 'module_limits':
            return 'CustomReports_Report_ReportingForm_Field_ModuleLimits';
        default:
            return parent::getChildType($child);
        }
    }

    public function setEnabled($enabled) {
        if ($enabled) {
            $this->setField('enabled',1);
        } else {
            $this->setField('enabled',0);
        }
    }

    public function isEnabled() {
        return ($this->hasField('enabled') && $this->getField('enabled') == 1);
    }

    public function setFormDisplay($form_display) {
        if ($form_display) {
            $this->setField('form_display',1);
        } else {
            $this->setField('form_display',0);
        }
    }

    public function isFormDisplay() {
        return ($this->hasField('form_display') && $this->getField('form_display') == 1);
    }

    public function setFormDisplayFields($fields) {
        $this->setField('form_display_fields',$fields);
    }

    public function hasFormDisplayFields() {
        return $this->hasField('form_display_fields');
    }
    
    public function getFormDisplayFields() {
        if ($this->hasField('form_display_fields')) {
            return $this->getField('form_display_fields');
        } else {
            return '';
        }
    }


    
    public function setHeader($header) {
//        if (!$this->hasHeader() && $header == $this->getDefaultHeader()) {
        /*
        if (!$this->hasHeader()) {
            return;
        }
        */
        $this->setTranslatableField('header',$header);
    }

    public function hasHeader() {
        return $this->hasField('header');
    }
    
    public function getHeader() {
        if ($this->hasField('header')) {
            return $this->getField('header');
        } else {
            return $this->getDefaultHeader();
        }
    }

    public function getDisplayName() {
        return $this->getHeader();
    }


    public function getLink() {
        if ($this->hasField('link')) {
            return $this->getField('link');
        } else {
            return false;
        }
     
    }

    public function getLinkAppend() {
        if ($this->hasField('linkAppend')) {
            return $this->getField('linkAppend');
        } else {
            return false;
        }
     
    }

    protected $fieldObj = false;
    protected $formObj = false;

    public function getFormObj() {
        if ($this->formObj instanceof I2CE_Form) {
            return $this->formObj;
        }
        $swissForm = $this->getSwissForm();
        if (!$swissForm) {
            return false;
        }
        $formObj = $swissForm->getFormObj();
        if (!$formObj instanceof I2CE_Form) {
            return false;
        }
        $this->formObj = $formObj;
        return $this->formObj;
    }

    public function getFieldObj() {
        if ($this->fieldObj instanceof I2CE_FormField) {
            return $this->fieldObj;
        }
        $formObj = $this->getFormObj();
        if (!$formObj instanceof I2CE_Form) {
            return false;
        }        
        if ($this->name == 'id') {
            $fieldObj = new I2CE_FormField_STRING_LINE('id');
            $fieldObj->setContainer($formObj);
        } else {
            $fieldObj = $formObj->getField($this->name);
            if (!$fieldObj instanceof I2CE_FormField) {
                return false;
            }
        }
        $this->fieldObj = $fieldObj;
        return $this->fieldObj;
    }

    public function isInRelationship() {
        return ($this->getFieldObj() instanceof I2CE_FormField);
    }



    public function getDefaultHeader() {
        $fieldObj = $this->getFieldObj();
        if (!$fieldObj instanceof I2CE_FormField) {
            return $this->getName();
        }
        $header = '';
        $formObj = $this->getFormObj();
        if ($formObj instanceof I2CE_Form) {
            $header = $formObj->getDisplayName() . ' ';
        }
        if ($fieldObj->hasHeader()) {
            $header .= $fieldObj->getHeader();
        } else {
            $header .= $this->humanText($fieldObj->getName());
        }
        return $header;
    }

    
    protected $limit_defaults = null;

    public function getLimitDefaults() {
        if (is_array($this->limit_defaults)) {
            return $this->limit_defaults;
        }
        $res = array('default');
        $fieldObj = $this->getFieldObj();
        if ($fieldObj instanceof I2CE_FormField) {
            if ($fieldObj->hasOption('meta')) {
                $meta = $fieldObj->getOption('meta');
                if (array_key_exists('limits',$meta) && is_array($meta['limits'])) {
                    $res = array_unique(array_merge($res,array_keys($meta['limits'])));
                }
            }
        } 
        $this->limit_defaults = $res;
        return $this->limit_defaults;
    }



    public function getLimitDefault() {
        $defaults = $this->getLimitDefaults();
        $default = $this->getField('limit_default');
        if (!in_array($default,$defaults)) {
            return 'default';
        } 
        return $default;
    }

    public function setLimitDefault($default) {
        $defaults = $this->getLimitDefaults();
        if (!in_array($default,$defaults)) {
            return ;
        }
        $this->setField('limit_default',$default);
    }





    protected function displayLimitDefaults($contentNode,$action) {
        $defaults = $this->getLimitDefaults();
        if (!is_array($defaults) || count($defaults) <= 1) {
            $this->template->setDisplayDataImmediate('show_default',0, $contentNode);
            return;
        }
        $selected_default = $this->getLimitDefault();
        if ($action == 'view') {
            $this->template->setDisplayDataImmediate('limit_default',$selected_syle,$contentNode);
            return;
        }
        $this->template->setDisplayDataImmediate('show_default',1, $contentNode);
        $defaults_node = $this->template->getElementById('default_list',$contentNode);
        if (!$defaults_node instanceof DOMNode) {
            return;
        }
        foreach ($defaults as $default) {
            $attrs = array ('value'=>$default);
            if ($default == $selected_default) {
                $attrs['selected'] = 1;
            }
            $defaults_node->appendChild( $this->template->createElement('option',$attrs, $default));
        }
    }




    public function processValues($vals) {
        if (!array_key_exists('field_submit',$vals)) {
            return true;
        }
        $field = $this->storage->getName();
        $formName = $this->getForm();
        if ($formName === false) {
            return false;
        }
        $factory = I2CE_FormFactory::instance();        
        
        $fields = $factory->getFieldNames($formName,array('in_db'=>true));        
        $fields[] = 'last_modified';
        $fields[] = 'created';
        if ($field != 'id' && !in_array($field,$fields)) {
            I2CE::raiseError("Bad field name $field for " . $this->storage->getPath(false));
            return false;
        }
        $this->setEnabled(array_key_exists('enabled',$vals) && $vals['enabled']);
        if (array_key_exists('header',$vals)) {
            $this->setHeader($vals['header']);
        }
        $this->setFormDisplay(array_key_exists('form_display',$vals) && $vals['form_display']);
        if (array_key_exists('form_display_fields',$vals)) {
            $this->setFormDisplayFields($vals['form_display_fields']);
        }
        $keys = array('link_url'=>'link','link_target'=>'target','link_append'=>'link_append','link_type'=>'link_type');
        foreach ($keys as $key=>$internal_key) {
            if (!array_key_exists($key,$vals)) {
                continue;
            }
            $this->setField($internal_key,$vals[$key]);
        }
        if (array_key_exists('limit_default',$vals)) {
            $this->setLimitDefault($vals['limit_default']);
        }
        return true;
    }



    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_report_form_field.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add reported form template");
            return false;
        }        
        $this->template->setDisplayDataImmediate('header', $this->getHeader(),$mainNode);
        $this->template->setDisplayDataImmediate('field_name', $this->getName(),$mainNode);
        if ($this->isEnabled()) {
            $this->template->setDisplayDataImmediate('enabled', 1,$mainNode);
        } else {
            $this->template->setDisplayDataImmediate('enabled', null,$mainNode);
        }
        if ( $this->getName() == 'id' && $this->getFormObj() instanceof I2CE_List ) {
            $this->template->setDisplayDataImmediate('is_id_field', true, $mainNode );
            if ( $this->isFormDisplay() ) {
                $this->template->setDisplayDataImmediate('form_display', 1,$mainNode);
            } else {
                $this->template->setDisplayDataImmediate('form_display', null,$mainNode);
            }
            $this->template->setDisplayDataImmediate('form_display_fields', $this->getFormDisplayFields(),$mainNode);
        } else {
            $this->template->setDisplayDataImmediate('is_id_field', false, $mainNode );
        }
        if ($action == 'edit') {
            $headerNode = $this->template->getElementById('header');
            if ($headerNode instanceof DOMElement && $this->parent instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Fields) {
                $existingHeaders = $this->parent->getExistingHeaders();
                if (count($existingHeaders) > 0) {
                    $this->template->setClassValue($headerNode,'validate_data',array('notinlist'=>$existingHeaders), '%');
                }
            }
        }
        $linkNode = $this->template->getElementById("link_options_menu",$mainNode);        
        if ($linkNode instanceof DOMNode) {
            if (!$this->displayLinkValues($mainNode,$transient_options,$action)) {
                return false;
            }
        }
        $swissLimits = $this->getChild('limits',true);
        if ($swissLimits instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Limits) {
            $swissLimits->addAjaxLink('limits_link','limits_contents','limits_ajax',$mainNode,$action, $transient_options);            
        }
        $swissMerges = $this->getChild('merges',true);
        if ($swissMerges instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_Merges) {
            $swissMerges->addAjaxLink('merges_link','merges_content','merges_ajax',$mainNode,$action, $transient_options);            
        }
        $swissModuleLimits = $this->getChild( 'module_limits', true );
        if ( $swissModuleLimits instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field_ModuleLimits && $swissModuleLimits->hasLimits() ) {
            $swissModuleLimits->addAjaxLink('module_limits_link', 'module_limits_contents',
                    'module_limits_ajax', $mainNode, $action, $transient_options );
            $this->template->setDisplayDataImmediate('has_module_limits',1,$mainNode);
        } else {
            $this->template->setDisplayDataImmediate('has_module_limits',0,$mainNode);
        }

        $this->displayLimitDefaults($mainNode,$action);
        $inputs = array('field_submit','enabled','header','link_url','link_target','link_append','link_type','limit_default','form_display','form_display_fields');
        if ($action == 'edit') {
            $this->renameInputs($inputs,$mainNode);        
        }
        return true;
    }

    protected $swissForm;

    public function getSwissForm() {
        if (!$this->swissForm instanceof I2CE_Swiss_CustomReports_Report_ReportingForm) {
            $this->swissForm = $this->getAncestorByClass('I2CE_Swiss_CustomReports_Report_ReportingForm');        
            if (!$this->swissForm instanceof I2CE_Swiss_CustomReports_Report_ReportingForm) {
                I2CE::raiseError("Could not get reported form");
            }
        }
        return  $this->swissForm;
    }

    public function getForm() {
        $swissForm = $this->getSwissForm();
        if (!$swissForm instanceof I2CE_Swiss_CustomReports_Report_ReportingForm) {
            return false;
        }
        $formRel = $this->getSwissRelationshipForm( $swissForm->storage->getName() );
        if (!$formRel instanceof  I2CE_Swiss) {
            I2CE::raiseError("Could not get relationship form for " . $swissForm->storage->getName());
            return false;
        }
        $formName = $formRel->getField("form");
        if (!$formName) {
            I2CE::raiseError("Could not get relationship form name for " . $swissForm->storage->getName());
            return false;
        }
        return $formName;
    }

    protected function displayLinkValues($linkNode, $transient_options, $action) {
        if ($this->hasField('link')) {
            $this->template->setDisplayDataImmediate('link_url', $this->getField('link'),$linkNode);
        }
        $formName = $this->getForm();
        if ($formName === false) {
            return false;
        }        
        if ($action == 'edit') {
            $reportFields= $this->getReportFields(false);
            $baseReport = $this->getBaseReport();
            if ($baseReport instanceof I2CE_Swiss_CustomReports_Report) {
                $swissForms = $baseReport->getSwissForms();
                if ($swissForms instanceof I2CE_Swiss_CustomReports_Report_ReportingForms) {
                    $forms = $swissForms->getKeys();
                    foreach ($forms as $formName) {
                        $reportFields["$formName+id"] = "$formName Id";
                    }
                }
            }
            ksort($reportFields);
            $this->template->setDisplayDataImmediate('link_target',$reportFields,$linkNode);
            if ($this->hasField('link_target')) {
                $this->template->selectOptionsImmediate('link_target',$this->getField('link_target'), $linkNode);
            }
            $this->template->setDisplayDataImmediate('link_append',$reportFields,$linkNode);
            if ($this->hasField('link_append')) {
                $this->template->selectOptionsImmediate('link_append',$this->getField('link_append'), $linkNode);
            }
            $typeFields = array();
            if ( $this->getFieldObj() instanceof I2CE_FormField_IMAGE ) {
                $typeFields['image'] = 'Image (img)';
            }
            $this->template->setDisplayDataImmediate('link_type',$typeFields,$linkNode);
            if ($this->hasField('link_type')) {
                $this->template->selectOptionsImmediate('link_type',$this->getField('link_type'), $linkNode);
            }
            if (!$this->addOptionMenu('link', $linkNode)) {
                return false;
            }            
        }        
        return true;
    }



    
    protected $reports=null;
    public function getLinkableReports() {
        if (!is_array($this->reports)) {
            $this->reports = $this->_getLinkableReports();
        }
        return $this->reports;
    }

    protected function _getLinkableReports() {
        //I2CE::raiseError("This is expensive at " . $this->getPath()) ;
        $reports = array();
        $field  = $this->name;
        $form = $this->getForm();
        if (!$form || !$field) {
            return $reports;
        }
        if  ( ! ($swissrep = $this->getBaseReport()) instanceof I2CE_Swiss_CustomReports_Report) {
            return $reports;
        }
        $baseReport =  $swissrep->getName();
        if (! ($relationship = $swissrep->getSwissRelationship()) instanceof I2CE_Swiss_FormRelationship) {
            return $reports;
        }
        if ( ! ($relForm = $relationship->getSwissForm($form)) instanceof I2CE_Swiss_FormRelationship) {
            return $reports;
        }
        if (!($joins = $relForm->getChild('joins',true))  instanceof I2CE_Swiss_FormRelationship_Joins) {
            return $reports;
        }
        $t_relatedForms =$joins->getRelatedForms();
        $relatedForms = array();
        foreach ($t_relatedForms as $join_style=>$join_data) {
            switch ($join_style) {
            case 'parent':
                //contains all of the forms which this form is a child of, e.g. the parent form
                //so in this case the $parentform+id would link to the this form if this field was parent.
                if ($field != 'parent' || !is_array($jon_data)) {
                    break;
                }
                foreach ($join_data as $parent_form=>$bool) {
                    if (!array_key_exists($parent_form,$relatedForms)) {
                        $relatedForms[$parent_form] = array();
                    }
                    if (!array_key_exists('id',$relatedForms[$parent_form])) {
                        $relatedForms[$parent_form]['id'] = array();
                    }
                    $relatedForms[$parent_form]['id'][] = 'parent';                    
                }
                break;
            case 'child':
                //contains all of the forms which are a child form of the current form.  
                //so in this case the $childform+parent would link to the this form if this field was id.
                if ($field != 'id' || !is_array($join_data)) {
                    break;
                }
                foreach ($join_data as $child_form=>$bool) {
                    if (!array_key_exists($child_form,$relatedForms)) {
                        $relatedForms[$child_form] = array();
                    }
                    if (!array_key_exists('parent',$relatedForms[$child_form])) {
                        $relatedForms[$child_form]['parent'] = array();
                    }

                    $relatedForms[$child_form]['parent'][] = 'child';
                }
                break;
            case 'parent_field':
                //join_data are the fields of this $form which map to another form.                
                //
                if (!is_array($join_data) || !array_key_exists($field,$join_data) || !is_array($join_data[$field])) {
                    break;
                }
                foreach ($join_data[$field] as $f) {
                    if (!array_key_exists($f,$relatedForms)) {
                        $relatedForms[$f] = array();
                    }
                    if (!array_key_exists('id',$relatedForms[$f])) {
                        $relatedForms[$f]['id'] = array();
                    }
                    $relatedForms[$f]['id'][] = 'parent_field';
                }
                break;
            case 'child_field':
                //contains array $fields of each of the fields of form $f which link to $form             
                //so if we are linked
                if ($field != 'id' || !is_array($join_data)) {
                    break;
                }
                foreach ($join_data as $f => $fields) {                    
                    foreach ($fields as $fld) {
                        if (!array_key_exists($f,$relatedForms)) {
                            $relatedForms[$f] = array();
                        }
                        if (!array_key_exists($fld,$relatedForms[$f])) {
                            $relatedForms[$f][$fld] = array();
                        }
                        $relatedForms[$f][$fld][] = 'child_field';                    
                    }
                }
                break;
            }
        }
        if (count($relatedForms) == 0) {
            return $reports;
        }
        if ( ! ($swissReports = $this->getSwissReports()) instanceof  I2CE_Swiss_CustomReports_Reports) {
            return $reports;                
        }
        foreach ($swissReports as $report=>$swissReport) {
            if ($report == $baseReport) {
                //lets not allow merging onto self.
                continue;
            }
            if (  ! ($swissReportForms = $swissReport->getSwissForms()) instanceof I2CE_Swiss_CustomReports_Report_ReportingForms) {
                //really we should also be checking for functions here.
                continue;
            }
            foreach ($swissReportForms as $reportForm=>$swissReportForm) {
                if (!($swissReportForm instanceof I2CE_Swiss_CustomReports_Report_ReportingForm)) {
                    continue;
                }
                $formName = $swissReportForm->getFormName();                
                if (!array_key_exists($formName,$relatedForms)) {
                    continue;
                }
                if (  ! ($swissReportFields = $swissReportForm->getSwissFields()) instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Fields) {
                    continue;
                }
                foreach ($swissReportFields as $field=>$swissReportField) {
                    if (!$swissReportField instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
                        continue;
                    }
                    if (!array_key_exists($field,$relatedForms[$formName])) {
                        continue;
                    }
                    $header = $swissReportField->getHeader();
                    $reportDisplay = $swissReport->getDisplayName();                       
                    $formDisp = $swissReportForm->getDisplayName();
                    foreach ($relatedForms[$formName][$field] as $join_style) {                        
                        $reports[] = array('report'=>$report, 'reportDisplay'=>$reportDisplay,'reportForm'=>$reportForm,'formDisplay'=>$formDisp,'formName'=>$formName,'field'=>$field,'join_style'=>$join_style,'header'=>$header);
                    }
                }
            }
        }
        return $reports;     
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
