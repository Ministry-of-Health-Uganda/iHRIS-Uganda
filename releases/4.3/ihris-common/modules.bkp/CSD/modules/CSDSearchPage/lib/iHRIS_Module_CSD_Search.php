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
* @package csd-provider-registry
* @subpackage search
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class iHRIS_Module_CSD_Search
* 
* @access public
*/


class iHRIS_Module_CSD_Search extends I2CE_Module {

    public static function getMethods() {
        return array(
            'I2CE_FormField_MAP->checkStyle_csd_search' => 'checkStyle_csd_search',
            'I2CE_FormField_MAP->processDOMEditable_csd_search' => 'processDOMEditable_csd_search',
	    );
    }

    public function checkStyle_csd_search($formfield) {
	return CSD_SearchMatches::checkStyle_csd_search($formfield);
    }

    /**
     * Creates an ajax set of drop downs that are populated during the page load with
     * web services and as you select each level.
     * @param I2CE_Template $template
     * @param DOMNode $node -- the node that requested this drop down
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.  Defaults to 0
     * @returns mixed DOMNode or an array of DOMNodes to add.
     */
    public function processDOMEditable_csd_search($formfield, $node, $template, $form_node,$show_hidden = 0) {
	return CSD_SearchMatches::processDOMEditable_csd_search($formfield, $node, $template, $form_node,$show_hidden);
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
