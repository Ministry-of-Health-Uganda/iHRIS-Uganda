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
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */


/**
 * Deps
 */
require_once 'I2CE_TemplateMeister.php';
require_once 'I2CE_Util.php';

if (!class_exists('I2CE_MagicDataTemplate',false)) {

    /**
     * I2CE_MagicDataTemplate
     * @package I2CE
     * @todo Better documentation.
     */
    class I2CE_MagicDataTemplate extends I2CE_TemplateMeister {

 
        /**
         * Get the text content of a node  specified by its tag name
         * @param string $name the name of the node we wish to find the text content for
         * @param DOMNode $node The node we wish to search under.  Defaults to null meaning we search from the top node.
         * @param int $which Which result we want.  Defaults to zero.  
         * @returns mixed string on sucess, null on failure
         */
        public function getTextContent($name,$node = null, $which = 0) {
            if ($node instanceof DOMNode) {
                $results = $this->query("./$name",$node);
            } else {
                $results = $this->query("/$name");
            }
            if (($results->length == 0) || ($results->length < $which  )) {
                return null;
            }
            return $results->item($which)->textContent;
        }


        /**
         * Get the value in the <value> tag of a subnode specfiied by the  name attribute
         * @param string $nodeName
         * @param DOMNode $node The node we wish to search under.  Defaults to null meaning we search from the top node.
         * @param int $which Which result we want.  Defaults to zero
         * @returns mixed.  Null on failure, a string  (the content) on sucess
         */
        public function getConfigurationTextContent($nodeName,$node = null, $which = 0) {
            if ($node instanceof DOMNode) {
                $subNodes = $this->query("./configuration[@name='$nodeName']/value",$node);
            } else {
                $subNodes = $this->query("/configuration[@name='$nodeName']/value");
            }
            if (($subNodes->length == 0) || ($subNodes->length < $which  )) {
                return null;
            }
            return $subNodes->item($which)->textContent;
        }

        /** 
         * I2CE_Template constructor method.
         * 
         * This constructor sets up the basic variables for all I2CE_Template objects.
         * 
         */
        public function __construct() {
            parent::__construct();
            $this->setWorkingDir(realpath(dirname(__FILE__)));
            $this->merges = array();
        }



        /**
         * Get the default status variables.
         */
        public function getDefaultStatus() {
            return array(
                'merge'=>false,
                'mergerecursive'=>false,
                'uniquemerge'=>false,
                'visible'=>true,
                'advanced'=>false,
                'permission'=>'',
                'overwrite'=>false,
                'required'=>true,
                'showIndex'=>true
                );
        }

        public function validate() {
            return true;
        }


        /**
         * Assumes that the template is valid.
         * Set the status variables according to this node
         * @param DOMNode $configNode.  A configuration or conigurationGroup node 
         */
        public function processStatus($configNode,$currentStatus = null, $vers = '0') {
            if (is_array($currentStatus)) {
                $status = $currentStatus;
            } else {
                $status = array();
            }
            $set_overwrite = false;
            if ($configNode->hasAttribute('locale')) {
                $status['locale'] = $configNode->getAttribute('locale');
            }
            if ($configNode->hasAttribute('binary')) {
                $status['binary'] = $configNode->getAttribute('binary');
            }
            if ($configNode->hasAttribute('encoding')) {
                $status['encoding'] = $configNode->getAttribute('encoding');
            }
            $statusNodes = $this->query('./status | ./version',$configNode);
            for  ($i=0; $i < $statusNodes->length; $i++) {                
                $statusNode = $statusNodes->item($i);
                if ($statusNode->tagName == 'version') {
                    $status['version'] = $statusNode->textContent;
                    continue;
                } 
                if (!preg_match('/^\s*(.*?)\s*:\s*(.*)\s*$/',$statusNode->textContent,$matches)) {
                    $this->raiseError("Skipping status (" . $statusNode->textContent .") unrecongized format");
                    continue;
                }
                $key = strtolower($matches[1]);
                if ( strlen($key) == 0) {
                    $this->raiseError("Skipping status (" . $statusNode->textContent .") unrecongized format for key");
                    continue;
                }
                switch($key) {
                case 'locale':
                case 'version':
                case 'permission':
                    $status[$key] = $matches[2];
                    break;
                case 'list':
                    $vals = preg_split('/,/',$matches[2],-1,PREG_SPLIT_NO_EMPTY);
                    foreach ($vals as $v) {
                        if (preg_match('/^(.*)=>(.*)$/',$v,$matches)) {
                            $index = trim($matches[1]);
                            $value = trim($matches[2]);
                            $status[$key][$index] = trim($value);
                        } else {
                            $status[$key][] = trim($v);
                        }
                    }
                    break;                
                case 'validate':
                case 'key_validate':
                    $status[$key] = preg_split('/,/',$matches[2],-1,PREG_SPLIT_NO_EMPTY);
                    foreach ($status[$key] as $j=>$v) {
                        $status[$key][$j] = trim($v);
                    }
                    break;
                case 'require': //let us take care of a common typo.
                    $val = I2CE_Validate::convertToBoolean(strtolower($matches[2]),true);
                    if (is_bool($val)) {
                        $status['required'] = $val;
                    } else {
                        $this->raiseError("Invalid boolean for $key at "  . $configNode->getAttribute('name'));
                    }
                    break;
                case 'overwrite':
                    $set_overwrite = true;
                case 'showindex':
                case 'merge':
                case 'mergerecursive':
                case 'uniquemerge':
                case 'required':
                case 'advanced':
                case 'visible':
                    $val = I2CE_Validate::convertToBoolean(strtolower($matches[2]),true);
                    if (is_bool($val)) {
                        $status[$key] = $val;
                    } else {
                        $this->raiseError("Invalid boolean for $key at "  . $configNode->getAttribute('name'));
                    }
                    break;
                default:
                    $processor = 'processStatus_' . $key;
                    if ($this->_hasMethod($processor)) {
                        $val =  $this->$processor($matches[2]);
                        if ($val !== null) {
                            $status[$key] =$val;
                        }
                    }
                }
            
            }
            if ( (!$set_overwrite)) {
                if (array_key_exists('version',$status)) {
                    if ( I2CE_Validate::checkVersion($status['version'] , '>' , $vers)) {
                        $status['overwrite'] = true;
                    }
                } else {
                    if ($vers == '0') {
                        $status['overwrite'] = true;
                    }
                }
            }
            return $status;
        }

        /**
         * Get the magic data paths whose status we are tracking.  
         * $retrurns array.  Keys are paths values are the mergetype
         */
        public function getMerges() {
            return  $this->merges;
        }

        /**
         * $var protected array $merges;
         */
        protected $merges;



        /**
         * Sets the config values from the magic data template
         * @param DOMNode $config_group --  DomNode of <configurationGroup>
         * @param I2CE_MagicData $storage
         * @param array $status
         * @param string $version .  Defaults to '0' .  The version of the currently loaded data in $storage responsible for this XML
         * @param array of string $paths -- the current path into the $storage that we are using.  Defaults to the empty array().
         * @returns boolean.  Returns true on sucess.  False if there was some data that
         * needs to be set in the administration/configuration page.
         */
        public  function setConfigValues($config_group,$storage, $status , $vers = '0', $paths = array()) {
            if (!$this->updatePaths($config_group,$paths)) {
                return false;
            }
            //deal with any erase Information
            $this->processErasers($config_group,$paths);

            $status = $this->processStatus($config_group,$status, $vers);
            //deal with any configurationGroups below us
            $config_groups = $this->query("./configurationGroup",$config_group);
            for ($i=0; $i < $config_groups->length; $i++) {
                if (!$this->setConfigValues($config_groups->item($i),$storage,$status,$vers, $paths)) {
                    return false; //if we encoutnered any errors, pass them down
                }
            }  
            //now deal with the config options 
            $configs = $this->query("./configuration",$config_group);  
            for ($i=0; $i < $configs->length; $i++) { 
                if (!$this->processValues($configs->item($i),$storage,$status, $vers, $paths)) {
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
            if (!$node instanceof DOMElement ||  !($node->hasAttribute("path")|| $node->hasAttribute("name")) ||    !is_array($paths)) {
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
                $paths = array($path);
            } else {
                $paths[] = $path;
            }
            return true;
        }
    

        /**
         * Get the path to the current node in the configuration file.
         * Used for error reporting.
         *
         * @param $node DOMElement
         *
         * @returns string The full configuration path 
         */
        protected function getConfigPath($node) {
            $path = '';            
            while ($node instanceof DOMElement && $node->hasAttribute('name')) {
                $path = $node->getAttribute('name') . '/' . $path;
                $node = $node->parentNode;
            }
            return '/' . $path;
        }
    
        protected function traversePaths($storage,$paths) {
            foreach ($paths as $path) {
                $storage = $storage->traverse($path,true,false);
                if (!$storage instanceof I2CE_MagicDataNode) {
                    $this->raiseError("Internal error");
                    return false;
                }                                        
            }
            return $storage;
        }

        /**
         * Process values for a config node
         * @param DOMNode $configNode
         * @param I2CE_MagicDataNode $data     
         * @param array $status.  If null, it defaults to the array set by getDefaultStatus().  The current status (of parent node) 
         * @param string $version .  Defaults to '0' .  The version of the currently loaded data in $storage responsible for this XML
         * @param array of string $paths -- the current path into the $storage that we are using.  Defaults to the empty array().
         * @returns boolean. true on sucess
         */
        public function processValues($configNode, $storage, $status=null, $vers, $paths) {
            if (!$configNode instanceof DOMNode) {
                $this->raiseError("Did not receive configuration node");
                return false;
            }
            //deal with any erase Information
            if (!$this->updatePaths($configNode,$paths)) {
                return false;
            }
            $this->processErasers($configNode, $paths);
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
            if( $valueType == 'delimited' ) {
                // Delimited types really only make sense as many values
                $valueValues = 'many';
            } elseif($configNode->hasAttribute('values')) {
                $valueValues = strtolower(trim($configNode->getAttribute("values")));
            } else {
                $valueValues = 'single';
            }                        
            $uniquemerge = null;
            if (array_key_exists('uniquemerge',$status)) {
                $uniquemerge = $status['uniquemerge'];
                unset($status['uniquemerge']);
            }
            $valStatus = $this->processStatus($configNode,$status, $vers);

            if (array_key_exists('uniquemerge',$valStatus)) {
                //the uniquemerge status has been set on explicitly this node.
                //do nothing 
            } else {
                //the unique merge status has not been set on this node.
                //if this node is a values='many' values='string' make it a default merge
                if ($valueValues == 'many'  && $valueType == 'string') {
                    $valStatus['uniquemerge'] = true;
                } else {
                    if ($uniquemerge !== null) {
                        $valStatus['uniquemerge'] = $uniquemerge;
                    }
                }
            }

            
            if (!$valStatus['overwrite'] ) {
                return true;
            }



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
            $encoding  =  null;
            if ($valueValues == 'single') {
                if ($valueList->item(0) instanceof DOMElement)  { //item 0 should exist by the check/return above
                    $vals =  $this->$processor(trim($valueList->item(0)->textContent),$valStatus);                    
                }
            } else {
                $vals = array();
                for ($k=0; $k < $valueList->length; $k++) {
                    if ($valueList->item($k) instanceof DOMElement) {
                        $vals[$k] = trim($valueList->item($k)->textContent);
                    }
                }
                $vals =  $this->$processor($vals,$valStatus);
                if (!is_array($vals)) {
                    $this->raiseError("Expected array to be returned from $processor() while evaluating " . $this->getConfigPath($configNode));
                    return false;
                }
            }
            if ($valStatus['required'] === true && 
                (($valueValues == 'single' && $vals === null) ||
                 ($valueValues == 'many' && count($vals) == 0)))        {
                $this->raiseError(
                    "Required value is not set at  " . $this->getConfigPath($configNode) . ' in the module ' 
                    .  $this->query("/I2CEConfiguration")->item(0)->getAttribute('name'));
                return false;
            }

            $validator = 'validateValues_' . $valueType. '_' . $valueValues;
            if ($this->_hasMethod($validator)) {
                $validate = $this->$validator($vals,$valStatus);
                if($validate !== null)  {
                    $this->raiseError("Invalid data at " . $this->getConfigPath($configNode) . " + " . $validate);
                    return false;
                }
            }
            $locale = null;
            if (array_key_exists('locale',$valStatus) && $valStatus['locale']) {
                //this node is translatable.
                $locale = $valStatus['locale'];
            }            
            if ($valStatus['uniquemerge'] && ($valueValues == 'many') ) {            
                if ($storage->is_scalar($path)) {
                    $this->raiseError("Trying to set non-scalar value at scalar valued (uniquemerge):\n" . $storage->getPath() .'/' . $path);
                    return false;
                }
                $valStorage = $this->traversePaths($storage,$paths);
                if (!$valStorage instanceof I2CE_MagicDataNode) { 
                    return false;
                }
                $old_vals = $valStorage->getAsArray(null,$locale);
                $valStorage->eraseChildren($locale);                
                $vals = I2CE_Util::array_unique(array_merge($old_vals,$vals));
            } else if ($valStatus['mergerecursive'] && ($valueValues == 'many') ) {            
                if ($storage->is_scalar($path)) {
                    $this->raiseError("Trying to set non-scalar value at scalar valued (mergerecursive):\n" . $storage->getPath() .'/' . $path);
                    return false;
                }
                $valStorage = $this->traversePaths($storage,$paths);
                if (!$valStorage instanceof I2CE_MagicDataNode) { 
                    return false;
                }
                $old_vals = $valStorage->getAsArray(null,$locale);
                $valStorage->eraseChildren($locale);                
                I2CE_Util::merge_recursive($old_vals,$vals);
                $vals = $old_vals;
            } else if ($valStatus['merge'] && ($valueValues == 'many') ) {
                if ($storage->is_scalar($path)) {
                    $this->raiseError("Trying to set non-scalar value at scalar valued (merge):\n" . $storage->getPath() .'/' . $path);
                    return false;
                }
                $valStorage = $this->traversePaths($storage,$paths);
                if (!$valStorage instanceof I2CE_MagicDataNode) { 
                    return false;
                }
                $old_vals = $valStorage->getAsArray(null, $locale);
                $valStorage->eraseChildren($locale);
                $vals = array_merge($old_vals,$vals);
            } else {                
                if (is_array($vals) && $storage->is_scalar($path)) {
                    $this->raiseError("Trying to set non-scalar value at scalar valued:\n" . $storage->getPath() .'/' . $path);
                    return false;
                }
                $valStorage = $this->traversePaths($storage,$paths);
                if (!$valStorage instanceof I2CE_MagicDataNode) { 
                    return false;
                }
            }

            $merges = array('merge','mergerecursive', 'uniquemerge');
            foreach($merges as $merge) {
                if (!array_key_exists($merge,$valStatus) || !$valStatus[$merge]) {
                    continue;
                }
                $this->merges[$valStorage->getPath(false)] = $merge;
            }
            //only one merge status can be set, notice above that unqiuemerge takes presedence over mergerecursive which takes presidents over mergere

            $valStorage->setValue($vals,$locale, false);
            if (array_key_exists('binary',$valStatus)) {
                $this->setAttributeOnChildren('binary',$valStatus['binary'],$valStorage,$vals);
            }
            if (array_key_exists('encoding',$valStatus)) {
                $this->setAttributeOnChildren('encoding',$valStatus['encoding'],$valStorage,$vals);
            }
            return true;
        }
    
        protected function setAttributeOnChildren($attr,$attr_val,$storage,$children) {
            if (!$storage instanceof I2CE_MagicDataNode) {
                return;
            }
            if (is_array($children)) {
                foreach ($children as $k=>$v) {
                    $this->setAttributeOnChildren($attr,$attr_val,$storage->traverse($k,false,false),$v);
                }
            } else if (is_scalar($children)) {
                $storage->setAttribute($attr,$attr_val);
            }
        }
        
        /**
         * Valiedates the value of a configuration of type string and single valued
         * @param string $value
         * @param array $status
         * @returns mixed
         */
        protected function validateValues_string_single($value,$status) {       
            if (array_key_exists('validate',$status) && is_array($status['validate']) && in_array('nonempty',$status['validate'])) {
                if (!is_string($value)) {
                    return "Did not received expected string";
                }
                if (strlen($value) == 0) {
                    return "Expected non-empty string";
                }
            }
            return null;
        }

        
        /**
         * Process the value of a configuration of type string and single valued
         * @param string $value
         * @param array $status
         * @returns mixed
         */
        protected function processValues_string_single($value,$status) {
            return $value;
        }


        /**
         * Process the value of a configuration of type string and many valued
         * @param array  $valueList of string
         * @param array $status
         * @returns mixed
         */
        protected function processValues_string_many($valueList,$status) {
            return $valueList;
        }


        /**
         * Process the value of a configuration of type boolean and single valued
         * @param string $value
         * @param array $status
         * @returns mixed
         */
        protected function processValues_boolean_single($value,$status) {
            return I2CE_Validate::convertToBoolean($value);
        }


        /**
         * Process the value of a configuration of type list and single valued
         * @param string $value
         * @param array $status
         * @returns mixed
         */
        protected function processValues_list_single($value,$status) {
            if (!array_key_exists('list',$status) || !is_array($status['list'])) {
                $this->raiseError("List type requested but no list has been defined");
                return null;
            }        
            if (!array_key_exists($value,$status['list'])) {
                $this->raiseError("requested value $value  is not a key in the list:" . print_r($status['list'],true));
                return null;
            }
            return $value;
        }

        /**
         * Process the value of a configuration of type list and many valued
         * @param array $valueList
         * @param array $status
         * @returns mixed
         */
        protected function processValues_list_many($value,$status) {
            if (!array_key_exists('list',$status) || !is_array($status['list'])) {
                $this->raiseError("List type requested but no list has been defined");
                return null;
            }
            $ret = array();
            foreach ($valueList as $value) {
                if (!array_key_exists($value,$status['list'])) {
                    $this->raiseError("requested value $value  is not in the list");
                    continue;
                }
                $ret[] = $value;
            }
            if (count($ret) == 0) {
                $ret = null;
            }
            return $ret;
        }

        /**
         * Process the value of a configuration of type delimited and single valued
         * @param string $value
         * @param array $status
         * @returns mixed
         */
        protected function processValues_delimited_single($value,$status) {
            I2CE::raiseError( "processValues_delimited_single should never be called.  This should be fixed!" );
            $ret = null;            
            $values = explode( ':', $value, 2 );
            for ($k=0; $k < count($values); $k++) {
                $values[$k] = trim($values[$k]);
            }                
            if (count($values) == 0 || (strlen($values[0]) == 0)) {
                $this->raiseError("Skipping $value as key is zero length".
                                  "/nonexistent");
            }  if (count($values) ==1) {
                //set the key to have an empty value
                $ret = array($values[0] => '');
            } elseif (count($values) == 2) {
                //set the key to have an empty value
                $ret = array($values[0] => $values[1]);
            } 
            return $ret;
        }




        /**
         * Process the value of a configuration of type delimited and many valued
         * @param array $valueList
         * @param array $status
         * @returns mixed
         */
        protected function processValues_delimited_many($valueList,$status) {
            $ret = array();
            foreach ($valueList as $value) {
                $values = explode( ':', $value, 2 );
                for ($k=0; $k < count($values); $k++) {
                    $values[$k] = trim($values[$k]);
                }                
                if (count($values) == 0 || (strlen($values[0]) == 0)) {
                    $this->raiseError("Skipping $value as key is zero length/".
                                      "nonexistent");
                } else   if (count($values) ==1) {
                    $ret[$values[0]] = ''; //set the key to have an empty value
                } elseif (count($values) == 2) {
                    $ret[$values[0]]=$values[1];
                } 
            }
            if (count($ret) == 0) {
                $ret = null;
            }
            return $ret;
        }


        /**
         * @var protected I2CE_MagicDataNode $meta_config.  The magic data node for the meta configuration.  Gets set in the {@see getConfigMetaData()} call
         **/
        protected $meta_config;


        /**
         * Gets the metadata of this config file in storage
         * @param I2CE_MagicDataNode $storage
         * @returns boolean true on sucess
         */
        public function getConfigMetaData($storage = NULL) {
            if(NULL === $storage) {
                I2CE::raiseError("Got a NULL value for storage.", E_ERROR);
                return FALSE;
            }
            
            $xml = $this->getDoc();
            $topNode = $this->query('/I2CEConfiguration');
            if (!$topNode instanceof DOMNodeList || $topNode->length != 1) {
                I2CE::raiseError("No top level I2CEConfiguariton");
                return false;
            }
            $topNode = $topNode->item(0);
            if (!$topNode instanceof DOMElement) {
                I2CE::raiseError("invalid top level I2CEConfiguariton");
                return false;
            }
            $shortname = trim($topNode->getAttribute('name'));
            if (empty($shortname)) {
                $this->raiseError("Shortname for module is blank!" ,E_USER_WARNING);
                return FALSE;
            }
            $this->meta_config = $storage->$shortname;
            //validation ensures this is here.
            $metadata= $this->query('/I2CEConfiguration/metadata')->item(0);
            $setters = array('description','category','creator','link','email',
                             'displayName','version','priority');
            $erasers = array_merge($setters, array('classs', 'requirement',
                                                   'requirement_external',
                                                   'confict', 'conflict_external',
                                                   'enable','paths','erasers','eraseVals'));

            foreach ($erasers as $erase) {
                $this->meta_config->__unset($erase);
            }
            foreach ($setters as $key) {
                $key_node = $this->query( $key,$metadata);
                if ($key_node->length > 0) {
                    $this->meta_config->$key = 
                        trim($key_node->item(0)->textContent); 
                }
            }               
            $class = $this->query('./className',$metadata);
            if ($class->length > 0) {
                $class = trim($class->item(0)->textContent);
                $phpfunc = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*'; 
                if (!preg_match("/^$phpfunc\$/",$class)) {
                    $this->raiseError("Invalid class name $class for $shortname",
                                      E_USER_WARNING); 
                }  else {
                    $this->meta_config->class->name = $class;
                }
            }
            if (!isset($this->meta_config->priority) || !ctype_digit($this->meta_config->priority)) {
                $this->meta_config->priority = 50;
            }
            $this->processErasers($topNode);
            foreach (array('requirement','conflict') as $tag) {
                $requirements = $this->query( './' . $tag,$metadata);
                for ($i=0; $i < $requirements->length; $i++) {
                    $vals = array();
                    $requirement = $requirements->item($i);
                    $requirementName = trim($requirement->getAttribute('name'));
                    if (strlen($requirementName) == 0) {
                        $this->raiseError("Shortname for $tag module is blank!",
                                          E_USER_NOTICE);
                        continue;
                    }                               
                    $details = array();
                    for ($j =0; $j < $requirement->childNodes->length; $j++) {
                        $child = $requirement->childNodes->item($j);                        
                        if (!$child instanceof DOMElement) {
                            continue;
                        }
                        if ($child->nodeName == 'eval') {
                            if (is_string($child->textContent) && strlen(trim($child->textContent)) > 0) {
                                $vals[] = array('eval' => trim($child->textContent));
                            }
                        } else {
                            $details =array();                        
                            $details['version'] =
                                trim($child->getAttribute('version'));
                            $details['operator'] = $child->nodeName;
                            $vals[] = $details;
                        }
                    }
                    if ($requirement->hasAttribute('external')) {
                        $req_tag = $tag . '_external';
                    } else {
                        $req_tag = $tag;
                    }
                    $this->meta_config->$req_tag->$requirementName = $vals;
                }
            }
            $enables = $this->query('./enable',$metadata);
            $enableList = array();
            for ($i=0; $i < $enables->length; $i++) {
                $enableName = trim($enables->item($i)->getAttribute('name'));
                if (strlen($enableName) == 0) {
                    $this->raiseError("Shortname for requested module is blank!",
                                      E_USER_NOTICE);
                    continue;
                }
                $enableList[] = $enableName;
            }
            if (count($enableList) >0) {
                $this->meta_config->enable = $enableList;
            }

            if (! $this->updateMetaDataPaths()) {
                return false;
            }
            return true;
        }


        protected function processErasers($node,$current_path = array()) {
            if (!$this->meta_config instanceof I2CE_MagicDataNode) {
                //I2CE::raiseError("Bad meta config");
                return false;
            }
            if (  ($erasePaths = $this->query( './erase',$node)) instanceof DOMNodeList && $erasePaths->length > 0) {
                $eraseData = array();
                $eraseData = $this->meta_config->getAsArray('erasers');
                if (!is_array($eraseData)) {
                    $eraseData = array();
                }
                $new_offset = count($eraseData);
                for ($i=0; $i < $erasePaths->length; $i++) {
                    $erasePath = $erasePaths->item($i);
                    $md_path  = $current_path;
                    if (!$this->updatePaths($erasePath,$md_path)) {
                        I2CE::raiseError("No path");
                        continue;
                    }
                    $vals = array();
                    for ($j =0; $j < $erasePath->childNodes->length; $j++) {
                        $child = $erasePath->childNodes->item($j);                        
                        if (!$child instanceof DOMElement) {
                            continue;
                        }                    
                        $details =array();                        
                        $details['version'] =
                            trim($child->getAttribute('version'));
                        $details['operator'] = $child->nodeName;
                        $vals[] = $details;
                    }
                    $eraseData[] = array('path'=>implode("/",$md_path), 'requirements'=>$vals);
                }
                for ($i = $new_offset; $i < count($eraseData); $i++) {
                    $this->meta_config->erasers->$i = $eraseData[$i];
                }
            }

            if (  ($eraseValsPaths = $this->query( './eraseVals',$node)) instanceof DOMNodeList && $eraseValsPaths->length > 0) {
                $eraseValsData = array();
                $eraseValsData = $this->meta_config->getAsArray('eraseVals');
                if (!is_array($eraseValsData)) {
                    $eraseValsData = array();
                }
                $new_offset = count($eraseValsData);
                for ($i=0; $i < $eraseValsPaths->length; $i++) {
                    $eraseValsPath = $eraseValsPaths->item($i);
                    $md_path  = $current_path;
                    if (!$this->updatePaths($eraseValsPath,$md_path)) {
                        I2CE::raiseError("No path");
                        continue;
                    }

                    $reqs = array();
                    $vals = array();
                    for ($j =0; $j < $eraseValsPath->childNodes->length; $j++) {
                        $child = $eraseValsPath->childNodes->item($j);                        
                        if (!$child instanceof DOMElement) {
                            continue;
                        }           
                        switch ($child->tagName) {
                        case 'value':
                            $locale = '';
                            if ($child->hasAttribute('locale')) {
                                $locale = trim($child->getAttribute('locale'));
                            }
                            $val = trim($child->textContent);
                            if (!$val) {
                                I2CE::raiseError("No val set ");
                            }else {
                                $vals[] = array('val'=>$val ,  'locale'=>$locale);
                            }
                            break;
                        default:
                            $details =array();                        
                            $details['version'] =
                                trim($child->getAttribute('version'));
                            $details['operator'] = $child->nodeName;
                            $reqs[] = $details;
                            break;
                        }
                    }
                    $eraseValsData[] = array('path'=>implode("/",$md_path), 'requirements'=>$reqs, 'values'=>$vals);
                }
                for ($i = $new_offset; $i < count($eraseValsData); $i++) {
                    $this->meta_config->eraseVals->$i = $eraseValsData[$i];
                }
            }

            
        }

        /**
         * Update the storage object with the classfile and default classpath.
         *
         * @param $storage I2CE_MagicDataNode
         * @param $module  string containing the short module name.
         *
         * @returns boolean TRUE for success
         */
        protected function updateMetaDataPaths() {
            $paths = $this->query('/I2CEConfiguration/metadata/path');
            for ($i=0; $i < $paths->length; $i++ ) {
                $pathNode = $paths->item($i);
                $pathCategory = strtoupper(trim($pathNode->getAttribute('name')));
                $pathValues = $pathNode->getElementsByTagName('value');
                $vals = array();
                $this->meta_config->setIfIsSet($vals,"paths/$pathCategory",true);
                for ($j=0; $j < $pathValues->length; $j++) {
                    $vals[] = trim($pathValues->item($j)->textContent);
                }
                $this->meta_config->paths->$pathCategory = $vals;
            }
            if (isset($this->meta_config->class)) {
                unset($this->meta_config->class->file);
            }

            if (!isset($this->meta_config->paths->CLASSES)) {
                $this->meta_config->paths->CLASSES = array('./');
            }

            if (isset($this->meta_config->class)
                && isset($this->meta_config->class->name)
                ) {
                $temp_search = new I2CE_FileSearch();            
                $file = NULL;
                $this->meta_config->setIfIsSet($file,"file");
                $prefix = dirname($file);
                if ($this->meta_config->is_parent("paths/CLASSES")) {
                    foreach ($this->meta_config->paths->CLASSES as $path) {			
                        if(!$temp_search->addPath('CLASSES',$path,'EVEN_LOWER', TRUE,$prefix)) {
                            $this->raiseError("$prefix" . DIRECTORY_SEPARATOR . "$path doesn't exist!");
                        }
                    }
                }
                $classfile = $temp_search->search('CLASSES', $this->meta_config->class->name.'.php');
                if (!$classfile) {
                    $path = print_r($temp_search->getSearchPath('CLASSES'),true);
                    $this->raiseError("Class {$this->meta_config->class->name}"  ." cannot be found in the given class"   ." search path: $path", E_ERROR);
                    return FALSE;
                }
                $this->meta_config->class->file = realpath($classfile);
            }
            return TRUE;
        }
    }
}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
