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
* Class I2CE_Swiss_PageRelationship_Args
* 
* @access public
*/


abstract class I2CE_Swiss_PageRelationship_Args extends I2CE_Swiss_PageArgs{



    public function processValues($vals) {
        if (!parent::processValues($vals)) {
            return false;
        }
        $relationships = I2CE::getConfig()->getKeys("/modules/CustomReports/relationships");
        if (array_key_exists('relationship',$vals)
            && is_scalar($vals['relationship'])
            && (in_array($vals['relationship'],$relationships) || $vals['relationship'] =='')
            ) {
            $this->setField('relationship',$vals['relationship']);
        }
        return true;
    }

    public function displayArgs($mainNode,$transient_options, $action) {		
	if (!parent::displayArgs($mainNode,$transient_options,$action)) {
	    return false;
	}
        if ( ($relNode = $this->template->getElementByName('relationship',0,$mainNode)) instanceof DOMNode) {
            $relationships = I2CE::getConfig()->getKeys("/modules/CustomReports/relationships");
            $selected_rel = $this->getField('relationship');
            foreach ($relationships as $relationship) {
                $attrs = array('value'=>$relationship);
                if ($relationship == $selected_rel) {
                    $attrs['selected'] = 'selected';
                } 
                $title = false;
                if (I2CE::getConfig()->setIfIsSet($title,"/modules/CustomReports/relationships/$relationship/display_name")) {
                    $attrs['title'] = $title;
                }
                $relNode->appendChild($this->template->createElement('option',$attrs,$relationship));
            }
            if ($selected_rel) {
                $this->template->setDisplayDataImmediate('has_relationship',1,$mainNode);
                $this->template->setDisplayDataImmediate('relationship_link',"CustomReports/edit/relationships/" . $selected_rel, $mainNode);
            } else {
                $this->template->setDisplayDataImmediate('has_relationship',0,$mainNode);
            }
        }
        $this->renameInputs(array('relationship'),$mainNode);
	return true;
    }


    protected $relationshipObj = false; 


    public function getRelationship() { 
	if (! $this->relationshipObj  instanceof  I2CE_FormRelationship) {
	    $relationship = $this->getField('relationship');
	    try {
		$this->relationshipObj  = new I2CE_FormRelationship($relationship);
	    }  catch (Exception $e) {
                I2CE::raiseError("Couldn't get relationship $relationship:" . $e->getMessage());
		return false;
	    }

	}
	return $this->relationshipObj;

    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
