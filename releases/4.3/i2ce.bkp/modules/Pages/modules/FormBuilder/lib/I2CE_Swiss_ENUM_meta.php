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


class I2CE_Swiss_ENUM_meta extends I2CE_Swiss {



    protected function getTemplate() {
        return 'swiss_enum_meta.html';
    }
    

    protected function getChildType($child) {
        if ($child =='method') {
            return 'ENUM_method';
        } else {
            return parent::getChildType($child);
        }
    }

    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(),'div',$content_node)) instanceof DOMNode
            ){
	    I2CE::raiseError("Could not load template");
            return false;
        }
        $inputs = array('hook');
        $this->template->setDisplayDataImmediate('hook',$this->getField('hook'),$mainNode);

        if ( ($filesNode = $this->template->getElementByName('data',0,$mainNode)) instanceof DOMNode) {
            $data_values = $this->storage->getAsArray('data');
            if (!is_array($data_values) || count($data_values) == 0) {
                $data_values = array(0=>''); //make sure there is room to put at least one file
            } else {
                ksort($data_values);
                $data_values[] = '';
            }
            $first = true;
            foreach ($data_values as $k=>$v) {
                if (!$first) {
                    $filesNode->appendChild($this->template->createElement('br',array()));
                } else {
                    $first = false;
                }
                $input = 'data[' . $k  .']';
                $inputs[] = $input;
                //$filesNode->appendChild($this->template->createTextNode($k . ':'));
                $filesNode->appendChild($this->template->createElement('input',array('name'=>$input,'value'=>$v)));
            }
        }

        $this->renameInputs($inputs,$mainNode);

        if ( ($methodChild = $this->getChild('method',true)) instanceof I2CE_Swiss
             && ( $methodNode = $this->template->getElementById('method',$mainNode)) instanceof DOMNode
            ) {
            $methodChild->addAjaxLink('method_link','method_container', 'method_ajax' ,$methodNode,$action, $transient_options);
        }
        return true;
    }


    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        if (array_key_exists('hook',$vals)
            && is_scalar($h = $vals['hook'])
            ) {
            $this->setField('hook',$h);
        }
        if (array_key_exists('data',$vals)
            && is_array($vals['data'])) {
            foreach ($vals['data'] as $key=>$val) {
                if (!I2CE_MagicDataNode::checkKey($key)
                    || !is_scalar($val)) {
                    continue;
                }
                if ($val === '') {
                    if (($md = $this->storage->traverse("data/$key",false,false)) instanceof I2CE_MagicDataNode) {
                        I2CE::raiseError("Erasing");
                        $md->erase();
                    }
                }  else  if( $this->storage->is_scalar("data/$key")
                             || $val ) {
                    //either key was already set and we are ovewriting it, or we are adding a new key with a set value
                    $this->storage->data->$key=$val;
                }
			
            }
        }
	return parent::processValues($vals);
    }






}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
