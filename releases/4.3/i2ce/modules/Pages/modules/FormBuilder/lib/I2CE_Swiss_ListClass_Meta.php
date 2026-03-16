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
* Class I2CE_Swiss_ListClass_Meta
* 
* @access public
*/


class I2CE_Swiss_ListClass_Meta extends I2CE_Swiss_FormClass_Meta {
    protected function getTemplate() {
        return 'swiss_list_class_meta.html';
    }

    protected function getChildType($child) {
	if ($child =='list') {
	    return 'ListClass_Meta_Lists';
	}
	return parent::getChildType($child);
    }

    public function displayMeta($mainNode,$transient_options,$action) {
        if (!parent::displayMeta($mainNode,$transient_options,$action)) { 
            return false;
        }
        if ( ($listChild = $this->getChild('list',true)) instanceof I2CE_Swiss
             && ( $listNode = $this->template->getElementById('list',$mainNode)) instanceof DOMNode
            ) {
            $listChild->addAjaxLink('list_link','list_container', 'list_ajax' ,$listNode,$action, $transient_options);
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
