<?php
/**
* © Copyright 2013 IntraHealth International, Inc.
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
* @subpackage FormRelationship
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1
* @since v4.1
* @filesource 
*/ 
/** 
* Class I2CE_RelationshipData
* 
* @access public
*/


class I2CE_RelationshipData implements Iterator {
    
    protected $relationship;
    protected $data;
    protected $formIndices = array();
    protected $positions = array();
    public function __construct($relationship,$data) {
	$this->relationship = $relationship;
	$this->data = $data;
	$this->rewind();
    }
    public function rewind() {
	if (!$this->relationship instanceof I2CE_FormRelationship || !is_array($this->data)) {
	    return;
	}
	$this->positions= array();
	foreach ($this->relationship->getFormNames() as $formName) {
	    $this->positions[$formName] = $this->relationship->getForm($formName) . '|0';
	}	
	$this->walkRewind('primary_form',$this->data);
    }
    protected function walkRewind($form,$data_tree) {
	if ( !is_array($data_tree)
	     || !array_key_exists($form,$data_tree) 
	     || !is_array($data_tree[$form]) 
	     || count($data_tree[$form]) == 0
	    ) {
	    $this->positions[$form] = $this->relationship->getForm($form) . '|0';
	    //can't do anthing
	    return;
	}
	reset($data_tree[$form]);
	$pos = key($data_tree[$form]);
	$this->positions[$form] = $pos;
	if (array_key_exists('joins',$data_tree[$form][$pos]) && is_array($data_tree[$form][$pos]['joins'])) {
	    foreach ($this->relationship->getChildFormNames($form) as $child_form) {
		$this->walkRewind($child_form,$data_tree[$form][$pos]['joins']);
	    }
	}	
    }


    public function next() {
	if (!$this->relationship instanceof I2CE_FormRelationship || !is_array($this->data)) {
	    return;
	}
	if ( ! ($this->walkNext('primary_form',$this->data))) {
	    //we didn't advance so  zero everything out so that is is invalid
	    foreach ($this->relationship->getFormNames() as $formName) {
		$this->positions[$formName] = $this->relationship->getForm($formName) . '|0';
	    }		    
	}
    }
    protected function walkNext($form,$data_tree) {
	if ( !is_array($data_tree) 
	     ||!array_key_exists($form,$data_tree) 
	     || !is_array($data_tree[$form]) 
	     || count($data_tree[$form]) == 0
	    ) {
	    //can't do anthing
	    return false;
	}
	$pos = $this->positions[$form];
	//first we try to walkNext on the subs... if that fails, we try to walk next on the currentnode
	if (array_key_exists('joins',$data_tree[$form][$pos]) && is_array($data_tree[$form][$pos]['joins'])) {
	    foreach ($this->relationship->getChildFormNames($form) as $child_form) {
		if ($this->walkNext($child_form,$data_tree[$form][$pos]['joins'])) {
		    //we advanced;
		    return true;
		}
	    }
	}	
	//we didn't advance on the subs.  try to advance here.
	$next_pos = false;
	$found = false;
	foreach (array_keys($data_tree[$form]) as $t_pos) {
	    if ($t_pos == $pos) {
		$found = true;
		continue;
	    }
	    if ($found && !$next_pos) {
		$next_pos = $t_pos;
		break;
	    }
	}
	if (!$next_pos ) {
	    return false;
	}
	$this->positions[$form] = $next_pos;
	//we walked at this pos, we now need to update all positions underneath.
	if (array_key_exists('joins',$data_tree[$form][$next_pos]) && is_array($data_tree[$form][$next_pos]['joins'])) {	    
	    foreach ($this->relationship->getChildFormNames($form) as $child_form) {
		$this->walkRewind($child_form,$data_tree[$form][$next_pos]['joins']);
	    }
	}		
	return true;
    }
    
    
    public function valid () {	
	if (!$this->relationship instanceof I2CE_FormRelationship || !is_array($this->data)) {
	    return false;
	}
	foreach ($this->relationship->getFormNames() as $formName) {
	    $zero = $this->relationship->getForm($formName) . '|0';
	    if ( $this->positions[$formName] != $zero) {
		return true;
	    }
	}	
	return false;
    }
	
    public function key () {
	if (!$this->relationship instanceof I2CE_FormRelationship || !is_array($this->data)) {
	    return '';
	}
	return json_encode($this->positions);
    }
    public function current () {
	if (!$this->relationship instanceof I2CE_FormRelationship || !is_array($this->data)) {
	    return false;
	}
	$results = array();
	foreach ($this->relationship->getFormNames() as $formName) {
	    $results[$formName] = array();
	}
	$this->walkCurrent('primary_form',$this->data,$results);
	return $results;
    }
    protected function walkCurrent($form,$data_tree,&$results) {
	if (( ! ($pos = $this->positions[$form]))
	    || !array_key_exists($form,$data_tree) 
	    || !is_array($data_tree[$form]) 
	    || !array_key_exists($pos,$data_tree[$form]) 
	    || !is_array($data_tree[$form][$pos])) {
	    //can't do anthing
	    return;
	}
	if (array_key_exists('fields',$data_tree[$form][$pos]) && is_array($data_tree[$form][$pos]['fields'])) {
	    $results[$form] = $data_tree[$form][$pos]['fields'];
	}
	if (array_key_exists('joins',$data_tree[$form][$pos]) && is_array($data_tree[$form][$pos]['joins'])) {
	    foreach ($this->relationship->getChildFormNames($form) as $child_form) {
		$this->walkCurrent($child_form,$data_tree[$form][$pos]['joins'],$results);
	    }
	}
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
