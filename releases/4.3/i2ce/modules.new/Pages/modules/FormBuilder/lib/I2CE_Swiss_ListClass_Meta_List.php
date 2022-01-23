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
* Class I2CE_Swiss_ListClass_Meta_List
* 
* @access public
*/


class I2CE_Swiss_ListClass_Meta_List extends I2CE_Swiss{

    protected function getChildType($child) {
	if ($child =='display_args' || $child == 'sort_fields') {
	    return 'ListClass_Meta_List_field_sorter';
	}
	return parent::getChildType($child);
    }



    public function processValues($vals) {
	if (array_key_exists('display_string',$vals)
	    && is_scalar($ds = $vals['display_string'])) {
	    $this->setTranslatableField('display_string',$ds);
	}
	return true;
    }

    protected function getTemplate() {
        return 'swiss_list_class_list.html';
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
        if ( ($display_argsChild = $this->getChild('display_args',true)) instanceof I2CE_Swiss
             && ( $display_argsNode = $this->template->getElementById('display_args',$mainNode)) instanceof DOMNode
            ) {
            $display_argsChild->addAjaxLink('display_args_link','orders_container', 'display_args_ajax' ,$display_argsNode,$action, $transient_options);
        }
	if ( ($sort_fieldsChild = $this->getChild('sort_fields',true)) instanceof I2CE_Swiss
	     && ( $sort_fieldsNode = $this->template->getElementById('sort_fields',$mainNode)) instanceof DOMNode
	    ) {
	    $sort_fieldsChild->addAjaxLink('sort_fields_link','orders_container', 'sort_fields_ajax' ,$sort_fieldsNode,$action, $transient_options);
	}
        return true;
    }

    protected function displayMain($mainNode,$transient_options,$action) {
        $this->template->setDisplayDataImmediate('display_string',$this->getField('display_string'),$mainNode);
        $this->renameInputs(array('display_string'),$mainNode);
        return true;
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
