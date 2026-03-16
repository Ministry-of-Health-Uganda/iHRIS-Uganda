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
* Class I2CE_Swiss_MAPPED_displays
* 
* @access public
*/


class I2CE_Swiss_REFERENCE_displays extends I2CE_Swiss {
    

    public function processValues($vals) {
	if (array_key_exists('new',$vals)
	    && I2CE_MagicDataNode::checkKey($new = $vals['new'])
	    && !in_array($new,$this->storage->getKeys())) {
	    $this->storage->$new = array();
	}
	return true;
    }

    protected function getChildType($child) {
	return 'REFERENCE_display';
    }

    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_mapped_displays.html','div',$content_node)) instanceof DOMNode
	    || !($displaysNode = $this->template->getElementByName('displays',0,$mainNode))
	    ) {
            I2CE::raiseError("Could not load swiss_mapped_displays.html");
            return false;
        }
	$this->renameInputs(array('new'),$mainNode);
	foreach ($this->storage->getKeys() as $name) {	    
	    $displaysNode->appendChild($liNode = $this->template->createElement('li'));
	    if ( !($displayNode =$this->template->appendFileByNode('swiss_mapped_displays_each.html','div',$liNode)) instanceof DOMNode
		 || !($swissDisplay = $this->getChild($name)) instanceof I2CE_Swiss
		) {
		continue;
	    }
	    $this->template->setDisplayDataImmediate('name',$name,$displayNode);
	    $swissDisplay->addAjaxLink('display_link','display_container','display_ajax',$displayNode,$action, $transient_options);            
	    $delete_link = $swissDisplay->getURLRoot('delete_class')  .  $swissDisplay->path;
	    $this->template->setDisplayDataImmediate('delete_link',$delete_link,$displayNode);

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
