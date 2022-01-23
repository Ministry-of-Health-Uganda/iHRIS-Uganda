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
* @subpackage pages
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_PageArgs
* 
* @access public
*/


class I2CE_Swiss_FormClass_Meta extends I2CE_Swiss {


    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        if (array_key_exists('unique',$vals)
            && is_scalar($un = $vals['unique'])
            ) {
            $this->setField('unique',$un?1:0);
        }
        return parent::processValues($vals);
    }

    protected function getTemplate() {
        return 'swiss_form_class_meta.html';
    }


    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(),'div',$content_node)) instanceof DOMNode) {
            return false;
        }
        return $this->displayMeta($mainNode,$transient_options,$action);
    }


    public function displayMeta($mainNode,$transient_options,$action) {
        $this->template->selectOptionsImmediate('unique',array($this->getField('unique')?1:0),$mainNode);
        $this->renameInputs(array('unique'),$mainNode);
        //Display 
        return true;
    }
  }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
