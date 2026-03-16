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
* Class I2CE_Swiss_MAPPED_meta
* 
* @access public
*/


class I2CE_Swiss_MAPPED_meta extends I2CE_Swiss_REFERENCE_meta {

    protected function getChildType($child) {
        if ($child =='display') {
            return 'MAPPED_displays';
        } else if ($child =='add_limit_module') {
            return 'MAPPED_add_limit_module';
        } else if ($child =='limits') {
            return 'MAPPED_limits';
        } else {
            return parent::getChildType($child);
        }
    }



    protected function getTemplate() {
        return 'swiss_mapped_meta.html';
    }


    protected function displayAjax($mainNode,$transient_options,$action) {
        if (!parent::displayAjax($mainNode,$transient_options,$action)) {
            return false;
        }
        if ( ($limitsChild = $this->getChild('limits',true)) instanceof I2CE_Swiss
             && ( $limitsNode = $this->template->getElementById('limits',$mainNode)) instanceof DOMNode
            ) {
            $limitsChild->addAjaxLink('limits_link','limits_container', 'limits_ajax' ,$limitsNode,$action, $transient_options);
        }
        if ( ($add_limit_moduleChild = $this->getChild('add_limit_module',true)) instanceof I2CE_Swiss
             && ( $add_limit_moduleNode = $this->template->getElementById('add_limit_module',$mainNode)) instanceof DOMNode
            ) {
            $add_limit_moduleChild->addAjaxLink('add_limit_module_link','association_container', 'add_limit_module_ajax' ,$add_limit_moduleNode,$action, $transient_options);
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
