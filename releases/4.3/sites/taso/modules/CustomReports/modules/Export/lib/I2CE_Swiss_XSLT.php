<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @subpackage customrports
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.6
* @since v4.0.6
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_XSLT
* 
* @access public
*/


class I2CE_Swiss_XSLT extends I2CE_Swiss{
    
    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */    
    public function processValues($vals) {        
        if (!array_key_exists('edit_style',$vals) ) {
            return true;
        }
        switch ($vals['edit_style']) {
        case 'upload':
            if (!array_key_exists('__files_upload',$vals) || ! ($vals['__files_upload']) || !array_key_exists($vals['__files_upload'],$_FILES) || !$_FILES[$vals['__files_upload']]['name']) {
                return true;
            }
            if (!is_readable($_FILES[$vals['__files_upload']]['tmp_name'])) {
                return false;
            }
            $contents = file_get_contents($_FILES[$vals['__files_upload']]['tmp_name']);
            if (!$contents) {
                return false;
            }
            break;
        case 'edit':
            $contents = $vals['definition'];
            break;
        default:
            return false;
        }
        $this->setField('definition',$contents);
        return true;
    }


    

    public function displayValues($contentNode, $transient_options,$action) {
        if (!($mainNode = $this->template->appendFileByNode('swiss_xslt.html','div',$contentNode)) instanceof DOMNode) {
            return false;
        }        
        $this->template->setDisplayDataImmediate('name',$this->getDisplayName(),$mainNode);
        $this->template->setDisplayDataImmediate('definition',$this->getField('definition'),$mainNode);
        $inputs = $this->renameInputs(array('upload','edit_style','definition'),$mainNode);
        return true;
    }

    


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
