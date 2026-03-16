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
* @package I2CE
* @subpackage I2CE
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.3
* @since v4.0.3
* @filesource 
*/ 
/** 
* Class I2CE_MagicDataTemplate_Documentor
* 
* @access public
*/

require_once(dirname(__FILE__) . '/../lib/I2CE_MagicDataTemplate.php');
class I2CE_MagicDataTemplate_Documentor extends I2CE_MagicDataTemplate{
    

    protected $node_defs;
    /** 
     * I2CE_Template constructor method.
     * 
     * This constructor sets up the basic variables for all I2CE_Template objects.
     * 
     */
    public function __construct() {
        parent::__construct();
        $this->node_defs = array('/'=>array());
    }
    
    public function getNodeData() {
        return $this->node_defs;
    }

    
    /**
     * Sets the config values from the magic data template
     * @param DOMNode $config_group --  DomNode of <configurationGroup>
     * @param array $status
     * @param string $version .  Defaults to '0' .  The version of the currently loaded data in $storage responsible for this XML
     * @param array of string $paths -- the current path into the $storage that we are using.  Defaults to the empty array().
     * @returns boolean.  Returns true on sucess.  False if there was some data that
     * needs to be set in the administration/configuration page.
     */
    public  function document($config_group, $status , $vers = '0', $paths = array()) {
        if (!$this->updatePaths($config_group,$paths)) {
            return false;
        }
        $status = $this->processStatus($config_group,$status, $vers);
        $node_data =array(
            'is_scalar'=>false
            );
        if (method_exists('DOMNode','getLineNo')) { //only in vers 5.3
            $node_data['line']=$config_group->getLineNo();
        }
        if (array_key_exists('version',$status) && $status['version']) {
            $node_data['version'] = $status['version'];
        }
        $dispList = $this->query("./displayName",$config_group);
        if ($dispList->length == 1) {
            $node_data['displayName']  = trim($dispList->item(0)->textContent);
        }
        $descList = $this->query("./description",$config_group);
        if ($descList->length == 1) {
            $node_data['description']  = trim($descList->item(0)->textContent);
        }
        $this->addNodeData($paths,$node_data);
        //deal with any configurationGroups below us
        $config_groups = $this->query("./configurationGroup",$config_group);
        for ($i=0; $i < $config_groups->length; $i++) {
            if (!$this->document($config_groups->item($i),$status,$vers, $paths)) {
                return false; //if we encoutnered any errors, pass them down
            }
        }  
        //now deal with the config options 
        $configs = $this->query("./configuration",$config_group);  
        foreach ($configs as $config) {
            if (!$this->documentValues($config,$status, $vers, $paths)) {
                return false;
            }
        }
        return true;
        
    }



    /**
     * Update the list of path elements in MagicData based on
     * attributes of the element passed in:
     *   path   Contents of this attribute specify an explicit path
     *   name   If no path element is used then the contents of the
     *          name attribute are added to the path.
     *
     * @param $node DOMElement
     *
     * @param $paths array of strings
     *
     * @returns boolean TRUE if no error.
     */
    protected function updatePaths($node = NULL, &$paths = NULL) {
        if (!$node instanceof DOMElement ||
            !($node->hasAttribute("path")
              || $node->hasAttribute("name")) ||
            !is_array($paths)) {
            $this->raiseError("Internal Error", E_ERROR);
            return FALSE;
        }

        /* check to see if there is an explicit path set */
        if ($node->hasAttribute('path')) {
            $path = $node->getAttribute('path');
        } else {
            $path =$node->getAttribute('name');
        }
        if (!is_string($path) || strlen($path) === 0) {
            $this->raiseError($node->tagName . " has empty path at " . $this->getConfigPath($node), E_ERROR );
            return false;
        }
        if ($path[0] == '/') {
            $paths = explode('/',$path);
        } else {
            $paths[] = $path;
        }
        foreach ($paths as $i=>$path) {
            if (strlen($path) == 0) {
                unset($paths[$i]);
            }
        }
        return true;
    }
    

    protected function addNodeData($paths,$node_data) {
        $t_paths = $paths;
        $path = '/'.implode('/', $paths);
        array_pop($t_paths);
        while( count($t_paths) > 0){
            $t_path = '/'.implode('/', $t_paths);
            if (array_key_exists($t_path,$this->node_defs)) {
                break;
            }
            $this->node_defs[$t_path] = array();
            array_pop($t_paths);                
        }
        $this->node_defs[$path] = $node_data;
    }

