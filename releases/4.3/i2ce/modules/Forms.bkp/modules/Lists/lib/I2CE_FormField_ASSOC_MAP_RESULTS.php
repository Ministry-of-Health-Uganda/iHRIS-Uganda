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
* @package I2CE
* @subpackage Forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_FormField_ASSOC_MAP_RESULTS
* 
* @access public
*/


class I2CE_FormField_ASSOC_MAP_RESULTS extends I2CE_FormField_ASSOC_LIST {



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

     

    protected function showKey() {
	return false;
    }



    protected $has_auto_list = false;

    public function __construct( $name, $options=array() ) { 
	parent::__construct($name,$options);
	$this->has_auto_list =  I2CE_ModuleFactory::instance()->isEnabled('form-gizmos');
    }

    /**
     * Return the display value of this form field as a DOM Node.
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @return DOMNode
     */
    public function getDisplayNode( $node,$template ) {
        $text_node = $template->createElement('span', array('class'=>'assoc_container'));
        $value = $this->getValue();
        foreach ($value as $k=>$v) {
	    list($form,$id) = array_pad(explode('|',$k,2),2,'');
            $pair_node = $template->createElement('span', array('class'=>'assoc_pair_container'));
            $text_node->appendChild($pair_node);
	    $attrs = array('class'=>'assoc_pair_value');
	    if ($this->has_auto_list && $form && $id) {
		$elem = 'a';
		$attrs['href'] ='auto_list/view?form_name=' . $form  . '&id=' .  $id ;
	    } else {
		$elem = 'span';
	    }
            $pair_node->appendChild(  $template->createElement($elem, $attrs, $this->getSingleDisplayValue($v)));
        }
	return $text_node;
    }
    





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
