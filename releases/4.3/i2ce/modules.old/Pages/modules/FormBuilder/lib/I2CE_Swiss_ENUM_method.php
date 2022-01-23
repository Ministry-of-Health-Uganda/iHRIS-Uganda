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
* Class I2CE_Swiss_ENUM_method
* 
* @access public
*/


class I2CE_Swiss_ENUM_method extends I2CE_Swiss{


    protected function getChildType($child) {
        if (in_array($child,array('module','static'))) {
            return 'ENUM_method_' . $child;
        } else {
            return parent::getChildType($child);
        }
    }

    
    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_enum_methods.html','div',$content_node)) instanceof DOMNode
            ){
	    I2CE::raiseError("Could not load template");
            return false;
	}
        foreach(array('module','static') as $child) {
            if ( ($swissChild = $this->getChild($child,true)) instanceof I2CE_Swiss
                 && ( $childNode = $this->template->getElementById('method_' . $child,$mainNode)) instanceof DOMNode
                ) {
                $swissChild->addAjaxLink('method_'. $child . '_link','association_container', 'method_' . $child . '_ajax' ,$childNode,$action, $transient_options);
            }
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
