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
* Class I2CE_Swiss_MAPPED_display
* 
* @access public
*/


class I2CE_Swiss_MAP_display extends I2CE_Swiss_MAPPED_display {
    

    protected function getStyles() {
        return array('ajax_list','tree','list','reportSelect');
    }


    protected static $childNames = array('printf','printf_args','no_limits','printf_arg_styles');

    protected function displayAjax($mainNode,$transient_options,$action) {
        if (!parent::displayAjax($mainNode,$transient_options,$action)) {
            return false;
        }        
        foreach (self::$childNames as $child) {
            if ( ($swissChild = $this->getChild($child,true)) instanceof I2CE_Swiss
                 && ( $childNode = $this->template->getElementById($child,$mainNode)) instanceof DOMNode
                ) {
                $swissChild->addAjaxLink($child. '_link','form_container', $child. '_ajax' ,$childNode,$action, $transient_options);
            }
        }
        if ( ($swissChild = $this->getChild('reportSelect',true)) instanceof I2CE_Swiss
             && ( $childNode = $this->template->getElementById('reportselect',$mainNode)) instanceof DOMNode
            ) {
            $swissChild->addAjaxLink('reportselect_link','reportselect_container', 'reportselect_ajax' ,$childNode,$action, $transient_options);
        }

        return true;
    }


    protected function getTemplate() {
        return 'swiss_map_display.html';
    }



    protected function getChildType($child) {
        if (in_array($child,self::$childNames)) {
            return 'MAP_' . $child . '_forms';
        }
        if ($child =='reportSelect') {
            return 'MAP_reportSelect';
        }
        return parent::getChildType($child);
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
