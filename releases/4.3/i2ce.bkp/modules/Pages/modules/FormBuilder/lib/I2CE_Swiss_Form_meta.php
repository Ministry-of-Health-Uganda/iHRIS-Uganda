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
* @subpackage formbuilder
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_Form_meta
* 
* @access public
*/


class I2CE_Swiss_Form_meta extends I2CE_Swiss{

    protected function getChildType($child) {
        if ($child == 'child_form_data') {
            return 'Form_child_form_data_displays';
        } else {
            return parent::getChildType($child);
        }
    }


    public function processValues($vals) {
        if (array_key_exists('child_forms',$vals)
            && is_array($child_forms= $vals['child_forms'])	    
	    && ($md = $this->storage->traverse("child_forms",true,false)) instanceof I2CE_MagicDataNode
	    ){
	    $allowed = I2CE::getConfig()->getKeys("/modules/forms/forms");
	    $valid = array();
	    foreach ($child_forms as $child_form => $enabled) {
		if (!in_array($child_form,$allowed)
		    || !$enabled
		    ) {
		    continue;
		}
		$valid[] = $child_form;
	    }
	    $md->eraseChildren();
	    $md->setValue($valid);
        }
	if (array_key_exists('description',$vals)
            && is_scalar($description= $vals['description'])
            ) {
            $this->setTranslatableField('description',$description);
        }
        return true;
    }


    public function getSelectedForms() {
        $forms = $this->storage->getAsArray('child_forms');
        if (!is_array($forms)) {
            $forms = array();
        }
        return $forms;
    }
    
    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_form_meta.html','div',$content_node)) instanceof DOMNode) {
            I2CE::raiseError("no template ");
            return false;
        }
	$inputs = array('description');
	$forms = I2CE::getConfig()->getKeys("/modules/forms/forms");
	$selected = $this->getSelectedForms();
	if (!is_array($selected)) {//in case it wan't ever set
	    $selected = array();
	}
	if (($formsNode = $this->template->getElementByName('child_forms',0,$mainNode)) instanceof DOMNode) {
	    foreach ($forms as $form) {
		$input = 'child_forms[' . $form . ']';
		$inputs[] = $input;
		$attr = array('value'=>1,'name' => $input,'type'=>'checkbox');
		if (in_array($form,$selected)) {
		    $attr['checked'] = 'checked';
		}
		$formsNode->appendChild($formNode = $this->template->createElement('span',array('style'=>'display:inline-block;width:33%; min-width:33%')));
		$formNode->appendChild($this->template->createElement('input',$attr));
		$formNode->appendChild($this->template->createTextNode($form));
	    }
	}
	
        $this->template->setDisplayDataImmediate('description',$this->getField('description'),$mainNode);
        $this->renameInputs($inputs,$mainNode);        
        if ( ($swissChild = $this->getChild('child_form_data',true)) instanceof I2CE_Swiss
             && ( $childNode = $this->template->getElementById('child_form_data',$mainNode)) instanceof DOMNode
            ) {
            $swissChild->addAjaxLink('child_form_data_link','display_container', 'child_form_data_ajax' ,$childNode,$action, $transient_options);
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
