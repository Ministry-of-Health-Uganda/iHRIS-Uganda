<?php
/**
 * @copyright © 2008-11 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
*/
/**
*  I2CE_CustomReport_Display_Search -- the search HTML display of 
*  a report view
* @package iHRIS
* @subpackage Common
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.0
* @access public
*/


class I2CE_CustomReport_Display_Search extends I2CE_CustomReport_Display_Default {

    /** 
     * Adds any report display controls that can be added for this view.
     * @param DOMNode $conentNode
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @returns boolean $true on success
     */
    protected function displayReportControls($contentNode, $controls=null) {
        $this->template->addHeaderLink('mootools-core.js');
        $this->template->addHeaderLink('I2CE_ClassValues.js');
        $this->template->addHeaderLink('I2CE_SubmitButton.js');
        $controlNode = $this->template->createElement('span',array('class'=>"CustomReport_control",'id'=>"CustomReport_controls_Search"));        
        $contentNode->appendChild($controlNode);
        $this->displayReportControl($controlNode);
        return true;
    }   

    
    /**
     * Return the list of pivots
     * @return array
     */
    protected function getPivots() {
        //we don't want to allow pivoting
        return array();
    }   

}


?>
