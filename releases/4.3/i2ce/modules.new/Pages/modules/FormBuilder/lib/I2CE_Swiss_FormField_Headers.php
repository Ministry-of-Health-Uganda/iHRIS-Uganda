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
* @subpackage formbuilder
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_FormField_Headers
* 
* @access public
*/


class I2CE_Swiss_FormField_Headers extends I2CE_Swiss{
    
    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {    
        I2CE::raiseError(print_r($vals,true));
        if (! ( parent::processValues($vals))) {
            return false;
        }
        if (array_key_exists('val',$vals)
            && is_array($vals['val'])) {
            foreach ($vals['val'] as $key=>$val) {
                if (!is_scalar($val)
                    ||!I2CE_MagicDataNode::checkKey($key)
                    ){
                    continue;
                }
                $this->setTranslatableField($key,$val);
            }
        }
        if (array_key_exists('do_new',$vals)
            && array_key_exists('new_key',$vals)
            && array_key_exists('new_val',$vals)
            && $vals['do_new']
            && is_scalar($key = $vals['new_key'])
            && is_scalar($val = $vals['new_val'])
            && I2CE_MagicDataNode::checkKey($key)
            && !in_array($key,$this->storage->getKeys())
            ){
            $this->setTranslatableField($key,$val);
        }
        if (array_key_exists('delete',$vals)
            && is_array($vals['delete'])) {
            foreach ($vals['delete'] as $key=>$selected) {
                if (!$selected 
                    || !is_scalar($val)
                    || ! I2CE_MagicDataNode::checkKey($key)
                    || ! ($mdNode = $this->storage->traverse($key,false,false)) instanceof I2CE_MagicDataNode
                    ) {
                    continue;
                }
                $mdNode->erase();
            }
        }
        
        return true;
    }


    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_formfield_headers.html','div',$content_node)) instanceof DOMNode
            || ! ($headersNode= $this->template->getElementByName('headers_body',0,$mainNode)) instanceof DOMNode
            ) {
            I2CE::raiseError("Could not load headers template");
            return false;
        }
        $headers = $this->storage->getAsArray();
        $inputs= array('new_key','new_val','do_new');
        $delete = 'Delete Header';
        I2CE::getConfig()->setIfIsSet($delete,"/modules/form-builder/messages/delete_header");
        foreach ($headers as $key=>$val) {
            if (!is_scalar($val) && ! $val === null) {
                continue;
            }
            $headersNode->appendChild($trNode = $this->template->createElement('tr'));
            $trNode->appendChild($keyNode = $this->template->createElement('td'));
            $trNode->appendChild($valNode = $this->template->createElement('td'));
            $keyNode->appendChild($labelNode = $this->template->createElement('h3'));
            $labelNode->appendChild($this->template->createTextNode($key));
            $input = 'val['. $key .']';
            $inputs[] = $input;
            $valNode->appendChild($this->template->createElement('input',array('value'=>$val,'name'=>$input)));
            $valNode->appendChild($this->template->createElement('br'));
            $input = 'delete['. $key .']';
            $inputs[] = $input;
            $keyNode->appendChild($this->template->createElement('input',array('type'=>'checkbox','value'=>1,'name'=>$input)));
            $keyNode->appendChild($this->template->createTextNode($delete));
        }
        if (($newNode = $this->template->getElementByName('new_key',0,$mainNode)) instanceof DOMNode) {
            $this->template->setClassValue($newNode,'validate_data',array('notinlist'=>array_keys($headers)), '%');
        }

        $this->renameInputs($inputs,$mainNode);
        return true;
        
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
