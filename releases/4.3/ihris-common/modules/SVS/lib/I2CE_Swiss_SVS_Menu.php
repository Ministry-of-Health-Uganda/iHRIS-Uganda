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
* @package ihris-common
* @subpackage svs
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_SVS_Menu
* 
* @access public
*/


class I2CE_Swiss_SVS_Menu extends I2CE_Swiss{


    protected function getChildType($child) {
        if ($child =='lists') {
	    return 'SVS_Lists';
	}else {
	    return parent::getChildType($child);
	}
	
    }


    public function processValues($vals) {
	$oids = $this->storage->getKeys("list");
	if (!is_array($oids)) {
	    $oids = array();
	}
	if (array_key_existS('oid',$vals)		
	    && I2CE_MagicDataNode::checkKey($oid = $vals['oid'])
	    && !in_array($oid,$oids)
	    && array_key_exists('list',$vals)
	    && is_scalar($list = $vals['list'])
	    && ($listObj = I2CE_FormFactory::instance()->createContainer($list)) instanceof I2CE_List
	    ) {
	    $this->storage->lists->$oid = array('list'=>$list);
	}
	return true;
    }
	    


	


    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_svs_menu.html','div',$content_node)) instanceof DOMNode) {
            I2CE::raiseError("Could not load ");
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


    protected function displayMain($mainNode,$transient_options,$action) {
	if (($selectNode = $this->template->getElementByName('list',0,$mainNode )) instanceof DOMNode) {
	    $lists = array();
	    $ff = I2CE_FormFactory::instance();
	    foreach (I2CE::getConfig()->getKeys("/modules/forms/forms") as $form) {
		if (!($formObj = $ff->createContainer($form)) instanceof I2CE_List) {
		    continue;
		}
		$lists[]  = $form;
	    }
	    foreach ($lists as $list) {
		$attrs = array('value'=>$list);
		$selectNode->appendChild($this->template->createElement('option',$attrs,$list));
	    }
	}
	$this->renameInputs(array('list','oid'),$mainNode);
	return true;
    }

    protected function displayAjax($mainNode,$transient_options,$action) {
        if ( ($listsChild = $this->getChild('lists',true)) instanceof I2CE_Swiss
             && ( $listsNode = $this->template->getElementById('lists',$mainNode)) instanceof DOMNode
            ) {
            $listsChild->addAjaxLink('lists_link','contents', 'lists_ajax' ,$listsNode,$action, $transient_options);
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
