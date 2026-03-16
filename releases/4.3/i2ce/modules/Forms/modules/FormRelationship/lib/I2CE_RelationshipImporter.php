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
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_PageImportRelationshipData
* 
* @access public
*/


class I2CE_RelationshipImporter {
    
    protected $user =null;
    protected $args = array();
    protected $ff = null;
    protected function get_default_args($args) {
	if (!is_array($args)) {
	    $args = array();
	}
	if (!array_key_exists('new_form_ids',$args)) {
	    $args['new_form_ids'] = false;
	}
	if (!array_key_exists('only_child',$args)) {
	    $args['only_child'] = true;
	}

	return $args;
	
    }

    public function __construct($user,$args=array()) {
	$this->user = $user;
	$this->args = $this->get_default_args($args);
	$this->ff = I2CE_FormFactory::instance();
    }

    public function import_data($data) {
	if (is_string($data)) {
	    $doc = new DOMDocument();
            if (!$doc->loadXML($data)) {
                $this->raiseError($this->xmlError(libxml_get_errors(), "Could not load XML:\n$data"));
                libxml_clear_errors();
                libxml_use_internal_errors(false);
                return false;
            }        	    
	} else if ($data instanceof DOMNode) {
	    $doc = $data->ownerDocument;
	} else {
	    $doc = $data;
	}
	if (!$doc instanceof DOMDocument) {
	    I2CE::raiseError("Invalid data for import");
	    var_dump($data);
	    return false;
	}
	$rootElement = $doc->documentElement;
	$xpath_qry = false;
	switch ($rootElement->localName) {
	case 'relationshipCollection':
	    $xpath_qry = "/relationshipCollection/relationship";
	    break;
	case 'relationship':
	    $xpath_qry = "/relationship";
	    break;
	default:
	    I2CE::raiseError("Root element of loaded XML must be either <relationship/> or <relationshipCollection/>");
	    return false;
	}
	$xpath = new DOMXPath($doc);
	if (! ($nodes = $xpath->query($xpath_qry)) instanceof DOMNodeList) {
	    I2CE::raiseError("Badness in xpath: {$xpath_qry}");
	    return false;
	}
	I2CE::raiseError("Importing relationship");
	foreach ($nodes as $node) {
	    if (! ($this->import_relationship_instance($xpath,$node))) {
		I2CE::raiseError("Could not import node:\n". $doc->saveXML($node));
		return false;
	    }
	}
	return true;
    }



    protected function import_relationship_instance($xpath,$node,$parent_form = false, $parent_id = false) {
	if (!$xpath instanceof DOMXPath
	    || !$node instanceof DOMElement
	    || ! ($node->localName == 'relationship' || $node->localName ==  'joinedForm')
	    || ! ($node->hasAttribute('form'))
	    || ! ($form_name = $node->getAttribute('form'))
	    || ! ($form_nodes = $xpath->query("./form",$node)) instanceof DOMNodeList
	    || ! ($joined_forms = $xpath->query('./joinedForms/joinedForm',$node)) instanceof DOMNodeList
	    ){
	    I2CE::raiseError("Invalid node passed to parse:" . $xpath->document->saveXML($node));
	    return false;
	}		
	foreach ($form_nodes as $form_node) {
	    if (!  ($form = $this->ff->createContainer($form_name)) instanceof I2CE_Form) {
		I2CE::raiseError("Could not create $form_name");
		return false;
	    }
	    if ($parent_id && $parent_form) {
		$form_node->setAttribute('parent_form',$parent_form);
		$form_node->setAttribute('parent_id',$parent_id);
	    }
	    $form_node->setAttribute('modified',date("Y-m-d H:i:s"));
	    $form->loadFromXML($form_node);
	    if ($this->args['new_form_ids'] ) {
		$form->setID(0);
	    }
	    if (! ($form->save($this->user))) {
		I2CE::raiseError("Could not save:" . $xpath->document->saveXML($form_node));
		return false;
	    }
	    I2CE::raiseError("Saved imported form " . $form->getFormID());
	    $form->cleanup();
	}
	foreach ($joined_forms as $joined_form) {
	    if (! $joined_form->hasAttribute('join_style') 
		|| !  ( $style = $joined_form->getAttribute('join_style'))
		) {
		I2CE::raiseError("No join style set");
		return false;
	    }
	    if ($style == 'child') {	    
		$joined_parent_form = $form_name;
		$joined_parent_id = $form->getID();
	    } else {
		if ($this->args['only_child']) {
		    continue;
		}
		$joined_parent_id =false;
		$joined_parent_form =false;
	    }
	    if (! ($this->import_relationship_instance($xpath,$joined_form,$joined_parent_form,$joined_parent_id))) {
		return false;
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
