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
* @subpackage formbuilder
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_ASSOC_LIST_meta
* 
* @access public
*/


class I2CE_Swiss_BINARY_FILE_meta extends I2CE_Swiss {



    protected function getTemplate() {
        return 'swiss_binary_file_meta.html';
    }
    

    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(),'div',$content_node)) instanceof DOMNode
            ){
	    I2CE::raiseError("Could not load template");
            return false;
        }
        if (!$this->displayMeta($mainNode,$transient_options,$action)) {
            return false;
        }
        return true;
    }



    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        if (array_key_exists('max_size_kb',$vals)
            && is_scalar($v = strval($vals['max_size_kb']))
            && (ctype_digit($v) || $v ==='')
            ){
            $this->setField('max_size_kb',$v);
        }
	return parent::processValues($vals);
    }


    protected function displayMeta($mainNode,$transient_options,$action) {
        $this->template->setDisplayDataImmediate('max_size_kb',$this->getField('max_size_kb'),$mainNode);
	$this->renameInputs(array('max_size_kb'),$mainNode);
        return true;
    }







}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
