<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
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
   * Pull in what we manufacture.
   */
require_once "I2CE_Module.php";

/**
 * Module Factory
 *
 * @package I2CE
 * @todo Better Documentation
 */
class I2CE_ModuleFactory { 
    /**
     * @var I2CE_ModuleFactory The singleton instance of this class.
     */
    static protected $instance;
    /**
     * Return the instance of this factory and create it if it doesn't exist.
     * @return I2CE_ModuleFactory
     */
    static public function instance() {
        if ( ! self::$instance instanceof I2CE_ModuleFactory ) {
            self::$instance = new I2CE_ModuleFactory();
        }
        return self::$instance;
    }

        
    /**
     * Create a new instance of a report factory.
     */
    public function __construct() {
        $this->setStorage();
        $this->loadedPaths = array();
        /* Taken out in 4.3 because it doesn't appear to be used, plus the field isn't defined elsewhere in the class.
        $this->db = I2CE::PDO();
        */
        //I2CE::pearError($this->db,"Cannot connect to DB");
        $this->classCache = array();
    }

    public function setStorage() {
        $this->config = I2CE::getConfig()->config;
    }
        
    /**
     * an array indexed by shortname of classes subclassing I2CE_Module 
     * @var protected array $classCache 
     */
    protected $classCache;

    /**
     * An array of path categories together with information if the priorities should be offeset from the postive
     * or negative from 0.  1 indicates positive, -1 indicates negative.  The more negative the quicker they
     * get searched.  If a category is not present, it defaults to -1.
     */
    protected static $path_categories = array
    ('CLASSES'=>1,
     'CSS'=>-1,
     'IMAGES'=>-1,
     'MODULES'=>1,
     'SCRIPTS'=>-1,
     'TEMPLATES'=>-1
        );

    public function resetStoredLoadedPaths() {
        $this->loadedPaths = array();
    }

    /**
     * Loads the class path by the indicated shortnames
     *
     * @param mixed $shortnames, either a string, a shortname of a
     * module, an array of shortnames, or null to indicate all enabled
     * modules
     *
     * @param mixed $category A string or an array of strings of the
     * categories to load.  Defaults to null which means to load all
     * paths.
     *
     * @param boolean $force Defaults to false.  Load the class path
     * even if the module is not enabled.
     *
     * @param I2CE_FileSearch A file search to use to store the module
     * paths.  If null(default) uses I2CE::getFileSearch()
     *
     * @param $data Defaults to null meaninging use the config data
     * stored in I2CE::getConfig().  If non-null uses the given value
     * for the config data.
     *
     * NOTE:  This needs to be cleaned up!
     */
    public function loadPaths($shortnames = NULL, $category = NULL, $force = FALSE,
                              $fileSearch = NULL, $data = NULL) {
        $store_loaded = false;
        if (! ($fileSearch  instanceof I2CE_FileSearch)) {
            $store_loaded = true;
            $fileSearch = I2CE::getFileSearch();
        }
        if (! ($data instanceof I2CE_MagicDataNode)) {
            $data = $this->config->data;
        }
        if ($category !== null && !is_array($category)) {
            $category = array($category);
        }
        $sitemodule = '';
        $this->config->setIfIsSet($sitemodule,'site/module');
        if ($shortnames === null) {
            $shortnames = $this->getEnabled();
            if ($sitemodule && !in_array($sitemodule,$shortnames)) {                
                $shortnames[] = $sitemodule;
            }
        } else {
            if(!is_array($shortnames)) {
                $shortnames = array($shortnames);
            }
            if (!$force) {
                foreach ($shortnames as $indx=>$shortname) {
                    if (!$this->isEnabled($shortname)) {
                        unset($shortnames[$indx]);
                    }
                }
            }
        }    
        foreach ($shortnames as $shortname) {
            if (!is_string($shortname) || strlen($shortname) == 0) {
                continue;
            }
            if ('I2CE' == $shortname) {
                $fileSearch->addPath('MODULES',dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR. '*',0,true);   //ands "parent directory of I2CE"/* 
            } else  if ($sitemodule == $shortname) {
                $sitedir = dirname(I2CE_FileSearch::realPath($this->config->data->$sitemodule->file));
                if (!$sitedir) {
                    I2CE::raiseError("Could not find the direcotry for the site config");
                } else {
                    $fileSearch->addPath('MODULES',$sitedir, -100,true);
                }            
            }
            if (!$data->__isset("$shortname/file")) {
                continue;
            }
            $path_prefix = dirname(I2CE_FileSearch::realPath($data->__get("$shortname/file")));
            if ((!$force) && $store_loaded  && array_key_exists($shortname, $this->loadedPaths) &&
                $this->loadedPaths[$shortname] === true) {
                continue;
            }
            if ($category === null) {
                $paths  = $data->getKeys("$shortname/paths");
            } else {
                $paths = $category;
            }
            $pathConfig = $data->traverse("$shortname/paths",false);
            if (!$pathConfig instanceof I2CE_MagicDataNode) {
                continue;
            }
            foreach ($paths as $i=>$path) {
                if (!is_string($path)) {
                    I2CE::raiseError("Bad directory for $shortname at index $i");
                    continue; //just to be safe
                }
                $dirs=$pathConfig->traverse($path,false,false);
                if  (!$dirs instanceof I2CE_MagicDataNode || !$dirs->is_parent()) {
                    continue;
                }
                $mult = -1;
                if (isset(self::$path_categories[$path])) {
                    $mult = self::$path_categories[$path];
                }
                $priority = 50;
                $data->setIfIsSet($priority,"$shortname/priority");
                $priority = ($mult) * ((int) $priority);
                $local_priority = $priority - abs($mult);
                foreach ($dirs as $dir) {
                    if (!is_string($dir)) {
                        I2CE::raiseError("Bad directory under $path for $shortname");
                        if ($dir instanceof I2CE_MagicDataNode) {
                            I2CE::raiseError("Bad directory under $path for $shortname:\n" . print_r($dir->getAsArray(),true));
                        }
                        continue; 
                    }
                    if ($path == 'MODULES') {                        
                        $fileSearch->addPath($path, $dir . DIRECTORY_SEPARATOR. '*', $priority ,true,$path_prefix);
                    } else {
                        $fileSearch->addPath($path, $dir , $priority ,true,$path_prefix);
                        $fileSearch->addPath($path, $dir.DIRECTORY_SEPARATOR.'local', 
                                             $local_priority,true,$path_prefix);
                    }
                }
            }
        }
        if ($store_loaded) {
            foreach ($shortnames as $shortname) {
                $this->loadedPaths[$shortname] = true;
            }
        }

    }

