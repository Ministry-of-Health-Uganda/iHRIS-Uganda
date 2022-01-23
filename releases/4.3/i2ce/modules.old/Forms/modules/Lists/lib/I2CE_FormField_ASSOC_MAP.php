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
* @subpackage fields
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0.1
* @since v4.2.0.1
* @filesource 
*/ 
/** 
* Class I2CE_FormField_ASSOC_MAP
* 
* @access public
*/


class I2CE_FormField_ASSOC_MAP extends I2CE_FormField_ASSOC_LIST{



    public function loadFromXML($node) {
        if (!$node instanceof DOMElement
            || ! ($val_nodes = $node->getElementsByTagName('value')) instanceof DOMNodeList
            || ! ($val_nodes->length > 0)
            ) {
            return;
        }
        $value = array();
        foreach ($val_nodes as $val_node) {
            if (! $val_node instanceof DOMElement
                || ! $val_node->hasAttribute('keyid')
                || ! $val_node->hasAttribute('keyform')
                ) {
                continue;
            }
            $value[$val_node->getAttribute('keyform') . '|' . $val_node->getAttribute('keyid')] = $val_node->textContent;
        }
        $this->setValue($value);
    }



    /**
     * Appends an XML representation of the field data onto the current node
     * @param DOMNode $field_node the node we are appending the representation onto
     */
    protected function appendXMLRepresentation($field_node) {
        $doc = $field_node->ownerDocument;
        if ( is_array($value = $this->getValue())) {
            foreach ($value as $k=>$v) {
                list($form,$id) = array_pad(explode("|",$k,2),2,'');
                $field_node->appendChild($val_node = $doc->createElement('value',$v));
                $val_node->setAttribute('keyid',$id);
                $val_node->setAttribute('keyform',$form);
            }
        }
    }

     
    protected $list_values = null;
	
    protected function loadList() {
	if (is_array($this->list_values)) {
	    return ;
	}
	$this->list_values = array();
        $path = "meta/list";
        if (!$this->optionsHasPath($path) 
	    ||!  is_string( $list_form = $this->getOptionsByPath($path))
	    || ! is_array($list_values = I2CE_List::listOptions($list_form))
	    ) {
	    return;
	}
	foreach ($list_values as $data) {
	    if (!is_array($data)
		|| !array_key_exists('value',$data)
		|| !array_key_exists('display',$data)
		) {
		continue;
	    }
	    $this->list_values[$data['value']] = $data['display'];
	}
    }


    protected function ensureKeys() {
	$this->loadList();
	foreach (array_keys($this->list_values) as $key) {
	    $this->ensureKey($key);
	}
    }

    public function getKeys()  {
	$this->loadList();
	return array_keys($this->list_values);
    }


    public function setValueOfKey($key,$val) {
	$this->loadList();
	if (!array_key_exists($key,$this->list_values)) {
	    return false;
	}
    }

    public function ensureKey($key)  {
	$this->loadList();
        if (!$this->issetValue()) {
            $this->value = array();
        }
	if (!array_key_exists($key,$this->list_values)) {
	    return false;
	}
        if (!array_key_exists($key,$this->value)) {
            $this->value[$key] = '';
        }
	return true;
    }

          
    protected function getSingleDisplayKey($key) {
	$this->loadList();
	if (array_key_exists($key,$this->list_values)) {
	    return $this->list_values[$key];
	} else {
	    return "<<" . $key . ">>";
	}
    }



    public function processDOMEditable($node,$template,$form_node) {
	$this->ensureKeys();
	return parent::processDOMEditable($node,$template,$form_node);
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