    protected function appendToNodeData($paths, $key,$val) {
        $path = '/'.implode('/', $paths);
        if (!array_key_exists($key, $this->node_defs[$path])) {
            $this->node_defs[$path][$key] = '';
        }
        $this->node_defs[$path][$key] .= $val;
    }

    /**
     * Process values for a config node
     * @param DOMNode $configNode
     * @param array $status.  If null, it defaults to the array set by getDefaultStatus().  The current status (of parent node) 
     * @param string $version .  Defaults to '0' .  The version of the currently loaded data in $storage responsible for this XML
     * @param array of string $paths -- the current path into the $storage that we are using.  Defaults to the empty array().
     * @returns boolean. true on sucess
     */
    public function documentValues($configNode,  $status=null, $vers, $paths) {
        if (!$configNode instanceof DOMNode) {
            $this->raiseError("Did not receive configuration node");
            return false;
        }
        if (!$this->updatePaths($configNode,$paths)) {
            return false;
        }
        if($status === null) {
            $status = $this->getDefaultStatus();
        }
        if ($configNode->hasAttribute('path')) { //check to see if there is an explicit path set
            $path = $configNode->getAttribute('path');
            $hasPath = true;
        } else {
            $path = $configNode->getAttribute('name');
            $hasPath = false;
        }
        if (strlen($path) === 0) {
            $this->raiseError("configuration has empty path at " . $storage->getPath() );
            return false;
        }
        if($configNode->hasAttribute('type')) {
            $valueType = strtolower(trim($configNode->getAttribute("type")));
        } else {
            $valueType = 'string';
        }
        if($configNode->hasAttribute('values')) {
            $valueValues = strtolower(trim($configNode->getAttribute("values")));
        } else {
            $valueValues = 'single';
        }                        
        $valStatus = $this->processStatus($configNode,$status, $vers);
        $valueList = $this->query("./value",$configNode);
        if ($valueList->length == 0) {                
            if ($valStatus['required'] === true) {
                $this->raiseError("Required value is not set at " . $this->getConfigPath($configNode));
                return false;
            } else {
                //not required so let's return
                return true;
            }
        }

        $processor = 'processValues_'   . $valueType . '_' . $valueValues;
        $vals = null;
        if ($valueValues == 'single') {
            if ($valueList->item(0) instanceof DOMElement)  { //item 0 should exist by the check/return above
                $vals =  $this->$processor(trim($valueList->item(0)->textContent),$valStatus);
            }
        } else {
            $vals = array();
            for ($k=0; $k < $valueList->length; $k++) {
                if ($valueList->item($k) instanceof DOMElement) {
                    $vals[] = trim($valueList->item($k)->textContent);
                }
            }
            $vals =  $this->$processor($vals,$valStatus);
            if (!is_array($vals)) {
                $this->raiseError("Expected array to be returned from $processor() while evaluating " . $this->getConfigPath($configNode));
                return false;
            }
        }
        $node_data = array('is_scalar'=>true);
        if (method_exists('DOMNode','getLineNo')) { //only in vers 5.3
            $node_data['line']=$config_group->getLineNo();
        }
        if (array_key_exists('version',$status) && $status['version']) {
            $node_data['version'] = $status['version'];
        }
        if (array_key_exists('locale',$valStatus) && $valStatus['locale']) {
            //this node is translatable.
            $node_data['translatable'] = true;
        }   else {
            $node_data['translatable'] = false;
        }


        
        
        if (is_scalar($vals)) {
            $t_node_data = $node_data;
            $t_node_data['value']=$vals;
            $dispList = $this->query("./displayName",$configNode);
            if ($dispList->length == 1) {
                $t_node_data['displayName']  = trim($dispList->item(0)->textContent);
            }
            $descList = $this->query("./description",$configNode);
            if ($descList->length == 1) {
                $t_node_data['description']  = trim($descList->item(0)->textContent);
            }
            $this->addNodeData($paths,$t_node_data);
        } else {
            foreach ($vals as $i=>$value) {
                $t_node_data = $node_data;
                $t_node_data['value']=$value;
                $t_paths = $paths;
                $t_paths[] = $i;
                $dispList = $this->query("./displayName",$configNode);
                $this->addNodeData($t_paths,$t_node_data);
            }
            if ($dispList->length == 1) {
                $this->appendToNodeData($paths, 'displayName', trim($dispList->item(0)->textContent));
            }
            $descList = $this->query("./description",$configNode);
            if ($descList->length == 1) {
                $this->appendToNodeData($paths, 'description', trim($descList->item(0)->textContent));
            }
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