    public function removeClassPaths($shortnames=null) {
        if ($shortnames === null) {
            $shortnames = $this->getEnabled();
        } else {
            if(!is_array($shortnames)) {
                $shortnames = array($shortnames);
            }
            foreach ($shortnames as $indx=>$shortname) {
                if (!$this->isEnabled($shortname)) {
                    unset($shortnames[$indx]);
                }
            }
        }
        $data = $this->config->data;
        foreach (self::$path_categories as $pathCat=>$mult) {
            foreach ($shortnames as $shortname) {
                if ($this->loadedPaths[$shortname]===false) {
                    continue;
                }
                if (!($data->is_parent("$shortname/paths/$pathCat"))) {
                    continue;
                }
                $dirs = $data->traverse("$shortname/paths/$pathCat");
                $path_prefix = dirname(I2CE_FileSearch::realPath($data->$shortname->file));
                foreach ($dirs as $dir) {
                    I2CE::getFileSearch()->addPath($pathCat, $dir ,true,$path_prefix);
                    I2CE::getFileSearch()->addPath($pathCat, $dir.DIRECTORY_SEPARATOR.'local', true,$path_prefix);
                }
            }
        }
        foreach ($shortnames as $shortname) {
            $this->loadedPaths[$shortname] = false;
        }
                
    }

