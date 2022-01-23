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
* @subpackage formbuilder
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_ASSOC_LIST_meta
* 
* @access public
*/


class I2CE_Swiss_ASSOC_LIST_meta extends I2CE_Swiss {



    protected function getTemplate() {
        return 'swiss_assoc_list_meta.html';
    }
    

    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(),'div',$content_node)) instanceof DOMNode
            ){
	    I2CE::raiseError("Could not load template");
            return false;
        }
        if (!$this->displayMeta($mainNode,$transient_options,$action)) {
            return false;
        }
        return true;
    }



    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
	foreach (array_keys(self::$bool_keys) as $k) {
	    if (array_key_exists($k,$vals)
		&& is_scalar($vals[$k])
		) {
		$this->setField($k,$k?1:0);
	    }
	}
	if (array_key_exists('key_sort',$vals)
	    && is_scalar($sort = $vals['key_sort'])
	    && in_array($sort,self::$allowed_sorts)
	    ) {
	    $this->setField('key_sort',$sort);
	}
	return parent::processValues($vals);
    }

    protected static $bool_keys = array('allow_new' => 0,'allow_delete' =>0 ,'show_key' => 1);
    protected static $allowed_sorts = array('NONE','SORT_REGULAR','SORT_NUMERIC','SORT_STRING','SORT_STRING_CASE','SORT_LOCAL_STRING','SORT_NATURAL','SORT_NATURAL_CASE');

    protected function displayMeta($mainNode,$transient_options,$action) {
	foreach (self::$bool_keys as $k=>$default) {
	    if ($this->hasField($k)) {
		$val = $this->getField($k)?1:0;
	    } else {
		$val = $default;
	    }
	    $this->template->selectOptionsImmediate($k,array($val),$mainNode);
	}
	$this->renameInputs(array_keys(self::$bool_keys),$mainNode);


        if (! ($sortNode = $this->template->getElementByName('key_sort',0,$mainNode)) instanceof DOMNode
	    ) {
            I2CE::raiseError("Don't know where to add key sorts");
            return false;
        }
	$sort = $this->getField('key_sort');
	if (!in_array($sort,self::$allowed_sorts)) {
	    $sort = 'SORT_NATURAL_CASE'; //the default
	}
	foreach (self::$allowed_sorts as $s) {
	    $attrs = array('value'=>$s);
	    if ($sort == $s) {
		$attrs['selected']='selected';
	    }
	    $sortNode->appendChild($this->template->createElement('option',$attrs,$s));
	}
        $this->renameInputs(array('key_sort'),$mainNode);
        return true;
    }







}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
