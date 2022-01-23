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
* Class I2CE_Swiss_Form_child_form_data
* 
* @access public
*/


class I2CE_Swiss_Form_child_form_data extends I2CE_Swiss{


    protected function getChildType($child) {
        if ($child == 'limits') {
            return 'child_form_data_where';
	} else if ($child == 'order') {
	    return 'child_form_data_order';
        } else {
            return parent::getChildType($child);
        }
    }



    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_child_form_data.html','div',$content_node)) instanceof DOMNode) {
            I2CE::raiseError("no template");
            return false;
        }
	if ( ($swissChild = $this->getChild('limits',true)) instanceof I2CE_Swiss
             && ( $childNode = $this->template->getElementById('limits',$mainNode)) instanceof DOMNode
            ) {
            $swissChild->addAjaxLink('limits_link','relationship_where_container', 'limits_ajax' ,$childNode,$action, $transient_options);
        }
        if ( ($swissChild = $this->getChild('order',true)) instanceof I2CE_Swiss
             && ( $childNode = $this->template->getElementById('order',$mainNode)) instanceof DOMNode
            ) {
            $swissChild->addAjaxLink('order_link','orders_container', 'order_ajax' ,$childNode,$action, $transient_options);
        }
	

    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
