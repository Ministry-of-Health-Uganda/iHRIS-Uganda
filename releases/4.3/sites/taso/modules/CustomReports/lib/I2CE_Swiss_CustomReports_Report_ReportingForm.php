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
*  I2CE_SwissConfig_CustomReports_Report_ReportingForm
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingForm extends I2CE_Swiss_CustomReports_Report_Base{
    public function getChildType($type) {
        switch ($type) {
        case 'fields':
            return 'CustomReports_Report_ReportingForm_Fields';            
        default:
            return parent::getChildType($type);
        }
    }
    
    public function getDisplayName() {
        if (!$this->setupRelationshipFactory()) {
            return parent::getDisplayName();
        }
        $report = $this->getBaseReport();
        if (!$report instanceof I2CE_Swiss_CustomReports_Report) {
            return parent::getDisplayName();
        }
        $root = $this->relationshipFactory->getSwiss('/' . $report->getRelationship() );
        if (!$root instanceof I2CE_Swiss) {
            return parent::getDisplayName();
        }
        $forms =  $root->getExistingSwissForms();
        if (!array_key_exists($this->name,$forms) || !$forms[$this->name] instanceof I2CE_Swiss) {
            if ($this->name == 'primary_form') {
                return $root->getDisplayName();
            } else {
                return parent::getDisplayName();
            }
        }
        return $forms[$this->name]->getDisplayName();
    }
    
    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_report_form.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add reported form template");
            return false;
        }               
        if (!$this->displayMetaData($mainNode)) { 
            return false;
        }
        $fields=$this->getChild('fields',true);
        if (!$fields instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Fields) {            
            return false;
        }
        $fields->addAjaxLink('fields_link','fields_content','fields_ajax',$mainNode,$action, $transient_options);            
        //$fields->addLink('fields_content','fields_link',$mainNode,$action, $transient_options);            
        return true;
    }
    

    public function getSwissFields() {
        $fields=$this->getChild('fields',true);
        if (!$fields instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Fields) {            
            return array();
        } else {
            return $fields;
        }
    }

    public function isPrimary() {
        return $this->name === 'primary_form';
    }

    protected $formObj = false;


    public function getFormName() {
        if ($this->isPrimary()) {
            $formName = $this->getRelationship();
        } else {
            $formName = $this->name;
        }        
        return $formName;
    }
    public function getFormObj() {
        if ($this->formObj instanceof I2CE_Form) {
            return $this->formObj;
        }
        $formName = $this->getFormName();
        $swissRelForm = $this->getSwissRelationshipForm($formName);
        if (!$swissRelForm instanceof I2CE_Swiss_FormRelationship) {
            return false;
        }
        $form = $swissRelForm->getForm();
        if (!is_string($form) || strlen($form) == 0) {
            return false;
        }
        $formObj = I2CE_FormFactory::instance()->createForm($form);
        if (!$formObj instanceof I2CE_Form) {
            return false;
        }
        $this->formObj = $formObj;
        return $this->formObj;
    }

    public function isInRelationship() {
        return ($this->getFormObj() instanceof I2CE_Form);
    }

    



    public function isRequired() {
        if (!$this->hasField('drop_empty')) {
            return false;
        }
        if ($this->getField('drop_empty') == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function setRequired($req) {
        if ($req) {
            $this->setField('drop_empty',1);
        } else {
            $this->setField('drop_empty',0);
        }
    }


    protected function displayMetaData($contentNode) {
        //first check to see if is is the primary form:
        if ($this->name == 'primary_form') {
            $primary = true;
        } else {
            $primary = false;
        }
        $relFormSwiss = $this->getSwissRelationshipForm($this->name);
        if (!$relFormSwiss instanceof I2CE_Swiss_FormRelationship) {
            return false;
        }
        $this->template->setDisplayDataImmediate('reported_form_name',$this->name,$contentNode);
        $this->template->setDisplayDataImmediate('reported_form_display_name',$relFormSwiss->getDisplayName(),$contentNode);
        $this->template->setDisplayDataImmediate('reported_form_description',$relFormSwiss->getDescription(),$contentNode);
        
        if ($this->isPrimary()) {
            $this->template->setDisplayDataImmediate('form_is_primary','1',$contentNode);            
        } else {
            $this->template->setDisplayDataImmediate('form_is_primary','',$contentNode);            
            if ($this->isRequired()) {
                $this->template->setDisplayDataImmediate('required',1, $contentNode);
            }
            $this->renameInputs(array('submit','required'), $contentNode);
        }
        return true;
    }


    public function processValues($vals) {
        if (!array_key_exists('submit',$vals)) {
            return true;
        }
        $this->setRequired(array_key_exists('required',$vals) && $vals['required']);
        return true;
    }

  }
  
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
    
