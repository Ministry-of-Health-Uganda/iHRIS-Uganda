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


class I2CE_Swiss_REFERENCE_display extends I2CE_Swiss {

    protected static $childNames = array('printf','printf_args','no_limits');
    protected function getChildType($child) {
        if (in_array($child,self::$childNames)) {
            return 'MAP_' . $child . '_forms';
        }
        if ($child =='reportSelect') {
            return 'MAP_reportSelect';
        }
        return parent::getChildType($child);
    }




    protected function getTemplate() {
        return 'swiss_reference_display.html';
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

    protected function displayMain($mainNode,$transient_options,$action) {
        return true;
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
