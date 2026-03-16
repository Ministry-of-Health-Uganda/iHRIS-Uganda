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
*  I2CE_MagicData_Export
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/



class I2CE_MagicDataExport_Template extends I2CE_MagicDataTemplate {


    /**
     * The top level configurationGroup node
     * @var protected DOMNode $mainNode
     */
    protected $mainNode;
    /**
     * The metadata node
     * @var protected DOMNode $metaNode
     * 
     */
    protected $metaNode;


    /**
     * Set the name of the module to be used for export.  
     * @param string $module
     * @param string $path.  Defaults to null.  If set, sets the  path  for the top level configuration group
     */
    public function setModule($module,$path = null) {
        $this->setAttribute('name',$module,null,'//I2CEConfiguration');
        $this->mainNode = $this->query('/I2CEConfiguration/configurationGroup');
        if ($this->mainNode->length != 1) {
            I2CE::raiseError("No top level configurationGroup");
            $this->mainNode = null;
            return false;
        }        
        $this->mainNode = $this->mainNode->item(0);
        $this->mainNode->setAttribute('name',$module);
        if ($path !== null) {
            $this->mainNode->setAttribute('path',$path);
        }
        return true;
    }

    protected function addTextToNode($node,$nodeName,$text,$binary = false,$encoding =false) {
        $nodes =  $this->query("./$nodeName", $node);
        if (!$nodes->length ==1) {
            I2CE::raiseError("Nothing found for $qry");
            return false;
        }
        if ($binary) {
            switch ($encoding) {
            case 'base64':
                $text = base64_encode($text);
                $textNode = $this->createTextNode($text);
                $nodes->item(0)->appendChild($textNode);
                //$nodes->item(0)->appendChild($this->doc->createCDATASection($text));
                break;
            default:
                $nodes->item(0)->appendChild($this->doc->createCDATASection($text));
            }
        } else {
            $textNode = $this->createTextNode($text);
            $nodes->item(0)->appendChild($textNode);
        }
        return true;
    }






    /**
     * Create the meta data node 
     * @param array $options.  Has the following keys used to set the metadata: 'description','displayName', 'creator','email','link','version'.
     * We also have keys which  'requirement','enable','conflict','path' are themselves arrays containing the data needed to define them.  Each
     * entry of this array corresponds to a sepearte instnce of a node with the given name.  This 
     * data is as in the following examples:
     * 'requirement'=>array('I2CE'=>array('atLeast'=>4.3,'lessThan'=>2.2),'blah'=>array('greaterThan'=>4)),
     * 'paths'=>array('templates'=>array('value'=>'./templates')),
     * 'enable'=>array('brower'=>array(),'blah_module'=>array())
     */
    public function createMetaDataNode($options) {
        $this->metaNode = $this->createElement('metadata');
        $i2ceConfigNode = $this->doc->getElementsByTagName('I2CEConfiguration');
        if ( $i2ceConfigNode->length != 1 ) {
            I2CE::raiseError("Found unexpected number of I2CEConfiguration nodes: " . $i2ceConfigNode->length);
        }
        $i2ceConfigNode = $i2ceConfigNode->item(0);
        if ($i2ceConfigNode->hasChildNodes()) {
            $i2ceConfigNode->insertBefore($this->metaNode,$i2ceConfigNode->childNodes->item(0));
        } else {
            $i2ceConfigNode->appendChild($this->metaNode);
        }
        foreach( array('displayName'=>true,'className'=>false,'category'=>false,'description'=>false
                       ,'creator'=>false,'email'=>false,'link'=>false,'enable'=>false,'requirement'=>false,'conflict'=>false,
                       'path'=>false,'version'=>true) as $option=>$required) {
            if ($required && (!array_key_exists($option,$options) || !$options[$option])) {                
                I2CE::raiseError("Required option '$option' not specified");
                return false;
            }
            if (array_key_exists($option,$options) && $options[$option]) {
                if (is_array($options[$option])) {
                    foreach ($options[$option] as $name=>$vals) {
                        $node = $this->createElement($option , array('name'=>$name));
                        $this->metaNode->appendChild($node);
                        foreach ($vals as $type=>$val) {
                            $valNode=$this->createElement($type,array(),$val);
                            $node->appendChild($valNode);
                        }
                    }
                } else {
                    $this->metaNode->appendChild($this->createElement($option,array(),$options[$option]));
                }
            }
        }
        return true;
    }

    /**
     * Create an configurationGroup node by appending on to the given configurationGroup node the values
     * stored in the magic data at the specified $key.
     * @param DOMNode $configNode.  A configurationGroup node.
     * @param I2CE_MagicDataNode $config.  The data we wish to store at this node
     * @param array $pipe.  An array of path components relative to the $config. If the pipe is a non-empty array, we export only the 
     * keys specifed by the lowest member of $pipe, if it exists.  Otherwise, if the pipe is empty, we export all keys
     * @param string $key.  The key.  (Warning.  It assumes it exists in the magic data!)
     * @param string $configType.  Defaults to the empty string.  The configuration type to give the configuration node.
     * @param array $status.  An array of status options we should set for this configuration node.  Defaults to the empty array
     */
    public function createExportNodeConfigurationGroup($configNode,$config,$pipe,$key,$configType,$status) {
        $child = $this->appendFileByNode('export_magicdata_node.xml', 'configurationGroup',$configNode);                
        if (! $child instanceof DOMElement) {
            return false;
        }
        if (is_string($configType) && strlen($configType) > 0) {
            $child->setAttribute('config',$configType);
        }
        $child->setAttribute('name',$key);                
        if (!$this->addTextToNode($child,'displayName',$this->humanText($key))) { return false;}
        foreach ($status as $k=>$stat) {
            $child->appendChild($this->createElement('status' , array(), "$k:$stat"));
        }
        
        if (!$this->createExport($child,$config->$key,$pipe,$configType,$status)) {
            return false;
        }
        return true;
    }


