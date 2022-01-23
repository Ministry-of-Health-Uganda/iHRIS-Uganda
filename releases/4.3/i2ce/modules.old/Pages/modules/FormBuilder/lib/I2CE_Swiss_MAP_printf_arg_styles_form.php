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
* Class I2CE_Swiss_MAP_printf_form
* 
* @access public
*/


class I2CE_Swiss_MAP_printf_arg_styles_form extends I2CE_Swiss{


    public function processValues($vals) {
        I2CE::raiseError("Got " . print_r($vals,true));
        I2CE::raiseError("Check " . print_r($this->getStyles(),true));
        if (array_key_exists('style',$vals)           
	    && is_scalar($style = $vals['style'])
            && in_array($style,$this->getStyles())
	    ) {
            I2CE::raiseError("Setting: $style");
	    $this->storage->setValue($style);
	}
	return true;
    }


    protected function getStyles() {
        $class = false;
        if (($formObj = I2CE_FormFactory::instance()->createContainer($this->name)) instanceof I2CE_Form
            ) {
            $class =  get_class($formObj);
        }
        $available = array('default');
        while (I2CE_MagicDataNode::checkKey($class)) {
            if (is_array($t_available = I2CE::getConfig()->getKeys("/modules/forms/formClasses/$class/meta/list"))) {
                $available = array_merge($available,$t_available);
            }
            $class = get_parent_class($class);
        }
        $available = array_unique($available);
        return $available;
    }

    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_map_printf_arg_styles_form.html','div',$content_node)) instanceof DOMNode
            || ! ($selectNode = $this->template->getElementByName('style',0,$mainNode)) instanceof DOMNode
	    ) {
            I2CE::raiseError("Could not load template");
            return false;
        }
        $available = $this->getStyles();
        $selected = $this->storage->getValue();
        if (!in_array($selected,$available)) {
            $selected = 'default';
        }
        foreach ($available as $style) {
            $attrs = array('value'=>$style);
            if ($style == $selected) {
                $attrs['selected'] = 'selected';
            }
            $selectNode->appendChild($this->template->createElement('option',$attrs,$style));
        }
	$this->renameInputs(array('style'),$mainNode);
	return true;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
