<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
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
* @subpackage page
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_PageXMLRelationship_Forms
* 
* @access public
*/


class I2CE_Swiss_PageXMLRelationship_Form extends I2CE_Swiss{



    public function getRelationship() { 
	if (($parentSwiss = $this->getParent()) instanceof I2CE_Swiss_PageXMLRelationship_FormFields) {
	    return $parentSwiss->getRelationship();
	}
	return false;
    }

    protected function getFieldNames() {
	if ( ($relObj = $this->getRelationship()) instanceof I2CE_FormRelationship
	     && ($form = $relObj->getForm($this->name))
	     && ($formObj = $relObj->getContainer($form)) instanceof I2CE_Form
	    ) {
	    $fieldnames = array();
	    foreach (  $formObj->getFieldNames() as $fieldname) {
		if (! ($fieldObj = $formObj->getField($fieldname)) instanceof I2CE_FormField
		    || !$fieldObj->isInDB()) {
		    continue;
		}
		$fieldnames[] = $fieldname;
	    }
	    return $fieldnames;
	} else {
	    return array();
	}

    }


    public function displayValues($contentNode, $transient_options, $action) {
        $mainNode = $this->template->appendFileByNode('swiss_xmlrelationship_form.html','div',$contentNode);                
        if (!$mainNode instanceof DOMNode) {
            I2CE::raiseError("Unable to add reported form template");
            return false;
        }       
	if ( ($relObj = $this->getRelationship()) instanceof I2CE_FormRelationship
	     && ($md = $relObj->getFormConfig($this->name)) instanceof I2CE_MagicDataNode 
	    ) {        
	    if ($this->name == 'primary_form')  {
		$name = $relObj->getForm('primary_form') . ' (primary_form)';
	    } else {
		$name = $this->name;
	    }
	    $disp_name = '';
	    $desc = '';
	    $md->setIfIsSet($disp_name,"display_name");
	    $md->setIfIsSet($desc,"description");
	    $this->template->setDisplayDataImmediate('form_name',$name,$contentNode);
	    $this->template->setDisplayDataImmediate('form_display_name',$disp_name,$contentNode);
	    $this->template->setDisplayDataImmediate('form_description',$desc,$contentNode);
	}
	$listNode = $this->template->getElementById('fields_list', $mainNode);
        if (!$listNode instanceof DOMElement) {
            I2CE::raiseError("Don't know where to add fields");
            return false;
        }
	$enabled = $this->storage->getAsArray();	
	I2CE::raiseError("Enalbed are " . print_r($enabled,true));
	foreach ($this->getFieldNames() as $fieldname) {
	    if ( !($fieldNode = $this->template->appendFileByNode('swiss_xmlrelationship_field.html','div',$listNode)) instanceof DOMNode
		 || ! ($enabledNode = $this->template->getElementByName('enabled',0,$fieldNode)) instanceof DOMNode
		) {
                continue;
            }
            $this->template->setDisplayDataImmediate('name',$fieldname,$fieldNode);
	    $input = 'enabled[' . $fieldname .']';
	    $enabledNode->setAttribute('name',$input);
	    if (in_array($fieldname,$enabled)) {
		$enabledNode->setAttribute('checked','checked');
	    }
	    $this->renameInputs($input,$fieldNode);
	}
        return true;
    }
    

    public function processValues($vals) {
        if (!parent::processValues($vals)) {
            return false;
        }
	$fieldnames = $this->getFieldNames();
	if (array_key_exists('enabled',$vals)
	    &&is_array($vals['enabled'])) {
	    $enabled_fields = array();
	    foreach ($vals['enabled'] as $field=>$enabled) {
		if (!$enabled
		    || !in_array($field,$fieldnames)
		    ){
		    continue;
		}
		$enabled_fields[] =  $field;
	    }
	    $this->storage->eraseChildren();
	    $this->storage->setValue($enabled_fields);
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
