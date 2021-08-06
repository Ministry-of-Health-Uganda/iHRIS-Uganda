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
* @subpackage page
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_PageFormAutoView
* 
* @access public
*/


class I2CE_Swiss_CSD_IL_HWR_Redirect_Args extends I2CE_Swiss_PageArgs {


    

    public function processValues($vals) {
        if (!parent::processValues($vals)) {
            return false;
        }
        $fields = array('il_iwr_host');
        foreach ($fields as $field) {
            if (!array_key_exists($field, $vals)
                || ! is_scalar($val = $vals[$field])
                ) {
                continue;
            }
            $this->setField($field, $val);	   
                
        }
            
        return true;
    }

    protected function getTemplate() {
        return 'swiss_page_csd_il_hwr_redirect_args.html';
    }

	
    public function displayValues($content_node, $transient_options, $action) {
        // Append the html template and save it as a DOMNode object
        if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(), 'div', $content_node)) instanceof DOMNode) {
            return false;
        }


        
        // Display the arguments for the page
        if (!($this->displayArgs($mainNode, $transient_options, $action))) {
            return false;
        }
        return true;
    }


    public function displayArgs($mainNode, $transient_options, $action) {
    	// Call I2CE_Swiss_PageArgs->displayArgs() to display the page's title, tasks, and default html files
        if (!parent::displayArgs($mainNode, $transient_options, $action)) {
            //parent function failed
            return false;
        }
        $fields = array('il_hwr_host');
        foreach ($fields as $field) {
            $this->template->setDisplayDataImmediate($field,$this->getField($field));
        }
        $this->renameInputs($fields, $mainNode);

        return true;
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
