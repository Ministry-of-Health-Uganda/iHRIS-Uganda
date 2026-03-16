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
* @subpackage formrelationship
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.6
* @since v4.0.6
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_FormRelationship_AncestralCondition
* 
* @access public
*/


class I2CE_Swiss_FormRelationship_AncestralCondition extends I2CE_Swiss_FormRelationship_Base{

    public function getDescription() {
        if ($this->getField('anc_form') && $this->getField('anc_field') && $this->getField('child_field')) {
            return $this->getField('anc_form') . '+' .  $this->getField('anc_field') .'=' .  $this->getField('child_field');
        } else {
            return  'Unspecified';
        }
    }
    
    public function processValues($vals) {
        $check = array('anc_form','child_field');
        foreach ($check as $key) {
            if (!array_key_exists($key,$vals) || !$vals[$key]) {
                return false;
            }
        }
        if (!array_key_exists('anc_field',$vals) || !is_array($vals['anc_field']) || !array_key_exists($vals['anc_form'],$vals['anc_field']) ||  !( $anc_field =  $vals['anc_field'][$vals['anc_form']])) {
            return false;
        }
        if (! ($parent = $this->getParent()) instanceof I2CE_Swiss_FormRelationship_AncestralConditions) {
            return false;
        }
        if (! ($pparent = $parent->getParent()) instanceof I2CE_Swiss_FormRelationship_Join) {
            return false;
        }
        $form = $pparent->getForm();
        $ff = I2CE_FormFactory::instance();
        if (! ($formObj = $ff->createContainer($form)) instanceof I2CE_Form) {
            return false;
        }
        if (! ($fieldObj = $formObj->getField($vals['child_field'])) instanceof I2CE_FormField) {
            return false;
        }
        
        $swissForm = $this->getSwissForm($vals['anc_form']);
        if (!$swissForm instanceof I2CE_Swiss_FormRelationship) {
            return false;
        }
        $ancForm = $swissForm->getForm();
        if (! ($ancFormObj = $ff->createContainer($ancForm)) instanceof I2CE_Form) {
            return false;
        }
        if (! ($ancFieldObj = $ancFormObj->getField($anc_field)) instanceof I2CE_FormField) {
            return false;
        }
        foreach ($check as $key) {
            $this->setField($key,$vals[$key]);
        }
        $this->setField('anc_field',$anc_field);
        return true;
    }



    public function displayValues($contentNode,$transient_options, $action) {
        if ($action == 'view') {
            return true;
        }
        $mainNode = $this->template->appendFileByNode('formRelationship_condition.html','div',$contentNode);
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Could not add condition template");
            return false;
        }

        if (! ($parent = $this->getParent()) instanceof I2CE_Swiss_FormRelationship_AncestralConditions) {
            return false;
        }
        if (! ($pparent = $parent->getParent()) instanceof I2CE_Swiss_FormRelationship_Join) {
            return false;
        }
        $form = $pparent->getForm();
        $ff = I2CE_FormFactory::instance();
        if (! ($formObj = $ff->createContainer($form)) instanceof I2CE_Form) {
            return false;
        }
        $fieldNames = $formObj->getFieldNames();
        $fieldNames[] = 'id';
        $fieldNames[] = 'parent';

        $fields = array();
        foreach ($fieldNames as $field) {
            $fields[$field] = $field;
        }
        $this->template->setDisplayDataImmediate('child_field',$fields,$contentNode);
        
        $ancForms = $pparent->getAncestorFormNames();
        if (count($ancForms) == 0) {
            return false;
        }
        if (! ($ancFormNode = $this->template->getElementById('anc_form',$mainNode)) instanceof DOMNode) {
            I2CE::raiseError("Could not get anc_from node");
            return false;
        }
        if (! ($fieldsNode = $this->template->getElementById('anc_fields',$mainNode)) instanceof DOMNode) {
            I2CE::raiseError("Could not get fields node");
            return false;
        }
        $forms = array();
        $menu_select = array();
        $inputs = array('anc_form','child_field');
        $anc_field_ids = array();
        $javascript = '';
        $curAncForm  = $this->getField('anc_form');
        foreach ($ancForms as $ancForm) {
            if (! ($ancFormObj = $ff->createContainer($ancForm)) instanceof I2CE_Form) {
                continue;
            }
            $forms[$ancForm] = $ancForm;
            $ancFieldId = "anc_field:$ancForm";
            $anc_field_ids[] = $this->path .':' . $ancFieldId;
            $inputs[] = $ancFieldId;
            if ($ancForm == $curAncForm) {
                $style = 'display:block'; 
            } else {
                $style = 'display:none'; 
            }
            $selNode = $this->template->createElement('select',array('name'=>$ancFieldId, 'id'=>$this->path .':' . $ancForm ,'style'=>$style));
            $javascript .= " document.getElementById( '" . $this->path .':' . $ancForm . "').style.display = 'none';" . "\n";
            $this->template->appendNode($selNode, $fieldsNode);
            $ancFieldNames = $ancFormObj->getFieldNames();
            $ancFieldNames[] = 'id';
            $ancFieldNames[] = 'parent';  
            $select_value = "Select Value";
            I2CE::getConfig()->setIfIsSet($select_value,"/modules/formRelationships/text/select_value");
          
            $fields = array(''=>$select_value);
            foreach ($ancFieldNames as $ancFieldName) {
                $fields[$ancFieldName]  = $ancFieldName;
            }            
            $this->template->setDisplayDataImmediate($ancFieldId,$fields, $mainNode);
        }
        $javascript .= " document.getElementById( '" . $this->path . ":' + this.value  ).style.display = 'block'; \n return true; ";
        

        $ancFormNode->setAttribute('onChange',$javascript);
        $inputs = $this->renameInputs($inputs,$mainNode);        
        $this->template->setDisplayDataImmediate($inputs['anc_form'],$forms,$mainNode);
        if ( ($childField = $this->getField('child_field')) &&  ($anc_field = $this->getField('anc_field')) && $curAncForm && array_key_exists("anc_field:$curAncForm",$inputs)) {
            $this->template->selectOptionsImmediate($inputs['child_field'],$childField,$mainNode);
            $this->template->selectOptionsImmediate($inputs['anc_form'],$curAncForm,$mainNode);
            $this->template->selectOptionsImmediate($inputs["anc_field:$curAncForm"],$anc_field,$mainNode);            
        } else {
            $this->template->selectOptionsImmediate($inputs['child_field'],'',$mainNode);
            $this->template->selectOptionsImmediate($inputs['anc_form'],'',$mainNode);
            foreach ($ancForms as $ancForm) {
                if ( array_key_exists("anc_field:$ancForm",$inputs)) {
                    $this->template->selectOptionsImmediate($inputs["anc_field:$ancForm"],'',$mainNode);                            
                }
            }
        }

        return true;
    }

    
    

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
