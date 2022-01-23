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
* Class I2CE_Swiss_ASSOC_MAP_meta
* 
* @access public
*/


class I2CE_Swiss_ASSOC_MAP_meta extends I2CE_Swiss_ASSOC_LIST_meta {

    protected function getListForms() {
	$forms = I2CE::getConfig()->getKeys('/modules/forms/forms');
	$lists = array();
	$ff = I2CE_FormFactory::instance();
	foreach ($forms as $form) {
	    if (! ($ff->createContainer($form)) instanceof I2CE_List) {
		continue;
	    }
	    $lists[] =  $form;
	}
	return $lists;
    }


    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        if (array_key_exists('list',$vals)
            && is_scalar($list = $vals['list'])
	    && ($list == '' || in_array($list,$this->getListForms()))
            ) {
	    $this->setField('list',$list);
        }
        return parent::processValues($vals);
    }


    protected function getTemplate() {
        return 'swiss_assoc_map_meta.html';
    }
    

    protected function displayMeta($mainNode,$transient_options,$action) {
        if (! parent::displayMeta($mainNode,$transient_options,$action)) {
            return false;
        }
        if (! ($listNode = $this->template->getElementByName('list',0,$mainNode)) instanceof DOMNode
	    ) {
            I2CE::raiseError("Don't know where to add lists");
            return false;
        }
	$list = $this->getField('list');
	foreach ($this->getListForms() as $form) {
	    $attrs = array('value'=>$form);
	    if ($list == $form) {
		$attrs['selected']='selected';
	    }
	    $listNode->appendChild($this->template->createElement('option',$attrs,$form));
	}
        $this->renameInputs(array('list'),$mainNode);
        return true;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