    /**
     * protected @var loadedPaths -- an array of the paths that have been loaded
     */
    protected $loadedPaths;
    /**
     * Gets the class name of the I2CE_Module object, if any, associated to this module.
     * @returns mixed.  string or null on failure.
     */
    public function getClassName($shortname) { 
        if (! (is_string($shortname) && strlen($shortname) >0)) {
            I2CE::raiseError("Trying to check class name of invalid module shortname: (" . print_r($shortname,true) . ")");
        }
        $class = null;
        $this->config->setIfIsSet($class,"data/$shortname/class/name");
        if (is_string($class)) {
            $class = trim($class);
            if (strlen($class) == 0) {
                $class = null;
            }
        }
        return $class;
    }
    /**
     * Gets the I2CE_Module object, if any, associated to this module.
     * @returns mixed.  string or null on failure.
     */
    public function getClass($shortname) {
        if (!array_key_exists($shortname, $this->classCache) || !isset($this->classCache[$shortname])) {
            $classname = $this->getClassName($shortname);
            if ((!empty($classname)) && (class_exists($classname))) {
                $this->classCache[$shortname] = new $classname();
                if (! ($this->classCache[$shortname] instanceof I2CE_Module)) {
                    I2CE::raiseError("Class associated with $shortname does not subclass I2CE_Module",E_USER_NOTICE);
                    $this->classCache[$shortname] = null;
                }
            } else {
                $this->classCache[$shortname] = null;
                //I2CE::raiseError("Could not associate a class with $shortname",E_USER_NOTICE);
            }
        }
        return $this->classCache[$shortname];

    }
    /**
     * Loads/updates the hooks associated with an enabled  module  
     * @param mixed $shortnames, either a string, a shortname of a module, an array of shortnames, or null
     * to indicate all enabled modules
     * @param boolean $force.  If true (defaults to false) then we update the hooks even if the module is not enabled
     * @param boolean $checkAccessTime  defaults to true which means that we only update the hooks if the last recorded access time (in magic data)
     * for the module's config file is less than its last modified time.  
     */
    public function updateHooks($shortnames=null,$force =false,$checkAccessTime =true) {
        if ($shortnames === null) {
            if ($force) {
                $shortnames = $this->getAvailable();
            } else {
                $shortnames = $this->getEnabled();
            }
        } else {
            if(is_scalar($shortnames)) {
                $shortnames = array($shortnames);
            }
        }
        if (!is_array($shortnames)) {
            return;
        }
        $phpfunc = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
        if ($checkAccessTime) {
            clearstatcache(); //make sure everything is nice and fresh
        }
        foreach ($shortnames as $shortname) {
            if ($checkAccessTime &&$this->isUpToDateModule($shortname)) {
                    continue;
            }
            I2CE::raiseError("Updating hooks/methods for $shortname");
            $this->removeHooks($shortname); 
            if ((!$force) &&(!$this->isEnabled($shortname))) {
                I2CE::raiseError("Continuing on $shortname b/c not enabled");
                continue;
            }
            // I2CE::raiseError("Updating hooks for $shortname", E_USER_NOTICE); 
            // we have changed the class file since we last examined
            // it.  need to update our hooks. 
            // make sure the class path is loaded
            $this->loadPaths($shortname,'CLASSES',$force);
            $class = $this->getClass($shortname);
            if (!$class instanceof I2CE_Module) {
                I2CE::raiseError("Continuing on $shortname b/c class does not subclass I2CE_Module");
                continue;
            }

            $hooks =  $class->getHooks();
            foreach ($hooks as $hook=>$data) {
                $priorities = array();
                if (! (is_string($hook) && strlen($hook) > 0)) {
                    I2CE::raiseError("getHooks() Invalid hook: $hook for $shortname");
                    continue;
                }
                if (!preg_match('/^' . $phpfunc . '$/',$hook)) {
                    I2CE::raiseError("getHooks()  Invalid hook: $hook for $shortname is not a php function");
                    continue;
                }
                if (is_string($data)) {
                    $priority= 50;
                    $this->config->setIfIsSet($priority,"data/$shortname/priority");
                    $priorities[$priority] = $data;
                } else if (is_array($data)) {
                    $priorities = $data;
                } else {
                    I2CE::raiseError("Invalid hook data for $hook : " . print_r($data,true));
                    continue; //don't know how to deal with the given data
                }
                foreach ($priorities as $p=>$m) {
                    if (!(is_string($m) && strlen($m) >0)) {
                        I2CE::raiseError("getHooks() Invalid method  " . print_r($m,true) . "  for $shortname");
                        continue;
                    }       
                    if (!preg_match("/^$phpfunc\$/",$m)) {
                        I2CE::raiseError("getHooks()  Invalid hook " . print_r($m,true)  . " for $shortname is not a php function");
                        continue;
                    }
                    if (!( (is_string($p) && strlen($p) >0 && ctype_digits($p))  || is_int($p))) {
                        I2CE::raiseError("getHooks()  Invalid priority " . print_r($p,true)  . " for $shortname");
                        continue;
                    }
                    $this->config->hooks->$hook->$p->$shortname = $m;
                }
                $this->config->hooks->$hook->ksort(SORT_NUMERIC);
            }
            foreach (array('methods'=>'getMethods','CLI_methods'=>'getCLIMethods') as $methodType=>$methodMethod) {
                $methods =$class->$methodMethod();
                foreach ($methods as $call=>$data) {
                    if (! (is_string($call) && strlen($call) > 0)) {
                        I2CE::raiseError("$methodMethod() Invalid key: $call for $shortname");
                        continue;
                    }
                    if (is_string($data)) {
                        $data = array('method'=>$data);
                    }
                    if (!(is_string($data['method']) && strlen($data['method']) >0)) {
                        I2CE::raiseError("$shortname::$methodMethod() -- invalid fuzzy method  name " . $data['method']);
                        continue;
                    }
                    if (!preg_match("/^$phpfunc\$/",$data['method'])) {
                        I2CE::raiseError("$methodMethod()  Invalid method " . $data['method'] . " for $shortname is not a php function");
                        continue;
                    }
                    if ( 
                        (
                            !isset($data['priority'])
                            )
                        || 
                        (!  ( 
                            (is_string($data['priority']) && strlen($data['priority']) >0 && ctype_digits($data['priority'] > 0))
                            || 
                            (is_int($data['priority']))
                            )
                            )
                        ) {
                        $data['priority'] = 50;
                        $this->config->setIfIsSet($data['priority'],"data/$shortname/priority");
                    }
                    $t_data = explode('->',$call);
                    if (count($t_data) != 2) {
                        I2CE::raiseError("$methodMethod() Invalid key: $call for $shortname");
                        continue;
                    }
                    list($className,$method) = $t_data;
                    if (!(is_string($className) && strlen($className) > 0)) {
                        I2CE::raiseError("$methodMethod() invalid class name $className  for $shortname");
                        continue;
                    }
                    if (!preg_match("/^$phpfunc\$/",$className)) {
                        I2CE::raiseError("$methodMethod()  Invalid class $className for $shortname is not a php class name");
                        continue;
                    }
                    if (! (is_string($method) && strlen($method) > 0)) {
                        I2CE::raiseError("$methodMethod() invalid method name $method for $shortname");
                        continue;
                    }
                    if (!preg_match("/^$phpfunc\$/",$method)) {
                        I2CE::raiseError("$methodMethod()  Invalid method $method for $shortname is not a php class name");
                        continue;
                    }
                    $this->config->$methodType->$className->$method->{$data['priority']}->$shortname = $data['method'];
                    $this->config->$methodType->$className->$method->ksort(SORT_NUMERIC);                    
                }
            }
        }
    }


