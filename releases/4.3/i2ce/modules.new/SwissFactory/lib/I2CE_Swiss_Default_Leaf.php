<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
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
*  I2CE_Swiss_Default_Leaf
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_Default_Leaf extends I2CE_Swiss_Default_Base {


    /**
     * Set the value at the magic data node associated to the path where the type of the value is a many valued string
     * @param I2CE_MagicDataNode
     * @param mixed $val
     * @returns false on failure
     */
    public function setValue_string_many($config,$val) {
        return $this->updatePairs($config,$val);
    }

    public function setValue_delimited_single($config,$val) {
        return $this->updatePairs($config,$val);
    }
    public function setValue_delimited_many($config,$val) {
        return $this->updatePairs($config,$val);
    }
    protected function updatePairs($config,$val) {
        if (!is_array($val) 
            || !array_key_exists('keys',$val) || !is_array($val['keys'])
            || !array_key_exists('vals',$val) || !is_array($val['vals'])) {
            I2CE::raiseError("Invalid data " . print_r($val,true) . " trying to bet set at:\n" . $config->getPath());
            return false;
        }
        $sucess = true;
        $locale = $this->getLocale();
        foreach ($val['keys'] as $old_key=>$new_key) {
            if (!array_key_exists($old_key,$val['vals'])) {
                $sucess = false;
                continue;
            }
            if (!$this->storage->is_scalar($old_key)) {
                $sucess = false;
                continue;
            }
            $new_val = $val['vals'][$old_key];
            $old = $this->storage->traverse($old_key,false,false);
            if ($old_key == $new_key) {
                if ($old->is_translatable()) {
                    $old->setTranslation($locale,$new_val);
                } else {
                    $old->setValue($new_val);
                }
                continue;
            } else {
                //old key is not new key
                $new = $this->storage->$new_key;
                $new = $old;
                if ($old->is_translatable()) {
                    $new->setTranslation($this->getLocale(),$new_val);
                } else {
                    $new->setValue($new_val);
                }
                $old->erase();
            }
        }
        return $sucess;
    }



    public function displayValues($contentNode, $transient_options, $action) {
        $this->template->addHeaderLink('swiss_default.css');
        if ($this->hasAttribute('type')) {
            $type = $this->getAttribute('type');
        } else {
            $type = 'string';
        }
        if ($this->hasAttribute('values')) {
            $values = $this->getAttribute('values');
        } else {
            $value = 'single';
        }                
        $valDisplayer = $action . 'Value_' .$type . '_' . $values;            
        $displayedNode = null;
        if ($this->_hasMethod($valDisplayer)) {
            $displayedNode = $this->$valDisplayer();
        }
        if (!$displayedNode instanceof DOMNode) {
            I2CE::raiseError("No valid displayed nodes for  {$type}_{$values} for " . $this->getPath());
            return false;
        }
        $this->template->setDisplayDataImmediate('description',$this->getDescription(),$displayedNode);
        $this->template->setDisplayDataImmediate('displayName',$this->getDisplayName(),$displayedNode);
        $contentNode->appendChild($displayedNode);
        if (is_string($this->error) && $this->error) {
            $this->error = $this->template->createElement('div',array('class'=>'error'),$this->error);
        } 
        if (  $this->error instanceof DOMNode) {
            $node->appendNode($error);
        }
        return true;
    }



    /**
     * @var protected mixed $errors.  
     */
    protected $error;

    /**
     * Constructor
     * @param I2CE_MagicDataNode the storage for this swiss 
     */
    public function __construct($storage, $factory,$name=null,$parent = null) {
        parent::__construct($storage,$factory,$name,$parent);
        $this->error = null;
    }




    /** Called by updateValues()
     * @param array $vals an associateive array of values.  Keys are of the form 'value_XXXXX_YYYY/ZZZZ
     * where ZZZZ is a config path (no instance), YYYY is either 'many'  or 'single', and XXXX is a data type (e.g. 'string')
     * @returns false on failure
     */
    public function processValues($vals) {
        $sucess= true;
        foreach  ($vals as $value_type => $data) {
            $validator = 'validate_' . $value_type;
            if ($this->_hasMethod($validator)) {
                $ret = $this->$validator($value);
                if ($ret instanceof DOMNode || is_string($ret)) { //it was in error
                    $this->error = $ret;
                    $has_error = true;
                    continue;
                }
            }
            $setter = 'setValue_' . $value_type;
            if ($this->_hasMethod($setter)) {
                $sucess &= $this->$setter($this->storage,$data);
            } else {
                if ($this->storage->is_translatable()) {
                    $this->storage->setTranslation($this->getLocale(),$data);
                } else {
                    $sucess &= $this->storage->setValue($data);
                }
            }
        }
        return $sucess;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
