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
* Class I2CE_Swiss_PageXMLRelationship_FormFields
* 
* @access public
*/


class I2CE_Swiss_PageXMLRelationship_FormFields extends I2CE_Swiss {


    public function getRelationship() { 
	if (($parentSwiss = $this->getParent()) instanceof I2CE_Swiss_PageRelationship_Args) {
	    return $parentSwiss->getRelationship();
	}
	return false;
    }




    protected function getChildType($child) {
	if (in_array($child,$this->getFormNames())) {
	    return 'PageXMLRelationship_Form';
	} 
	return parent::getChildType($child);
    }


    protected function getFormNames() {
	if (($parentSwiss = $this->getParent()) instanceof I2CE_Swiss_PageRelationship_Args
	    && ($relObj = $parentSwiss->getRelationship()) instanceof I2CE_FormRelationship
	    ) {
	    return  $relObj->getFormNames();
	} else {
	    return array();
	}

    }

    public function displayValues($content_node,$transient_options, $action) {
	if (! ($mainNode = $this->template->appendFileByNode('swiss_xmlrelationship_formfields.html','div',$content_node)) instanceof DOMNode) {
            return false;
        }
        $listNode = $this->template->getElementById('forms_list', $mainNode);
        if (!$listNode instanceof DOMElement) {
            I2CE::raiseError("Don't know where to add forms");
            return false;
        }
	foreach ($this->getFormNames()  as $formname) {
	    if (! ($swissChild = $this->getChild($formname,true)) instanceof I2CE_Swiss) {
		continue;
	    }
            $formNode = $this->template->appendFileByNode('swiss_xmlrelationship_formfields_each.html','li',$listNode);            
            if (!$formNode instanceof DOMNode) {
                continue;
            }
	    $name = $formname; 
	    if ($formname =='primary_form'
		&&  ($relObj = $this->getRelationship()) instanceof I2CE_FormRelationship
		) {
		$name = $relObj->getForm('primary_form') . ' (primary_form)';
	    }
            $this->template->setDisplayDataImmediate('name',$name,$formNode);
            $swissChild->addAjaxLink('form_link','form_contents','form_ajax',$formNode,$action, $transient_options);            
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