    /**
     * Sets the hashes for the class file for the given module(s)
     * @param mixed $modules. String or array if string
     * @param boolean $strict.  If true (default) then all modules are expected to have a class file
     */
    public function setModuleClassHash($modules, $strict = true) {
        if (is_string($modules)) {
            $modules = array($modules);
        }
        if (!is_array($modules)) {
            $modules = array();
        }
        foreach ($modules as $shortname) {
            $classfile = null;            
            if (!$this->config->setIfIsSet($classfile,"data/$shortname/class/file")) {
                if ($strict) {
                    I2CE::raiseError("$shortname has no class file but it is expected to");
                }
                unset($this->config->data->$shortname->class->last_access);
                unset($this->config->data->$shortname->class->hash);
                continue;
            }            
            //I2CE::raiseError("Setting hash for $shortname from " . $classfile . " at " . $this->config->getPath());
            $classfile = I2CE_FileSearch::realPath($classfile);
            $this->config->data->$shortname->class->last_access= filemtime($classfile);                
            $contents = file_get_contents($classfile);
            if (!$contents) {
                $this->config->data->$shortname->class->hash = '';
            } else {
                $this->config->data->$shortname->class->hash = md5($contents);
            }
        }
    }


    /**
     * Sets the hashes for the class file for the given module(s)
     * @param mixed $modules. String or array if string
     */
    public function setModuleHash($modules) {
        if (is_string($modules)) {
            $modules = array($modules);
        }
        if (!is_array($modules)) {
            $modules = array();
        }
        foreach ($modules as $shortname) {
            $conffile = null;            
            if (!$this->config->setIfIsSet($conffile,"data/$shortname/file")) {
                I2CE::raiseError("$shortname has no config file file but it is expected to");
                unset($this->config->data->$shortname->last_access);
                unset($this->config->data->$shortname->hash);
                continue;
            }            
            //I2CE::raiseError("Setting hash for $shortname from " . $conffile . " at " . $this->config->getPath());
            $conffile = I2CE_FileSearch::realPath($conffile);
            //I2CE::raiseError("real path is $conffile");
            $this->config->data->$shortname->last_access= filemtime($conffile);                
            //I2CE::raiseError("Setting last access to " .  filemtime($conffile));                
            $contents = file_get_contents($conffile);
            if (!$contents) {
                //I2CE::raiseError("Clearing for $shortname hash");
                $this->config->data->$shortname->hash = '';
            } else {
                //I2CE::raiseError("Setting for $shortname hash to " .  md5($contents));
                $this->config->data->$shortname->hash = md5($contents);
            }
        }
    }


    /**
     * Remove hooks associated to (a) module(s)
     * @param mixed $shortnames, either a string, a shortname of a module, an array of shortnames, or null
     * to indicate all enabled modules
     */
    public function removeHooks($shortnames) {
        if(is_scalar($shortnames)) {
            $shortnames = array($shortnames);
        }
        if (!is_array($shortnames)) {
            return;
        }
        $hooks = $this->config->traverse('hooks',false,false);
        if (!$hooks instanceof I2CE_MagicDataNode) {
            return;
        }
        foreach ($hooks as $hook=>$hook_priorities) {
            if (!$hook_priorities instanceof I2CE_MagicDataNode) {
                continue;
            }
            foreach ($hook_priorities as $priority=>$modules) {
                if (!$modules instanceof I2CE_MagicDataNode) {
                    continue;
                }
                foreach ($shortnames as $shortname) {
                    if (isset($modules->$shortname)) {
                        unset($modules->$shortname);
                    }
                }
                if (count($modules) == 0) {
                    unset($hook_priorities->$priority);
                }
            }
            if (count($hook_priorities) == 0) {
                unset($hooks->$hook);
            }
        }
        foreach (array('methods','CLI_methods') as $methodType) {
            $classes = $this->config->traverse($methodType,false,false);
            if (!$classes instanceof I2CE_MagicDataNode) {
                continue;
            }
            foreach ($classes as $class=>$methods) {
                if (!$methods instanceof I2CE_MagicDataNode) {
                    continue;
                }
                foreach ($methods as $method=>$priorities) {
                    if (!$priorities instanceof I2CE_MagicDataNode) {
                        continue;
                    }
                    foreach ($priorities as $priority=>$modules) {
                        if (!$modules instanceof I2CE_MagicDataNode) {
                            continue;
                        }
                        foreach ($shortnames as $shortname) {
                            if (isset($modules->$shortname)) {
                                unset($modules->$shortname);
                            }
                        }
                        if (count($modules) == 0) {
                            unset($methods->priority);
                        }
                    }
                    if (count($priorities) == 0) {
                        unset($methods->$method);
                    }
                }
                if (count($methods) == 0) {
                    unset($classes->$class);
                }
            }
        }
    }
    /**
     * Disable a module by its shortname.  Not intended for casul use.
     * WARNING -- does not remove any modules that this module depends on!!!
     * @param string $shortname
     */
    public function disable($shortnames) {
        if (!is_array($shortnames)) {
            $shortnames = array($shortnames);
        }
        foreach($shortnames as $shortname) {
            if (!$this->isEnabled($shortname)) {
                I2CE::raiseError("Skipping already disabled module {$shortname}", E_USER_NOTICE);
                continue;
            }
            I2CE::raiseError("Disabling {$shortname}", E_USER_NOTICE);
            $module = $this->getClass($shortname);
            if ($module instanceof I2CE_Module) {
                //disable any hooks associated to this module.
                $this->removeHooks($shortname);
                //disable any pages made avaialable                             
                if (!($module->action_disable()))  {
                    I2CE::raiseError("Warning: could not disable $shortname");
                }                            
            }
            unset($this->config->status->enabled->$shortname);
            $this->removeClassPaths($shortname);
        }
        return true;

    }





