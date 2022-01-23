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
* Class I2CE_Swis_Form_storage_options
* 
* @access public
*/


class I2CE_Swiss_Form_storage_options extends I2CE_Swiss {


    protected function getChildType($child) {
        $handlers = array();        
        if (!is_array($data = I2CE::getConfig()->getAsArray('/modules/form-builder/storage_handlers/' . $child))
            ||!array_key_exists('swiss',$data)
            ||!is_scalar($swiss  = $data['swiss'])) {
            return parent::getChildType($child);
        } 
        return $swiss;
    }    

    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_form_storage_options.html','div',$content_node)) instanceof DOMNode
            || ! ($soNode = $this->template->getElementByName('storage_options',0,$mainNode)) instanceof DOMNodE
            ){
            I2CE::raiseError("Could not load template");
            return false;
        }        
        foreach (I2CE::getConfig()->getKeys("/modules/form-builder/storage_handlers") as $handler) {
            if (! ($swissChild = $this->getChild($handler,true)) instanceof I2CE_Swiss
                || ! ($childNode = $this->template->appendFileByNode('swiss_form_storage_options_each.html','div',$soNode)) instanceof DOMNode
                ) {
                continue;
            }
            $this->template->setDisplayDataImmediate('storage_option',$handler,$childNode);
            $swissChild->addAjaxLink('so_link','container', 'so_ajax' ,$childNode,$action, $transient_options);
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