    /**
     * Create an configuration node by appending on to the given configurationGroup node the values
     * stored in the magic data at the specified $key.
     * @param DOMNode $configNode.  A configurationGroup node.
     * @param I2CE_MagicDataNode $conig.  The data we wish to store at this node
     * @param string $key.  The key.  (Warning.  It assumes it exists in the magic data!)
     * @param string $configType.  Defaults to the empty string.  The configuration type to give the configuration node.
     * @param array $status.  An array of status options we should set for this configuration node.  Defaults to the empty array
     */
    public function createExportNodeConfiguration($configNode,$config,$key,$configType,$status) {
        $child = $this->appendFileByNode('export_magicdata_value.xml','configuration',$configNode);
        if (! $child instanceof DOMElement) {
            return false;
        }
        if (is_string($configType) && strlen($configType) > 0) {
            $child->setAttribute('config',$configType);
        }
        $child->setAttribute('name',$key);
        if (!$this->addTextToNode($child,'displayName',$this->humanText($key))) { 
            return false;
        }
        if (count($status) > 0) {
            $valueNode = $this->query('./value',$child);
            if ($valueNode->length != 1) {
                I2CE::raiseError("Could not find value node:" . $valueNode->length); 
                return false;
            }
            foreach ($status as $k=>$stat) {
                $child->insertBefore(
                    $this->createElement('status' , array(), "$k:$stat"),
                    $valueNode->item(0)
                    );            
            }
        }
        $binary = ($config->traverse($key,false,false)->hasAttribute('binary') && $config->traverse($key,false,false)->getAttribute('binary'));        
        $encoding = false;
        if ($config->traverse($key,false,false)->hasAttribute('encoding')) {
            $encoding = $config->traverse($key,false,false)->getAttribute('encoding');
        }
        if (!$this->addTextToNode($child,'value',$config->$key,$binary,$encoding)) {
            return false;
        }
        if ($binary) {
            $child->setAttribute('binary',1);
        }
        if ($encoding) {
            $child->setAttribute('encoding',$encoding);
        }
        return true;
    }

    /**
     * Create an export node by appending on to the given configurationGroup node the values
     * stored in the magic data at the specified $key.
     * @param DOMNode $configNode.  A configurationGroup node.
     * @param I2CE_MagicDataNode $config.  The data we wish to store at this node
     * @param array $pipe.  An array of path components relative to the $config. If the pipe is a non-empty array, we export only the 
     * keys specifed by the lowest member of $pipe, if it exists.  Otherwise, if the pipe is empty, we export all keys
     * @param string $key.  The key.  (Warning.  It assumes it exists in the magic data!)
     * @param string $configType.  Defaults to the empty string.  The configuration type to give the configuration node.
     * @param array $status.  An array of status options we should set for this configuration node.  Defaults to the empty array
     */
    public function createExportNode($configNode,$config,$pipe,$key,$configType,$status) {
        if ($config->is_parent($key) || $config->is_indeterminate($key)) {
            if (!$this->createExportNodeConfigurationGroup($configNode,$config,$pipe,$key,$configType,$status)) {
                return false;
            }
        }  else  if ($config->is_scalar($key) && count($pipe) == 0) {
            if (!$this->createExportNodeConfiguration($configNode,$config,$key,$configType,$status)) {
                return false;
            }
        } else {            
            I2CE::raiseError("Don't know how to deal with type at" . $config->getPath(false));
        }       
        return true;
    }
    
    /**
     * Create an export node by appending on to the given configurationGroup node the values
     * stored in the magic data.
     * @param DOMNode $configNode.  A configurationGroup node.
     * @param I2CE_MagicDataNode $conig.  The data we wish to store at this node
     * @param mixed $pipe.  A path component or an array of path components relative to the $config. If the pipe is a non-empty array, we export only the 
     * keys specifed by the lowest member of $pipe, if it exists.  Otherwise, if the pipe is empty or null, we export all keys.  Defaults to empty array
     * @param string $configType.  Defaults to the empty string.  The configuration type to give the configuration node.
     * @param array $status.  An array of status options we should set for this configuration node.  Defaults to the empty array
     */
    public function createExport($configNode, $config,$pipe=null,$configType='',$status=array()) {
        if ($pipe === null) {
            $pipe = array();
        }
        if (!is_array($pipe)) {
            $pipe = array($pipe);
        }
        if (count($pipe) == 0) {
            $keys = $config->getKeys();
        } else {
            $component = array_shift($pipe);
            if (isset($config->$component)) {
                $keys = array($component);
            } else {
                I2CE::raiseError("Pipe componenent $component not found at " . $config->getPath(false));
                $keys = array();
            }
        }
        foreach ($keys as $key) {
            if (!$this->createExportNode($configNode,$config,$pipe,$key,$configType,$status)) {
                return false;
            }
        }
        return true;
    }


    /**
     * Tries to turn a string (such as a magic data key) into  human text
     * @param $text
     * @returns string
     */
    public static function humanText($text){
        $text = preg_replace('/([a-zA-Z0-9])[-_+\\.]([a-zA-Z0-9])/','\\1 \\2',$text ); //words with punctuation
        $text = preg_replace('/([a-z])([A-Z])/','\\1 \\2',$text ); //camel case
        $text = ucwords($text);        
        return $text;
    }






}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