    /**
     * Enable a module by its shortname.  Takes care of loading paths etc.
     * WARNING -- does not check to see if there are conflicts with enabled modules
     * @param string $shortname
     */
    public function enable($shortnames ) {        
        if (!is_array($shortnames)) {
            $shortnames = array($shortnames);
        }
        $sitemodule = '';
        $this->config->setIfIsSet($sitemodule,"site/module");
        foreach($shortnames as $shortname) {
            I2CE::raiseError("Enabling/updating {$shortname}");
            $this->loadPaths($shortname,null,true); //force to load all  path categories even though we haven't enabled this module yet
            $module = $this->getClass($shortname);
            if ($module instanceof I2CE_Module) {
                $classname = $this->getClassName($shortname);
                if (!empty($classname)) {
                    $this->config->classes->$classname = $shortname;
                }
                //load any hooks associated to this module.
                if ($this->isEnabled($shortname)) { //we are doing this on an update
                    $this->updateHooks($shortname);
                } else {
                    $this->updateHooks($shortname,true,false); //force the update of hooks even though the module isn't enabled yet and ignore the recored config file access time
                }
                if (!isset($this->config->status->initialized->$shortname)) {
                    I2CE::raiseError("Initializing $shortname");
                    //intialize the module if it never has been before
                    if (!$module->action_initialize()) {
                        I2CE::raiseError("Initialization for $shortname failed");
                        return false;
                    }
                }
                $this->config->status->initialized->$shortname = 1; //now se the module to be initialized
                //call the module's enable function
                if (!($module->action_enable()))  {
                    I2CE::raiseError("Enabling for $shortname failed");
                    return false;
                }
            }
            $this->config->status->enabled->$shortname = 1; //now set the module to be enabled
        }
        return true;
    }


    /**
     * Gets the fuzzy methods associated to the class
     * @param string $className
     * @param $check_parentclass. Defaults to true.  Check the parent classes for fuzzy methods
     * @returns array indexed by class names of  arrays of fuzzy method names
     */
    public function getMethods($className, $check_parent_class =true) {
        if (!is_string($className) || strlen($className)==0) {
            I2CE::raiseError("Trying to get fuzzy method for invalid classname");
            return array();
        }
        $methodType = 'methods';
        $methods = array();
        do {
            $methods[$className] =    $this->config->getKeys("$methodType/$className");
            $className = get_parent_class($className);
        } while (  ($check_parent_class) && ($className != 'I2CE_Fuzzy') && (!empty($className))); //I2CE_Fuzzy is superclass of I2CE_Module
        return $methods;
    }

    /**
     * Get the data for the fuzzy method
     * @param string $className
     * @param string $method
     * @returns array
     */
    public function getMethod($className,$method) {
        if (!is_string($className) || strlen($className)==0) {
            I2CE::raiseError("Trying to get fuzzy method for invalid classname");
            return array();
        }
        if (!is_subclass_of($className,'I2CE_Fuzzy')) {
            I2CE::raiseError("Trying to get invalid fuzzy methods on a non-fuzzy object");
            return array();
        }
        if (!is_string($method) || strlen($method)==0) {
            I2CE::raiseError("Trying to get invalid fuzzy method");
            return array();
        }
        $methodType = 'methods';
        if (!I2CE_MagicDataNode::checkKey($method)) {
            I2CE::raiseError("Trying to get invalid fuzzy method $method");
            return array();
        }
        while (!$this->config->pathExists("$methodType/$className/$method")) {
            $className = get_parent_class($className);
            if (empty($className)) {
                return array();
            }
        }
        $not_found = true;
        $priorities =$this->config->$methodType->$className->$method;
        if (!$priorities instanceof I2CE_MagicDataNode || count($priorities) == 0) {
            return array();
        }
        $priority_order = $priorities->getKeys();
        sort( $priority_order, SORT_NUMERIC );
        //$priorities->ksort( SORT_NUMERIC );
        //foreach ($priorities as $priority=>$shortnames) {
        foreach( $priority_order as $priority ) {
            $shortnames = $priorities->$priority;
            if (!$shortnames instanceof I2CE_MagicDataNode || count($shortnames) == 0) {
                unset($priorities->$priority);
                continue;
            }
            foreach ($shortnames as $shortname=>$implementing_method) {
                if (!is_string($implementing_method)) {
                    continue;
                }
                if ( !$this->isEnabled( $shortname ) ) {
                    continue;
                }                
                if ( !$this->getClass($shortname) instanceof I2CE_Module ) {
                    continue;
                }
                return array(
                    'shortname'=>$shortname,
                    'method'=>$implementing_method
                    );
            }
        }
        return array();
    }

    /**
     * Calls the specified  module hooks with the specifed arguements
     * @param string $hook the name of a hook
     * @param mixed $arg1,$arg2,...  The argument to pass to the methods.  Defaults to null, meaning there is no argument.
     * @returns an array containing all the return values of each of the hooked methods.
     */
    public static function callHooks() {
        $args = func_get_args();
        if (count($args) == 0
            || ! is_string($hook = array_shift($args))) {
            return;
        }
        $mod_factory = self::instance();
        $ret = array();
        $methods = $mod_factory->getHooks($hook);
        foreach ($methods as $data) {
            $callback = array(
                $mod_factory->getClass($data['module']),
                $data['method']
                );
            if (!is_callable($callback)) {
                continue;
            }
            $ret[] = call_user_func_array($callback, $args);
        }
        return $ret;
    }



