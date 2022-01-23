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
* Class I2CE_Swiss_FormClass_Field
* 
* @access public
*/


class I2CE_Swiss_FormField extends I2CE_Swiss {


    protected function getTemplate() {
        return 'swiss_formfield.html';
    }


    protected function getChildType($child) {
        if ($child =='headers') {
            return 'FormField_Headers';
	} else if ($child =='meta' && $this->hasMeta()) {
            return $this->metaSwiss();
        } else {
            return parent::getChildType($child);
        }
    }


    
    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        foreach (array('required','in_db','unique') as $key) {
            if (array_key_exists($key,$vals)) {
                $this->setField($key,$vals[$key]?1:0);
            }
        }
        if (array_key_exists('do_unique_field',$vals)
            && $vals['do_unique_field']
            &&(  !array_key_exists('unique_field',$vals) || !is_array($vals['unique_field']))
            ){
            $vals['unique_field'] = array();
        }
        if (array_key_exists('unique_field',$vals)
            && is_array($unique_fields = $vals['unique_field'])) {
            $new_fields = array();
            $fields = $this->parent->getFieldNames(true);
            $fields[] = 'parent';
            foreach ($unique_fields as $field => $selected) {
                if (!$selected
                    ||!in_array($field,$fields)
                    || $field == $this->name
                    ) {
                    continue;
                }
                $new_fields[] = $field;
            }
            $this->setField('unique_field',implode(',',$new_fields));
        }

        if (array_key_exists('formfield',$vals)
            && is_scalar($formfield = $vals['formfield'])
            && is_array( $formfields = I2CE::getConfig()->getKeys("/modules/forms/FORMFIELD"))
            && in_array($formfield,$formfields)
            ){
            $this->setField('formfield',$formfield);
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

    protected function metaSwiss() {
        return 'FormField_meta';
    }

    protected function hasMeta() {
        return false;
    }

    protected function displayAjax($mainNode,$transient_options,$action) {
        if ( ($headersChild = $this->getChild('headers',true)) instanceof I2CE_Swiss
             && ( $headersNode = $this->template->getElementById('headers',$mainNode)) instanceof DOMNode
            ) {
            $headersChild->addAjaxLink('headers_link','headers_container', 'headers_ajax' ,$headersNode,$action, $transient_options);
        }
        if ($this->hasMeta()) {
            if ( ($metaChild = $this->getChild('meta',true)) instanceof I2CE_Swiss
                 && ( $metaNode = $this->template->getElementById('meta',$mainNode)) instanceof DOMNode
                ) {
                $metaChild->addAjaxLink('meta_link','meta_container', 'meta_ajax' ,$metaNode,$action, $transient_options);
            }
        }
        return true;
    }

    protected function displayMain($mainNode,$transient_options,$action) {
        $inputs = array('required','unique','in_db','formfield','do_unique_field');
        $in_db = (!$this->hasField('in_db')) || ($this->getField('in_db')); //default to in_db = 1
        $this->template->selectOptionsImmediate('in_db',array($in_db),$mainNode);
        $this->template->selectOptionsImmediate('required',array($this->getField('required')?1:0),$mainNode);
        $this->template->selectOptionsImmediate('unique',array($this->getField('unique')?1:0),$mainNode);

        if  (($ffNode = $this->template->getElementByName('formfield',0,$mainNode)) instanceof DOMNode) {
            $formfields = I2CE::getConfig()->getKeys("/modules/forms/FORMFIELD");
            $selected = $this->getField('formfield');
            foreach ($formfields as $formfield) {
                $attrs = array('value'=>$formfield);
                if ($selected == $formfield) {
                    $attrs['selected'] = 'selected';
                }
                $ffNode->appendChild($this->template->createElement('option',$attrs,$formfield));
            }

        }
        if ($this->parent instanceof I2CE_Swiss_FormClass_Fields
            && ($ulNode = $this->template->getElementByName('unique_field',0,$mainNode)) instanceof DOMNode
            ) {            
            $existing = explode(',',$this->getField('unique_field'));
            $fields = $this->parent->getFieldNames(true);
            $fields[] = 'parent';
            foreach ($fields as $field) {
                if ($field == $this->name) {
                    continue;
                }
                $input = 'unique_field['. $field . ']';
                $inputs[] = $input;
                $attrs=array('value'=>1,'name'=>$input , 'type'=>'checkbox');
                if (in_array($field,$existing)) {
                    $attrs['checked'] = 'checked';
                }
                $ulNode->appendChild($liNode = $this->template->createElement('li',array()));
                $liNode->appendChild($this->template->createElement('input',$attrs));
                $liNode->appendChild($this->template->createTextNode($field));
            }
        }
        $this->template->setDisplayDataImmediate('has_meta',$this->hasMeta()?1:0,$mainNode);
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
