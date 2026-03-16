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


class  I2CE_Swiss_PageRelationshipGenerateTemplate_Required extends I2CE_Swiss{



    public function getRelationship() { 
	if (($parentSwiss = $this->getParent()) instanceof I2CE_Swiss_PageRelationshipGenerateTemplate_Args) {
	    return $parentSwiss->getRelationship();
	}
	return false;
    }

    protected function getFieldNames() {
	if ( ($relObj = $this->getRelationship()) instanceof I2CE_FormRelationship) {
	    $fieldnames = array();
	    foreach ($relObj->getFormNames() as $reportform) {
		if ( ! ($form = $relObj->getForm($reportform))
		     || ! ($formObj = $relObj->getContainer($form)) instanceof I2CE_Form
		    ) {
		    continue;
		}
		foreach (  $formObj->getFieldNames() as $fieldname) {
		    if (! ($fieldObj = $formObj->getField($fieldname)) instanceof I2CE_FormField
			|| !$fieldObj->isInDB()) {
			continue;
		    }
		    $fieldnames[] = $reportform .'+' . $fieldname;
		}
	    }
	    return $fieldnames;
	} else {
	    return array();
	}

    }


    public function displayValues($contentNode, $transient_options, $action) {
        if ( ! ($mainNode = $this->template->appendFileByNode('swiss_relationshiptemplate_required.html','div',$contentNode))  instanceof DOMNode
	     || ! ($listNode = $this->template->getElementById('fields_list', $mainNode)) instanceof DOMNode
	    ) {
            I2CE::raiseError("Unable to add reported form template");
            return false;
        }       
	
	$required = $this->storage->getAsArray();	
	I2CE::raiseError("Required are " . print_r($required,true));
	foreach ($this->getFieldNames() as $fieldname) {
	    if ( !($fieldNode = $this->template->appendFileByNode('swiss_relationshiptemplate_field.html','div',$listNode)) instanceof DOMNode
		 || ! ($requiredNode = $this->template->getElementByName('required',0,$fieldNode)) instanceof DOMNode
		) {
                continue;
            }
            $this->template->setDisplayDataImmediate('name',$fieldname,$fieldNode);
	    $input = 'required[' . $fieldname .']';
	    $requiredNode->setAttribute('name',$input);
	    if (in_array($fieldname,$required)) {
		$requiredNode->setAttribute('checked','checked');
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
	if (array_key_exists('required',$vals)
	    &&is_array($vals['required'])) {
	    $required_fields = array();
	    foreach ($vals['required'] as $field=>$required) {
		if (!$required
		    || !in_array($field,$fieldnames)
		    ){
		    continue;
		}
		$required_fields[] =  $field;
	    }
	    $this->storage->eraseChildren();
	    $this->storage->setValue($required_fields);
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