    /**
     * Get the (callable) hooked methods associated to a hook.
     * @param string $hook
     * @param boolean $only_enabled. Defaults to true.  return only the hooked mehtods for enabled modules
     * @returns  array indexed by integer of arrays with keys 'module' and 'method.  The arrays are indexed
     * by decreasing priority
     */
    public function getHooks($hook, $only_enabled = true) {
        $hooks = $this->config->hooks;
        if (!$hooks instanceof I2CE_MagicDataNode) {
            return array();
        }
        if (!isset($hooks->$hook) || !$hooks->$hook instanceof I2CE_MagicDataNode) {
            return array();
        }
        $ret = array();
        $priorities = $hooks->$hook->getKeys();
        sort( $priorities, SORT_NUMERIC );
        //$hooks->$hook->ksort( SORT_NUMERIC );
        //foreach ($hooks->$hook as $priority=>$shortnames) {
        foreach ( $priorities as $priority ) {
            $shortnames = $hooks->$hook->$priority;
            if (!$shortnames instanceof I2CE_MagicDataNode) {
                continue;
            }
            foreach ($shortnames as $shortname=>$method) {
                $module = $this->getClass($shortname);
                if (!is_string($method)) { //safety .. could have an indeterminat node.
                    continue;
                }
                if (!$module instanceof I2CE_Module) {
                    I2CE::raiseError("module $module for $shortname cannot be associated with a subclass of I2CE_Module " . get_class($module));
                    continue;
                }
                if (!$module->_hasMethod($method)) { 
                    I2CE::raiseError("Method ($method) not callable in module $shortname", E_USER_NOTICE);
                    continue;
                }
                $ret[] = array('module'=>$shortname,'method'=>$method);
            }
        }
        return $ret;
    }




    /**
     * Finds newly available configuration files and stores them in the magic data.
     * @param mixed  $modules a string or array of strings:  
     * the shortnames of modules we wish to look for sub-modules.  If null (default), we check all enabled modules.
     * @return array of string the shortname of any modules we found (old and new)
     */
    public function checkForNewModules($modules = null) {
        if ($modules !== null) {
            if (!is_array($modules)) {
                $modules = array($modules);
            }
            $fileSearch  = new I2CE_FileSearch();
            $this->loadPaths($modules,array('CLASSES','MODULES'),true,$fileSearch); //add in the class path and module paths for the new modules
        } else {
            $fileSearch = I2CE::getFileSearch();
        }
        require_once('I2CE_Configurator.php'); //need to explicitly put this in here b/c the system may  not be enabled
        $configurator = new I2CE_Configurator(I2CE::getConfig());        
        return  $configurator->findAvailableConfigs($fileSearch,false,'',true);
    }


