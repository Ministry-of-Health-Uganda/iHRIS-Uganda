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
*  I2CE_SwissConfig_CustomReports_Report_ReportingForm_Fields
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_CustomReports_Report_ReportingForm_Fields extends I2CE_Swiss_CustomReports_Report_Base {

    public function getChildType($child) {
        return 'CustomReports_Report_ReportingForm_Field';            
    }


    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('customReports_report_form_fields.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add reported form template");
            return false;
        }        
        $appendNode = $this->template->getElementById('report_form_field_list',$mainNode);
        if (!$appendNode instanceof DOMNode) {
            I2CE::raiseError("Don't know where at add fields for the reported form at " . $this->configPath);
            return false;
        }
        $this->ensureFieldsFromForm();
        foreach ($this->getChildNames() as $field) {
            $swissField = $this->getChild( $field );
            if (!$swissField instanceof I2CE_Swiss || !$swissField->isInRelationship()) {
                continue;
            }
            $fieldNode = $this->template->appendFileByNode('customReports_report_form_fields_each.html','div',$appendNode);            
            if (!$fieldNode instanceof DOMElement) {
                continue;
            }
            $this->template->setDisplayDataImmediate('name',$field,$fieldNode);
            //$swissField->addAjaxLink('field_link','field_contents','field_ajax',$fieldNode,$action, $transient_options);            
            $swissField->addLink('field_contents','field_link',$fieldNode,$action, $transient_options);            
        }
        return true;
    }

    
    public function rewind() {
        $this->ensureFieldsFromForm();
        parent::rewind();
    }


    protected $ensured = false;
    protected function ensureFieldsFromForm() {
        if ($this->ensured) {
            return;
        }
        $swissForm = $this->getAncestorByClass('I2CE_Swiss_CustomReports_Report_ReportingForm');        
        if (!$swissForm instanceof I2CE_Swiss_CustomReports_Report_ReportingForm) {
            I2CE::raiseError("Could not get reported form");
            return;
        }
        $formRel = $this->getSwissRelationshipForm( $swissForm->storage->getName() );
        if (!$formRel instanceof  I2CE_Swiss) {
            I2CE::raiseError("Could not get relationship form for " . $swissForm->storage->getName());
            return;
        }
        $formName = $formRel->getField("form");
        if (!$formName) {
            I2CE::raiseError("Could not get relationship form name for " . $swissForm->storage->getName());
            return;
        }
        $factory = I2CE_FormFactory::instance();        
        $fields = $factory->getFieldNames($formName,array('in_db'=>true));        
        $fields[]= 'last_modified';
        $fields[]= 'created';
        foreach ($fields as $field) {
            $this->getChild($field,true);
        }
        $this->getChild('id',true);
        $this->ensured =true;
    }


    protected $headers;
    public function getExistingHeaders($get_defaults = false) {
        if (!is_array($this->headers)) {
            $this->headers = array();
            $fields = $this->storage->getKeys();
            foreach ($fields as $field) {
                $swissField = $this->getChild($field);
                if (!$field instanceof I2CE_Swiss_CustomReports_Report_ReportingForm_Field) {
                    continue;
                }                               
                if (!$swissField->hasHeader() && !$get_defaults) {
                    continue;
                }
                $this->headers[$field] = $swissField->getHeader();
            }

        }
        return $this->headers;
    }


    



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
