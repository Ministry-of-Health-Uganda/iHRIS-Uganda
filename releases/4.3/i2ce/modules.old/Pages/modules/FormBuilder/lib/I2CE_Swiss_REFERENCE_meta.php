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
* @subpackage form-builder
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_MAPPED_meta
* 
* @access public
*/


class I2CE_Swiss_REFERENCE_meta extends I2CE_Swiss{

    protected function getChildType($child) {
        if ($child =='display') {
            return 'REFERENCE_displays';
        } else {
            return parent::getChildType($child);
        }
    }

    protected function getTemplate() {
        return 'swiss_reference_meta.html';
    }


    public function processValues($vals) {
	if (array_key_exists('form_any',$vals)) {
	    $this->setField('form_any',$vals['form_any']?1:0);
	}
	if (array_key_exists('forms',$vals)
	    && is_array($vals['forms'])
	    && ($formsNode = $this->storage->traverse('form',true,false)) instanceof I2CE_MagicDataNode
	    ) {
	    $forms = I2CE::getConfig()->getKeys('/modules/forms/forms');
	    $new_forms = array();
	    foreach($vals['forms'] as $form=>$selected) {
		if (!is_scalar($form)
		    ||!$selected
		    ||!in_array($form,$forms)) {
		    continue;
		}
		$new_forms[] = $form;
	    }
	    $formsNode->eraseChildren();
	    $formsNode->setValue($new_forms);
	}
	return true;
    }


    
    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(),'div',$content_node)) instanceof DOMNode) {
            I2CE::raiseError("Could not load " . $this->getTemplate());
            return false;
        }
        if ( !($this->displayMain($mainNode,$transient_options,$action))) {
            return false;
        }
        if (! ($this->displayAjax($mainNode,$transient_options,$action))) {
            return true;
        }
        return true;
    }

    protected function displayAjax($mainNode,$transient_options,$action) {
        if ( ($displayChild = $this->getChild('display',true)) instanceof I2CE_Swiss
             && ( $displayNode = $this->template->getElementById('display',$mainNode)) instanceof DOMNode
            ) {
            $displayChild->addAjaxLink('display_link','display_container', 'display_ajax' ,$displayNode,$action, $transient_options);
        }

	return true;
    }


    protected function displayMain($mainNode,$transient_options,$action) {
	$inputs = array('form_any');
	$form_any = $this->getField('form_any')?1:0;
        $this->template->selectOptionsImmediate('form_any',array($form_any),$mainNode);
	$forms = I2CE::getConfig()->getKeys("/modules/forms/forms");
	$selected =$this->storage->getAsArray('form');
	if (!is_array($selected)) {//in case it wan't ever set
	    $selected = array();
	}
	if (($formsNode = $this->template->getElementByName('forms',0,$mainNode)) instanceof DOMNode) {
	    foreach ($forms as $form) {
		$input = 'forms[' . $form . ']';
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
	$this->renameInputs($inputs,$mainNode);
	return true;
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
