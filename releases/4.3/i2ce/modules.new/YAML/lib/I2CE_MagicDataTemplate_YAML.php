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
*  I2CE_MagicDataTemplate_YAML
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_MagicDataTemplate_YAML extends I2CE_MagicDataTemplate{


    
    /**
     *  Load  an array loaded from a YAML config file to the DOM
     * Example of YAML config  portion is in sample.yaml
     * 
     *
     * @param array $data.   An array containing configuration data from a YAML config file
     * @returns boolean   True on success
     */
    public function loadFromYAMLArray($data) {
//        echo "<pre>"; var_dump($data); echo "</pre>"; die();
        if (!is_array($data)) {
            I2CE::raiseError("Did not receive data array");
            return false;
        }
        if (!array_key_exists('metadata',$data)) {
            I2CE::raiseError("Did not find 'metadata' in  data array");
            return false;
        }
        if (!array_key_exists('module',$data)) {
            I2CE::raiseError("Did not find 'module' in  data array");
            return;
        }
        $I2CENode = $this->query('/I2CEConfiguration');
        if ($I2CENode->length != 1) {
            I2CE::raiseError("Did not find I2CEConfiguration node");
            return false;
        }
        $I2CENode = $I2CENode->item(0);
        $I2CENode->setAttribute('name',$data['module']);
        if (!$this->convertMetaDataArray($data['metadata'])) {
            I2CE::raiseError("Could not convert metadata in  data array");
            return false;
        }
        $status = $this->getDefaultStatus();
        if (array_key_exists('configdata',$data) && !$this->convertConfigDataArray($data['configdata'], $data['module'],$I2CENode,$status)) {
            I2CE::raiseError("Could not convert configdata in  data array");
            return false;
        }
        return true;
    }


    /** 
     * an array to conver attribbutes for yaml keys in a config data to DOMElement attributes 
     * @var protected static $attributeConversion
     */
    protected static $attributeConversion = array('__path'=>'path', '__config'=>'config');
    /** 
     * an array to convert attribbutes for yaml keys in a config data to optional subnode of a DOMNode
     * @var protected static $optionalNodes
     */
    protected static $optionalNodes = array('__displayName'=>'displayName','__description'=>'description');
    /**
     * Converts configurationGroup data in an array to its DOM representation.
     * @param array $data.  The data for the current node
     * @param string $name.  The name for the current node.
     * @param DOMNode $node. The parent node we wish to add onto node
     * @param array $status.  The status of the parent node.  
     * @return booolean true on success
     */ 
    protected function convertConfigDataArray($data,$name,$node, $status ) {
        $options = array('name'=>$name);
        if (!array_key_exists('__status',$data)) {
            $data['__status'] = array();
        }
        $status = array_merge($status,$data['__status']);
        unset($data['__status']);
        $values = null;
        if (array_key_exists('__values',$data)) { 
            $values = $data['__values'];
            unset($data['__values']);            
            $options['values'] = 'many';  
            if (!array_key_exists('__type',$data)) {
                $all_int = true;
                $keys = array_keys($values);
                foreach ($keys as $key) {
                    if (!is_int($key)) {
                        $all_int = false;
                        break;
                    }
                }
                if (!$all_int){
                    //we have an array not indexed only by integers (e.g. a map) 
                    //set the type to be delimited if it was not explicitly set
                    $data['__type'] == 'delimited';
                }
            }
        } else if (array_key_exists('__value',$data)) { 
            $values = array($data['__values']);
            unset($data['__value']);            
        }
        if (!is_null($values)) {
            $group = false;
            $tagName = 'configuration';            
            if (array_key_exists('__type',$data)) {
                //we don't handle this via self::$attrirbuteConversion b/c it does not apply to a configurationGroup
                $type = $data['__type'];
                $options['type'] = $type;
                unset($data['__type']);
            } else {
                $type ='string';
            }
            $method = "processYAML_values_$type";
            if (!$this->_hasMethod($method)) {
                I2CE::raiseError("Do not know how to handle data $type");
                return false;
            }
            $t_values = $this->$method($values,$status);

            if (!is_array($t_values)) {
                I2CE::raiseError("Unable to process values of type $type:\n" . print_r($values,true));
                return false;
            }
            $values = $t_values;
        } else{
            $group = true;
            $tagName = 'configurationGroup';
        }
        //we have no values set so we are in a configuration group
        foreach (self::$attributeConversion as $yaml_key=>$dom_attr) {
            if (!array_key_exists($yaml_key,$data)) {
                continue;
            }
            $options[$dom_attr] = $data[$yaml_key];
            unset($data[$yaml_key]);
        }
        $configNode = $this->createElement($tagName,$options);
        $node->appendChild($configNode);
        foreach ($status as $key=>$val) {
            $method = "processYAML_status_$key";
            if (!$this->_hasMethod($method)) {
                $method = "processYAML_status";
            }
            $status_text = $this->$method($key,$val);
            if (!is_string($status_text)) {
                I2CE::raiseError("Unable to process YAML status values at $key");
                return false;
            }
            $configNode->appendChild( $this->createElement('status',array(),$status_text));
        }
        foreach (self::$optionalNodes as $yaml_key=>$tag) {
            if (!array_key_exists($yaml_key,$data)) {
                continue;
            }
            $configNode->appendChild($this->createElement($tag,array(),$data[$yaml_key]));
            unset($data[$yaml_key]);
        }
        if ($group) {
            //we are a configurationGroup... see if there are any remaining keys in $data to make subnodes for
            foreach ($data as $key=>$t_data) {
                if (!I2CE_MagicDataNode::checkKey($key)) {
                    I2CE::raiseError("Invalid key $key");
                    return false;
                }
                if (!$this->convertConfigDataArray($t_data,$key,$configNode,$status)) {
                    return false;
                }
            }
        } else {
            foreach ($values as $val) {
                $configNode->appendChild($this->createElement('value',array(),$val));
            }
        }
        return true;
    }


    /**
     * Processor for a YAML status key,value pair where $key='list'
     * @param string $key
     * @param mixed $val
     * $returns mixed.  string on success, the text content of the <status> node that should be created.  false on failure
     */
    protected function processYAML_status_list($key,$val) {
        if (!is_array($val)) {
            I2CE::raiseError("Invalid status variable set at list -- expecting an array");
            return false;
        }
        $t_vals = array();
        foreach ($val as $k=>$v) {
            if (!is_int($k)) {
                $t_vals[] = "$k=>$v";
            } else {
                $t_vals[] = ''. $v;
            }
        }
        return "list:" . implode(',',$t_vals);
    }

    /**
     * Default processor for a YAML status key,value pair
     * @param string $key
     * @param mixed $val
     * $returns mixed.  string on success, the text content of the <status> node that should be created.  false on failure
     */
    protected function processYAML_status($key,$val) {
        if (is_bool($val)) {
            if ($val) {
                $val = 'true';
            } else {
                $val = 'false';
            }
        }
        if (!is_scalar($val)) {
            I2CE::raiseError("For status key $key, do not know how to deal with value " . print_r($val,true));
            return false;
        }
        return    "$key:$val";
    }


    /**
     * Process YAML config data where __type ='string' or is not set
     * @param mixed $values  The configdata for the key '__values'
     * @param array $status.  The status of the configuration node
     * @return mixed array on success containing each value of which will be the content of a <value> node, or false on failure
     */
    protected function processYAML_values_string($values,$status) {
        if (!is_array($values)) {
            $values = array($values);
        }
        foreach ($values as $key=>$val) {
            if (is_scalar($val)) {
                if (is_bool($val)) {
                    if ($val) {
                        $values[$key] = 'true';
                    } else {
                        $values[$key] = 'false';
                    }
                } else {
                    $values[$key] = '' . $val; //force things to be a string
                }
            } else {
                I2CE::raiseError("Invalid string value " . print_r($val,true));
                return false;
            }
        }
        return $values;
    }


    /**
     * Process YAML config data where __type ='delimited'
     * @param mixed $values  The configdata for the key '__values'
     * @param array $status.  The status of the configuration node
     * @return mixed array on success containing each value of which will be the content of a <value> node, or false on failure
     */
    protected function processYAML_values_delimited($values,$status) {
        if (!is_array($values)) {
            I2CE::raiseError("Invalid data for delimited value" . print_r($values,true));
            return false;
        }
        $t_values = array();
        foreach ($values as $key=>$val) {
            $t_values[] = "$key:$val";
        }
        return $t_values;
    }


    /**
     * Process YAML config data where __type ='list'
     * @param mixed $values  The configdata for the key '__values'
     * @param array $status.  The status of the configuration node
     * @return mixed array on success containing each value of which will be the content of a <value> node, or false on failure
     */
    protected function processYAML_values_list($values,$status) {
        if (!is_array($values)) {
            $values = array($values);
        }
        if (!array_key_exists('list',$status)||!is_array($status['list']))  {
            I2CE::raiseError("List type being used but no list set in status");
            return false;
        }
        foreach ($values as $val) {
            if (!in_array($val,$status['list'])) {
                I2CE::raiseError("Value $val is not in list  " . implode(',',$status['list']));
                return false;
            }
        }
        return $values;
    }

    /**
     * Process YAML config data where __type ='boolean'
     * @param mixed $values  The configdata for the key '__values'
     * @param array $status.  The status of the configuration node
     * @return mixed array on success containing each value of which will be the content of a <value> node, or false on failure
     */
    protected function processYAML_values_boolean($values,$status) {
        if (!is_array($values)) {
            $values = array($values);
        }
        foreach ($values as $key=>$value) {
            if (is_bool($value)) {
                if ($value) {
                    $values[$key] = 'true';
                } else {
                    $values[$key] = 'false';
                }
            } else if (is_string($value)) {
                $value = strtolower($value);
                if ($value == 'true'|| $value == 't') {
                    $values[$key] = 'true';
                } else if ($value=='false' || $value == 'f') {
                    $values[$key] = 'false';
                } else {
                    if ($status['required']) {
                        I2CE::raiseError("(1)Invalid boolean value $value");
                        return false;
                    } else {
                        unset($values[$key]);
                    }
                }          
            } else if (is_int($value)) {
                if ($value == 0) {
                    $values[$key] = 'false';
                } else if ($value == 1) {
                    $values[$key] = 'true';
                } else  {
                    if ($status['required']) {
                        I2CE::raiseError("(2)Invalid boolean value $value");
                        return false;
                    } else {
                        unset($values[$key]);
                    }
                }
            } else {
                I2CE::raiseError("Invalid boolean value " . print_r($value,true));
            }
        }
        return $values;
    }
    


    /**
     * Converts the a metadata array to the metadata DOM node.
     * @param array $data for netadata
     *@returns boolean.  true on sucess
     */
    protected function convertMetaDataArray($data) {
        $metaNode = $this->query('/I2CEConfiguration/metadata');
        if ($metaNode->length != 1) {
            I2CE::raiseError("Did not find I2CEConfiguration/metadata node");
            return false;
        }
        $metaNode = $metaNode->item(0);
        $metadata = array('displayName'=>true,'className'=>false,'category'=>false,'description'=>false,'creator'=>false,'email'=>false,'link'=>false,'version'=>true);
        foreach ($metadata as $key=>$required) {
            if (!is_scalar($data[$key])) {
                if ($required) {
                    I2CE::raiseError("Required metadata $key is not scalar valued:" . print_r($data,true));
                } else {
                    continue;
                }
            }
            $data[$key] .= ''; //convert it to a string.
            $data[$key] = trim($data[$key]);
            if ($required && ( !array_key_exists($key,$data) ||  !$data[$key])) {
                I2CE::raiseError("Required metadata $key is not valued");
                return false;
            }
            $metaNode->appendChild($this->createElement($key,array(),$data[$key]));
        }        
        if (array_key_exists('enable',$data)) {
            if (!is_array($data['enable'])) {
                $data['enable'] = array($data['enable']);
            }
            foreach ($data['enable'] as $enable) {
                $metaNode->appendChild($this->createElement('enable',array('name'=>$enable)));
            }
        }
        $metadata = array('requirements','conflicts');
        $valid_operators = array('atleast'=>'atLeast','atmost'=>'atMost','lessthan'=>'lessThan','greaterthan'=>'greaterThan');
        foreach ($metadata as $key) {
            if (!array_key_exists($key,$data)) {
                continue;
            }
            if (!is_array($data[$key])) {
                $data[$key] = array($data[$key]);
            }
            if (count($data[$key]) == 0) {
                continue;
            }
            foreach ($data[$key] as $module=>$version_data) {
                if (!is_array($version_data) || count($version_data) == 0) {
                    continue;
                }
                $node = $this->createElement($key, array('name'=>$module));
                $metaNode->appendChild($node);
                foreach ($version_data as $operator=>$version) {
                    $operator = strtolower($operator);
                    if (!array_key_exists($operator,$valid_operators)) {
                        continue;
                    }
                    $versNode = $this->createElement($valid_operators[$operator],array('version'=>$version));
                    $node->appendChild($versNode);
                }
            }
        }
        if (array_key_exists('paths',$data)) {
            if (!is_array($data['paths'])) {
                $data['paths'] = array($data['paths']);
            }
            foreach ($data['paths'] as $class=>$paths) {
                $node = $this->createElement('path',array('name'=>$class));
                if (!is_array($paths)) {
                    $paths = array($paths);
                }
                $metaNode->appendChild($node);
                foreach ($paths as $path) {
                    $node->appendChild($this->createElement('value',array(),$path));
                }
            }
        }
        if (array_key_exists('priority',$data) && is_numeric($data['priority'])) {
            $metaNode->appendChild($this->createElement('priority',array(),$data['priority']));
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