    /**
     * Checks the registered config files {@see addConfigFile() } to make sure
     * that they have been initialized.  If the modified time is more recent than the
     * last access time, then it will re-initialize the files.  It also checks all
     * loaded config files for enabled modules/components to make sure that they are 
     * up-to-date in a simmiar way.  
     * @param mixed $module.  A string or an arry of string  of the modules to check.  Othwerwise we check all enabled modules.
     * @return array with keys 'updates' and 'removals' and values arrays of string, the shortname of the relevant modules.
     */
    public  function getOutOfDateConfigFiles( $shortnames = null, $no_messages = true ) {
        clearstatcache(); //make sure everything is nice and fresh
        $c_updates = array();
        $m_updates = array();
        $removals = array();
        $i2ce_dir = false;
        if (!is_array($shortnames)) {
            if (is_string($shortnames)) {
                $shortnames = array($shortnames);
            } else {
                $shortnames = $this->getEnabled();
            }
        }
        $data = $this->config->data;
        if (in_array('I2CE',$shortnames)) {
            $i2ce_config_file = false;
            if ($data->setIfIsSet($i2ce_config_file,"I2CE/file")) {
                if ($i2ce_config_file   != ($new_i2ce_config_file = rtrim(dirname(dirname(__FILE__)),DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'I2CE_Configuration.xml')) {
                    I2CE::raiseError("I2CE Base Library Location Moved from:\n{$i2ce_config_file}\nto:\n{$new_i2ce_config_file}");
                    //we are working at another location, perhaps after upgrading from 4.0.8 to 4.0.9 but we kept the 4.0.8 directory there.
                    //let us set I2CE to be updated so that we rescan everything
                    return array('updates'=>$this->getEnabled(),'removals'=>array());
                }
            }
        }


        $i2ce_vers = null;
        $data->setIfIsSet($i2ce_vers,"I2CE/version");
        if ($i2ce_vers && I2CE_Validate::checkVersion($i2ce_vers,'<','3.1.1')) {
            I2CE::raiseError("Version of I2CE  < 3.1.1 -- adding in config hashes");
            $enabled = $this->getEnabled();
            foreach ($enabled as $shortname) {
                $config_file = null;
                $data->setIfIsSet($config_file,"$shortname/file");
                $config_file = I2CE_FileSearch::realPath($config_file);
                if ($config_file && is_readable($config_file)) {
                    $contents = file_get_contents($config_file);
                    if ($contents) {
                        I2CE::raiseError("Adding in config hash for $shortname");
                        $data->$shortname->hash = md5($contents);
                    }
                }
                $class_file = null;
                $data->setIfIsSet($class_file,"$shortname/class/file");
                $class_file = I2CE_FilesSearch::realPath($class_file);
                if ($class_file && is_readable($class_file)) {
                    $contents = file_get_contents($class_file);
                    if ($contents) {
                        I2CE::raiseError("Adding in config module hash for $shortname");
                        $data->$shortname->class->hash = md5($contents);
                    }
                }
            }
        }
        foreach ($shortnames as $shortname) {
            if (!$data->offsetExists($shortname)) {
                continue;
            }
            $file = I2CE_FileSearch::realPath($data->$shortname->file);
            if (!is_string($file) || !file_exists($file)) {                
                $removals[] = $shortname;
                continue;                
            }
            if  (!$this->isUpToDate($shortname)) {
                $c_updates[] = $shortname;
            } else if (!$this->isUpToDateModule($shortname)) {
                $m_updates[] = $shortname;
            }
        }
        if ($no_messages) {
            if (count($c_updates) > 0) {
                I2CE::raiseError("Adding the following modules because config file not up to date:\n" . implode(',',$c_updates));
            } 
            if (count($m_updates) > 0) {
                I2CE::raiseError("Adding the following modules because module class file not up to date:\n" . implode(',',$m_updates));
            }
            if (count($removals) > 0) {
                I2CE::raiseError("The following module's config files could not be found:\n" . implode(',',$removals));
            }
        }
        return array('updates'=>array_merge($c_updates,$m_updates),'removals'=>$removals);
    }
    





    /**
     * Checks to see if a module is up to date according to its module file
     * @param string $shortname
     * @returns boolean
     */
    public function isUpToDateModule($shortname) {
        $classfile = null;            
        $this->config->setIfIsSet($classfile,"data/$shortname/class/file");
        if ($classfile === null ) {          
            return true; //no class file so it is up to date
        }
        $classfile = I2CE_FileSearch::realPath($classfile);
        $last_access = null;            
        $this->config->setIfIsSet($last_access,"data/$shortname/class/last_access");
        if (!file_exists($classfile)) {
            return false;
        }
        if  (($last_access && filemtime($classfile) > $last_access)) {
            return false;
        }
        $existing_hash = '';       
        $this->config->setIfIsSet($existing_hash,"data/$shortname/class/hash");
        $contents = file_get_contents($classfile);
        if (!$contents) {
            return false;
        }
        if (!$existing_hash) { 
            return false;
        } else {
            return (md5($contents) == $existing_hash);
        }
    }


    /**
     * Checks to see if a module is up to date according to the given config file
     * @param string $shortname
     * @param string $file  -- defaults to null which means use the currently loaded file location
     * @returns boolean
     */
    public function isUpToDate($shortname,$file=null) {
        if ($file == null) {
            if (!$this->config->setIfIsSet($file,"data/$shortname/file")) {
                I2CE::raiseError($shortname . " config never loaded");
                return false; // the module has never been loaded
            }
            $r_file = I2CE_FileSearch::realPath($file);
            if (!file_exists($r_file) || !is_readable($r_file) ) {
                I2CE::raiseError($shortname . " non-existent config");
                return false; // the file does not even exist
            }
        } else {
            $r_file = I2CE_FileSearch::realPath($file);
            if (!$r_file) {
                I2CE::raiseError($shortname . " invalid config file");
                return false;//invalid file was set.  
            }
            if (!$this->config->setIfIsSet($saved_file,"data/$shortname/file")) {
                I2CE::raiseError($shortname . " config never loaded");
                return false; // the module has never been loaded
            }            
            if (I2CE_FileSearch::realPath($saved_file) != $r_file) {
                I2CE::raiseError($shortname . " loaded is not current");
                //the loaded config file is different than the one we are looking at
                return false;
            }            
        }
        @$mtime = filemtime($r_file); 
        // if (!$mtime) {
        //     //will be false for :memory:EXT which happens for a config file loaded from memory.  assume it is up to date            
        //     return true;  
        // }
        $last_access = 0;
        $this->config->setIfIsSet($last_access, "data/$shortname/last_access");
        if ($mtime > $last_access) {
            //I2CE::raiseError($shortname . " mod time $mtime > $last_access for $r_file");
            //the modification time is greater.   so its out of date.
            return false;
        }
        //the access may be up to date b/c we reprocessed the available configs.  now check the hash.
        $existing_hash = '';
        $this->config->setIfIsSet($existing_hash,"data/$shortname/hash");
        $contents = file_get_contents($r_file);
        if (!$contents) {
            I2CE::raiseError($shortname . " no contents");
            return false;
        }
        if (!$existing_hash) {
            I2CE::raiseError($shortname . " no hash");
            return false;
        }
        if (md5($contents) != $existing_hash) {
            I2CE::raiseError($shortname . " mismatch hash on $r_file " . md5($contents) . ' != ' . $existing_hash);
            return false;
        }
        return $this->checkLocalesUptoDate($shortname,$file);
    }


    /**
     * Checks to see if the locales for a given module are up to date
     * @param string $shortname  The module
     * @param string $file  -- defaults to null which means use the currently loaded file location for the config file
     * @returns booolean.  True if all is up to date.
     */
    public function checkLocalesUptoDate($shortname,$file = null) {
        if ($file == null) {
            if (!$this->config->setIfIsSet($file,"data/$shortname/file")) {
                return false; // the module has never been loaded
            }
            $r_file = I2CE_FileSearch::realPath($file);
            if (!file_exists($r_file) || !is_readable($r_file) ) {
                return false; // the friggin file does not even exist.  why are you asking me?
            }
        } else {
            $r_file = I2CE_FileSearch::realPath($file);
            if (!$r_file) {
                return false;//invalid file was set.  
            }
            if (!$this->config->setIfIsSet($saved_file,"data/$shortname/file")) {
                return false; // the module has never been loaded
            }            
            if (I2CE_FileSearch::realPath($saved_file) != $r_file) {
                //the loaded config file is different than the one we are looking at
                return false;
            }            
        }
        $locales = I2CE_Locales::getAvailableLocales();
        $dirs = array();
        if (!$this->config->setIfIsSet($dirs,"data/$shortname/paths/CONFIGS",true)) {
            return true;
        }        
        $localized = array();
        $this->config->setIfIsSet($localized, "status/localized/$shortname",true);
        $basename = basename($r_file);
        $dirname =dirname($r_file);
        //I2CE::raiseError("Checking $shortname in  " . implode(',',$dirs) . " for locales " . implode(',',$locales) );
        foreach ($dirs as $dir) {
            foreach ($locales as $locale) {
                if (I2CE_FileSearch::isAbsolut($dir)) {  //the config path in theconfig file is absolut.
                    $t_file = $dir . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $basename;                
                    $localized_file = I2CE_FileSearch::realPath($t_file);
                    $dir = I2CE_FileSearch::relativePath($dir);
                } else { //the config path in theconfig file is relative to the module file
                    $t_file = $dirname . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $basename;
                    $localized_file = I2CE_FileSearch::realPath($t_file);               
                }
                if (!$localized_file || !is_file($localized_file) || !is_readable($localized_file)) {
                    continue;
                }
                if (!array_key_exists($locale,$localized) || !is_array($localized[$locale]))  {
                    I2CE::raiseError("Localization data $locale for module $shortname has never been loaded");
                    return false; //we have never examined this one before
                }
                foreach (array('file','mtime','hash') as $key) {
                    if (!array_key_exists($key,$localized[$locale])) {
                        return false;
                    }
                }
                //we have examined this one before. 
                $l_file = I2CE_FileSearch::realPath($localized[$locale]['file']);
                if ($localized_file !== $l_file) {
                    return false;
                }
                @$mtime = filemtime($localized_file); 
                if (!$mtime || !$localized[$locale]['mtime']) {
                    return false;
                }
                if ($mtime > $localized[$locale]['mtime']) {
                    return false;
                }
                $contents = file_get_contents($localized_file);
                if (!$contents) {
                    return false;
                }
                if ($localized[$locale]['hash'] != md5($contents)) {
                    return false;
                }
            }
        }
        return true;
    }
    /**
     * Checks to see if a module is enabled 
     * @param string $shortname
     * @returns boolean
     */
    public function isEnabled($shortname) {
        if (! (is_string($shortname) && strlen($shortname) >0)) {
            I2CE::raiseError("Trying to check status of invalid module shortname: (" . print_r($shortname,true) . ")");
            return;
        }
        return $this->config->__isset("status/enabled/$shortname");
    }


    /**
     * Checks to see if a module is intialized.  Returns true either
     * 1)  there is a class associated to the module and the aciton_initialize() method has been called succesfuuly
     * 2)  there is no class associated with the module but the config data has been processed
     * @param string $shortname
     * @returns boolean
     */
    public function isInitialized($shortname) {
        if (! (is_string($shortname) && strlen($shortname) >0)) {
            I2CE::raiseError("Trying to check status of invalid module shortname: (" . print_r($shortname,true) . ")");
            return;
        }
        if (is_string($this->getClassName($shortname))) {
            return $this->config->__isset("status/initialized/$shortname");
        } else {
            $vers = null;
            $mod_vers = null;
            $this->config->setIfIsSet($vers,"status/config_processed/$shortname");
            $this->config->setIfIsSet($mod_vers,"data/$shortname/version");
            if ($vers === null || $mod_vers === null) {
                return false;
            }            
            return I2CE_Validate::checkVersion($vers , '>=' , $mod_vers);
        }
    }

    public function hasConfigData($shortname) {
        if (! (is_string($shortname) && strlen($shortname) >0)) {
            I2CE::raiseError("Trying to check status of invalid module shortname: (" . print_r($shortname,true) . ")");
            return;
        }
        return ( !(  $this->config->is_scalar("data/$shortname/noConfigData") &&
                     $this->config->__get("data/$shortname/noConfigData")
                     ));
    }

    /**
     * Gets a list of the enabled modules
     * @return array of string (the modules' shortnames)
     */
    public function getEnabled() {
        $enabled = $this->config->getKeys("status/enabled");
        if (!is_array($enabled)) {
            $enabled = array();
        }
        return $enabled;
    }

    /**
     * Gets a list of the available modules
     * @return array of string (the modules' shortnames)
     */
    public function getAvailable() {
        $avail = $this->config->getKeys("data");
        if (!is_array($avail)) {
            $avail = array();
        }
        return $avail;
    }



    /**
     * Checks to see if a module is available
     * @param string $shortname
     * @returns boolean
     */
    public function isAvailable($shortname) {
        if (! (is_string($shortname) && strlen($shortname) >0)) {
            I2CE::raiseError("Trying to check status of invalid module shortname: (" . print_r($shortname,true) . ")");
        }
        return $this->config->__isset("data/$shortname");

    }


    /**
     * Check to see if this module exists
     *
     * @param string $shortname
     * @return boolean
     * @deprecated 
     */
    public function exists($shortname) {
        return $this->isAvailable( $shortname );
    }
}

# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
