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
 * pull in static classes.
 */
require_once 'I2CE_MagicDataTemplate.php';
require_once 'I2CE_ModuleFactory.php';
require_once 'I2CE_Validate.php';
require_once 'I2CE_Fuzzy.php';
require_once 'I2CE_Locales.php';
require_once 'I2CE_MagicDataStorageMem.php';

/**
 * Configurator class -- handles dependencies and such.
 * @package I2CE
 */
if (! class_exists('I2CE_Configurator',false)) {
    class  I2CE_Configurator extends I2CE_Fuzzy{

        /**
         *Get the magic data node storage for the configurator
         *@returns I2CE_MagicDataNode
         */
        public function getStorage() {
            return $this->storage;
        }
        /**
         * Hack to store the paths to each config
         * @var private array $mod_path
         */
        private $mod_path = array();

        /**
         * An associative array linking directory to top-level module
         * @var private array $top_module
         */
        private $top_module = array();

        /**
         * A list of the paths checked
         * @var private array $checked_paths
         */
        private $checked_paths = array();

        /**
         * Holds a list of found modules
         * @var private array $found_module
         */
        private $found_modules = array();

        /**
         * @var private string Errors encountered
         */
        private $errors = "";

        /**
         * @var private array list of files for each module
         */
        private $file_list = array();

        /**
         * Where we are saving all configuration data
         * @var protected I2CE_MagicData $storage
         */
        protected $storage;

        /**
         * the current template we are working with
         * @var protected I2CE_MagicDataTemplate $template;
         */
        protected $template;

        /**
         * $var protected array $merges.  Keys are magic data paths,
         * values are merge type
         */
        protected $merges;

        /**
         * The locales for which we check for config files.
         */
        protected $locales;

        /**
         * @var protected mixed $hash.  The hash of the last config
         * file processed.  It is a string, the hash, if the last
         * config file loaded successully.  false if the last config
         * file did not load succesully.  null if there was never a
         * config file processed.
         */
        protected $hash;

        /**
         * Constructor
         *
         * @param I2CE_MagicData $storage -- if none is passed in,
         * then a simple MagicData and config storage object using
         * MagicDataStorageMem will be set up.
         * 
         * @param boolean $set_last_access
         */
        public function __construct($storage = null, $set_last_access = true) {
            $this->locales = array(I2CE_Locales::DEFAULT_LOCALE);
            if(null !== $storage) {
                $this->storage = $storage;
            }    else {
                $store = new I2CE_MagicDataStorageMem;
                $config = I2CE_MagicData::instance("config");
                $config->addStorage($store);
                $this->storage = $config;
                I2CE::setConfig($config);
            }
            $this->resetMerges();
            $this->checked_paths = array();
            $this->set_last_access = $set_last_access;
            $this->setRoot();
        }

        public function __destruct() {
            $this->storage = null;
            $this->template = null;
        }

        /**
         * Set the locales used when handingling the processing of config data.
         * 
         * @param mixed $locales. Either a string ( a locale) or an array of strings
         */
        public function setLocales($locales) {
            if (is_string($locales)) {
                $locales = array($locales);
            }
            if (!is_array($locales)) {
                return;
            }
            $this->locales = $locales;
        }

        /**
         * Load a config file with various extensions.
         *
         * @param   string  $file            Path to the config file
         * @param   boolean $verbose_errors  (true)
         *
         * @returns booolean true on success
         */
        protected function loadConfigFile($file, $verbose_errors = true) {
            $template = new I2CE_MagicDataTemplate();
            $template->setVerboseErrors($verbose_errors);
            if ($template->loadRootFile($file)) {
                $this->template = $template;
            } else {
                if ($verbose_errors) {
                    I2CE::raiseError("Unable to load config file $file");
                }
                return false;
            }
            return true;
        }

        /**
         * Get the hash of the contents of the last processConfifFile.  
         * @returns mixed. String, the hash, if the last config file
         * loaded successfully.  false if the last config file did not
         * load succesully.  null if there was never a config file
         * processed.
         */
        public function getHash() {
            return $this->hash;
        }

        /**
         * Loads in a config file and validates it against its
         * referenced DTD.  Sets the access time and the shortname
         * (provided in the metadata) for the file.  If there is
         * already a config file with the same shortname, it will
         * remove that fro the list of the config files.  Save all the
         * configuration metadata under $storage->$shortname where
         * $shortname is the shortname provided for by the config
         * file.
         *
         * @param string $file The full path to the config file.  If
         * this is less than the time the file was last accessed, it
         * does not load the file/set the access time.
         *
         * @param mixed $process_configuration: Boolean -- true if you
         * want to save the configuration data into (defaults to
         * false).  If true process all config data.  If it is a
         * string, then we assume that it is a version number, in
         * which case we process all configuration data that is
         * versioned after the specified version
         *
         * @param boolean $verbose_errors.  defaults to true.  
         *
         * @param array $localized.  Data on localized versions of the
         * file already loaded.
         *
         * @returns string the shortname provided for by this config
         * file or null on failure.
         */
        public function processConfigFile($file, $process_configuration = false, $verbose_errors = true,   $process_meta  = true, $localized=array()) {
            $this->hash = false;
            $file_type = strtoupper(substr(strrchr($file, "."),1));
            $r_file = I2CE_FileSearch::realPath($file);

            if (!$this->loadConfigFile($r_file,$verbose_errors)) {
                if ($verbose_errors) {
                    I2CE::raiseError("Invalid Config file $file");
                }
                return null;

            }
            $file = $r_file;

            // just to make sure that verbose errors have been set in
            // case the loadConfigFile_XXX function forgot to.
            $this->template->setVerboseErrors($verbose_errors);
            if (!$this->template->validate()) {
                if ($verbose_errors) {
                    I2CE::raiseError("Invalid Config file $file");
                }
                return null;
            }
            $new_hash = md5(file_get_contents($file));
            $i2ceConfigNodeList = $this->template->query('/I2CEConfiguration');
            if ($i2ceConfigNodeList->length != 1) {
                if ($verbose_errors) {
                    I2CE::raiseError("No I2CEConfiguration node in $file");
                }
                return null;
            } else {
                $config = $i2ceConfigNodeList->item(0);
            }
            $shortname = trim( $i2ceConfigNodeList->item(0)->getAttribute('name'));
            if (empty($shortname)) {
                I2CE::raiseError("no shortname for $file");
                return null;
            }

            $meta_config = $this->storage->config->data;
            if (isset($meta_config->$shortname)) {
                $t_file = null;
                if (!$meta_config->setIfIsSet($t_file,"$shortname/file")) {
                    $meta_config->$shortname->file = I2CE_FileSearch::relativePath($file); //Will make it realtive if need be
                } else {
                    $tr_file = I2CE_FileSearch::realPath($t_file); //this is the one currently found
                    if ( (!empty($t_file))  && $tr_file != $file) {
                        // note ':memory:EXT:$file' will not (hopefullly) be a readable file
                        if (file_exists($tr_file) && is_readable($tr_file)) {
                            I2CE::raiseError("Naming conflict in module $shortname between files $file and $tr_file. \n Will use $tr_file");
                            $this->moved[$shortname] = true;
                        }                    
                        if ($file[0] ==':') { 
                            $meta_config->$shortname->file = $tr_file; //Will make it realtive if need be
                        } else {
                            $meta_config->$shortname->file = I2CE_FileSearch::relativePath($tr_file); //Will make it realtive if need be
                        }
                        I2CE::raiseError("Module $shortname has file:" . $meta_config->$shortname->file);
                        // clear the op code cache as we may have a conflict in names files.
                        if (function_exists('apc_clear_cache')) {
                            apc_clear_cache();
                            apc_clear_cache('user');
                        } 
                        $existing_hash = '';       
                        $meta_config->setIfIsSet($existing_hash,"$shortname/hash");

                        if ($process_meta && $existing_hash &&
                            ($existing_hash == $new_hash)) {

                            // we already know everything about this file.
                            $process_meta = false;
                            @$mtime = filemtime($t_file); 

                            // will be false for ':memory:EXT:$file' -- 
                            // set the time to now.
                            if ($mtime === false) {
                                I2CE::raiseError("No mtime -- using now");
                                $mtime = time();
                            }
                            if ($this->set_last_access) {                            
                                //I2CE::raiseError("Setting last access for $shortname to $mtime at "  . $meta_config->getPath());
                                $meta_config->$shortname->last_access = $mtime;
                            }
                        }
                    } else {
                        //will be false for ':memory:EXT:$file'
                        @$mtime = filemtime($file);

                        if ($mtime && $meta_config->__isset("$shortname/last_access") &&
                            $meta_config->$shortname->last_access >= $mtime) {
                            //we already know everything about this file.
                            $process_meta = false;
                        } 
                    }
                }
            } else {
                $meta_config->$shortname->file = I2CE_FileSearch::relativePath($file); //will make it realtive if need be
            }
            $shortname =  $this->processConfig($process_configuration,
                                               $verbose_errors,$process_meta);

            if (empty($shortname)) {
                if($verbose_errors) {
                    I2CE::raiseError("Could not process config: $file");
                }
                return null;
            }
            // will be false for ':memory:EXT:$file' -- set the time to now.
            if(!realpath($file)) {
                $mtime = time();
            } else {
                $mtime = filemtime($file);
            }
            if ($this->set_last_access) {
                $meta_config->$shortname->last_access = $mtime;
            }
            $meta_config->$shortname->hash = $new_hash;
            $this->hash = $new_hash;
            return $shortname;
        }  

        public function importLocalizedTemplates( $localized = array()) {
            $imported = array();
            $i2ceConfigNodeList = $this->template->query('/I2CEConfiguration');
            if ($i2ceConfigNodeList->length != 1) {
                I2CE::raiseError("No configuration template loaded");
                return $imported;
            } else {
                $configNode = $i2ceConfigNodeList->item(0);
            }
            $shortname = trim( $configNode->getAttribute('name'));
            if (!$shortname) {
                I2CE::raiseError("Could not find module name");
                return $imported;
            }
            $config = $this->storage->config->data->$shortname;
            $file = false;
            $config->setIfIsSet($file,"file");
            if (!$file) {
                I2CE::raiseError("Could not source file");
                return $imported;
            }
            $dirs = array();
            $basedir = dirname($file);
            $basename = basename($file);
            $config->setIfIsSet($dirs,"paths/CONFIGS",true);
            if (!is_array($localized)) {
                $localized = array();
            }
            foreach ($dirs as $dir) {
                foreach ($this->locales as $locale) {
                    if (array_key_exists($locale,$imported)) {
                        continue;
                    }
                    if (I2CE_FileSearch::isAbsolut($dir)) {  //the config path in theconfig file is absolut.
                        $localized_file = I2CE_FileSearch::realPath($dir . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $basename);                
                        $dir = I2CE_FileSearch::relativePath($dir);
                    } else { //the config path in theconfig file is relative to the module file
                        $localized_file = I2CE_FileSearch::realPath($basedir . DIRECTORY_SEPARATOR . $dir  . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $basename);               
                    }
                    if (!$localized_file || !is_file($localized_file) || !is_readable($localized_file)) {
                        continue;
                    }
                    I2CE::raiseError("Loading $localized_file");
                    $loc_template = new I2CE_MagicDataTemplate();                    
                    $loc_template->loadRootFile($localized_file);
                    $loc_node =$loc_template->doc->documentElement;
                    if (!$loc_node instanceof DOMNODE) {
                        continue;
                    }
                    $results = $loc_template->query('./configurationGroup', $loc_node);
                    $localenode = null;
                    if ($results->length == 1) {
                        $localenode = $results->item(0);
                        if ($localenode->getAttribute('locale') !== $locale) {
                            I2CE::raiseError("Locale mismatch on $localized_file");
                            continue;
                        }
                    }
                    $results= $loc_template->query('./metadata/version',$loc_node);
                    if ($results->length != 1) {
                        I2CE::raiseError("No version on $localized_file");
                        continue;
                    }
                    $new_vers = trim($results->item(0)->textContent);
                    if (!array_key_exists($locale,$localized) 
                        || !is_array($localized[$locale]) 
                        || !array_key_exists('vers',$localized[$locale]) 
                        || !is_string($localized[$locale]['vers']) 
                        || strlen($localized[$locale]['vers']) == 0)  {
                        $old_vers = '0';
                    } else{
                        $old_vers = $localized[$locale]['vers'];
                    }
                    if ($localenode) {
                        $localenode_imported = $this->template->doc->importNode($localenode,true);
                        I2CE::raiseError("Adding in localizations of $shortname in $locale to process");
                        $configNode->appendChild($localenode_imported);
                    }
                    $data = array(
                        'file'=> I2CE_FileSearch::relativePath($localized_file),
                        'mtime'=> @filemtime($localized_file),
                        'hash' => md5(file_get_contents($localized_file)),
                        'old_vers'=>$old_vers,
                        'vers'=>$new_vers,
                        );                    
                    $imported[$locale] = $data;
                }
            }
            if (count($imported) > 0) {
                I2CE::raiseError("Found localized config files for " . $shortname . ": " .implode(',',array_keys($imported)));
            }
            return $imported;
        }

        public function processConfig($process_configuration=false,
                                      $verbose_errors = true,
                                      $process_meta = true, $localized = array()) {
            $this->template->setVerboseErrors($verbose_errors);
            $i2ceConfigNodeList = $this->template->query('/I2CEConfiguration');
            if ($i2ceConfigNodeList->length != 1) {
                if($verbose_errors) {
                    I2CE::raiseError("NodeList Length != 1");
                }
                return null;
            } else {
                $config = $i2ceConfigNodeList->item(0);
            }
            if ($this->storage->is_scalar("config/data")) {
                I2CE::raiseError("bad magic data at config/data\n" . $this->storage->getPath() );
                return null;
            }
            $shortname = trim( $i2ceConfigNodeList->item(0)->getAttribute('name'));
            if ($process_meta && $shortname) {
                if (!$this->template->getConfigMetaData($this->storage->config->data)) {
                    if($verbose_errors) {
                        I2CE::raiseError("Can not get ConfigMetaData");
                    }
                    return null;
                }
            }
            $meta_config = $this->storage->config->data->$shortname;
            $config_groups = $this->template->query("./configurationGroup",$config); 
            if($config_groups->length > 0 ) { 
                if ($process_configuration !== false) {
                    if ($shortname != 'I2CE') {
                        $storage = $this->storage->modules;
                    } else {
                        $storage = $this->storage;
                    }
                    foreach ($config_groups as $config_group) {
                        $locale = false;
                        $status = $this->template->getDefaultStatus();
                        if ($config_group->hasAttribute('locale')) {
                            $locale = $config_group->getAttribute('locale');
                        }
                        $vers = '0';
                        if ($locale && $locale != I2CE_Locales::DEFAULT_LOCALE) {
                            $status['locale'] = $locale;
                            $status['required'] = false;
                            if (array_key_exists($locale,$localized) && is_array($localized[$locale]) && array_key_exists('vers',$localized[$locale]) && is_string($localized[$locale]['old_vers']) && strlen($localized[$locale]['old_vers']) > 0) {
                                $vers = $localized[$locale]['old_vers'];
                            }
                        } else {
                            if (is_string($process_configuration) && strlen($process_configuration) > 0) {                   
                                $vers = $process_configuration;
                            }
                        }
                        if($verbose_errors) {
                            if ($locale) {
                                I2CE::raiseError("Processing config for ($shortname) on locale ($locale) with version ($vers)");
                            } else {
                                I2CE::raiseError("Processing config for ($shortname) with version ($vers)");
                            }
                            I2CE::raiseError("Reading in config values for version >= $vers");
                        }
                        if ($this->template->setConfigValues($config_group,$storage, $status, $vers) === false ){
                            if($verbose_errors) {
                                I2CE::raiseError("Invalid configuration:  Couldn't setConfigValues");
                            }
                            return null;
                        }
                    }
                    $this->merges = array_merge($this->merges,$this->template->getMerges());
                }

                if ($process_meta) {
                    $meta_config->noConfigData = '0';
                }
            } else {
                if ($process_meta) {
                    $meta_config->noConfigData = '1';
                }
            }
            return  $shortname;
        }

        /**
         * Get the magic data paths whose status we are tracking.  
         * $retrurns array.  Keys are magic data paths, values are the merge type
         */
        public function getMerges() {
            return $this->merges;
        }
        /**
         * Reset the tracked data.
         */
        public function resetMerges() {
            $this->merges = array();
        }

        private function setupFileSearch($mod = null, $path = "MODULES") {            
            $this->resetCheckedPaths();
            $file_search = I2CE::getFileSearch();
            if(null === $file_search) {
                I2CE::raiseError("Resetting file search");
                I2CE::setupFileSearch();
                $file_search = I2CE::getFileSearch();
            }
            // just to make sure all of our module paths are loaded.
            $mod_factory = I2CE_ModuleFactory::instance();
            $mod_factory->resetStoredLoadedPaths();
            $mod_factory->loadPaths($mod, $path, true, $file_search, $this->storage->config->data);
            return $file_search;
        }

        /**
         * Return a list of potential configuration files.  "Potential
         * config files" are those whose extension matches the regular
         * expression.
         *
         * @param array 
         * @returns
         */
        private function findPotentialConfigs($file_search) {
            $found = $file_search->findByGlob('MODULES','*.xml',true);
            return $found;
        }

        /**
         * Searches the system for available configuration files (if
         * not done already) and stores them in the given I2CE_MagicData
         *
         * @param I2CE_FileSearch A file search to use for modules.
         * If null(default) uses I2CE::getFileSearch()
         *
         * @param boolean $deep.  Defaults to false.  If false, only
         * checks the module paths of the currently enabled modules.
         * If true, checks the module paths of all modules which are
         * either enabled or disabled.
         *
         *
         * @param string $limit_to_subdir.  If $file_search is given
         * and this is set, we only add in the directories which are
         * subdirectorues of $limit_to_subdir (check is by string
         * comparision)
         *
         * @returns array of string the list of shortnames found
         */
        public function findAvailableConfigs($file_search = null, $deep = false, $limit_to_subdir = '', $verbose_errors=false) {
            if (!$file_search instanceof I2CE_FileSearch) {
                $file_search = $this->setupFileSearch();
                $limit_to_subdir = '';
            }
            $this->checked_paths = array_merge($this->checked_paths, $file_search->limitToSubdir('MODULES',$limit_to_subdir));
            $potential_configs = $this->findPotentialConfigs( $file_search);            
            if (!is_array($potential_configs)) {
                return array();
            }
            $shortnames = $this->shallowScan($potential_configs, $verbose_errors);
            if ($deep) {
                $this->recursiveScan($shortnames, $limit_to_subdir, $verbose_errors);
            }
            return $shortnames;
        }

        private function recursiveScan(&$shortnames,  $limit_to_subdir, $verbose) {
            $added_shortnames = array();
            foreach ($shortnames as $shortname) {
                $file_search = new I2CE_FileSearch();
                $mod_factory = I2CE_ModuleFactory::instance();

                $mod_factory->loadPaths($shortname, 'MODULES', true,
                                        $file_search,$this->storage->config->data);
                $file_search->removePaths('MODULES',
                                          array_keys($this->checked_paths));

                $added_shortnames = array_merge(
                    $added_shortnames,
                    $this->findAvailableConfigs($file_search,  true,    $limit_to_subdir,   $verbose));
            }
            $shortnames = array_unique(array_merge($shortnames,$added_shortnames));
        }

        private function shallowScan($potential_configs, $verbose = false) {
            $shortnames = array();

            foreach($potential_configs as $potential_config) {
                //first check for a local config file...

                $shortname = $this->processConfigFile($potential_config,
                                                      false, $verbose);
                if (is_string($shortname) && strlen($shortname) > 0) {
                    $shortnames[] = $shortname;
                    $this->mod_path[$shortname] = $potential_config;
                }
            }
            return $shortnames;
        }



        protected function flattenRequirements($requirementDOM, $optional = null) {
            $reqs = array();
            $opts = array();
            $orders = array();
            $t_optional = false;
            if ($requirementDOM instanceof DOMElement && $requirementDOM->hasAttribute('name')) {
                //optioanl !== false  is same as optioanl === true || optional === null)
                if ($optional !==false   && $requirementDOM->hasAttribute('optional') && $requirementDOM->getAttribute('optional')) {
                    $t_optional = true;
                }
            }

            for ($i=0; $i < $requirementDOM->childNodes->length; $i++) {
                $child = $requirementDOM->childNodes->item($i);
                if (!$child instanceof DOMElement) {
                    continue;
                }
                $order = $child->getAttribute('order');
                $orders[$order] =  $child;
            }
            krsort($orders);
            foreach ($orders as $child) {
                list($req,$opt) = $this->flattenRequirements($child,$t_optional);
                foreach($req as $r) {
                    array_push($reqs, $r);
                }
                foreach($opt as $r) {
                    array_push($opts, $r);
                }

            }
            if ($requirementDOM instanceof DOMElement && $requirementDOM->hasAttribute('name')) {
                if ($requirementDOM->hasAttribute('optional') && $requirementDOM->getAttribute('optional')) {
                    array_push($opts, $requirementDOM->getAttribute('name'));
                } else {
                    array_push($reqs, $requirementDOM->getAttribute('name'));
                }
            }
            return array($reqs,$opts);
        }


        public function resetCheckedPaths() {
            $this->checked_paths = array();
        }

        protected $moved = array();

        /**
         * Checks the requirements.  if all requirements are met and
         * there are no conflicts, it returns a list of those that
         * need to be installed, an empty array() if none are needed.
         * On failure returns null.
         *
         * @param array $shortnames an array of shortnames
         *
         * @param mixed $disables a shortname or an array of
         * shortnames of modules to disable.  Defaults to empty array
         *
         * @param array $remove an array of potential shortnames to
         * remove -- if we did not find them somplace else. (it may
         * have just been moved) defaults to an empty array.
         * 
         * @param array $enabled_modules An array of shortnames of
         * enabled modules -- we check for conflicts against this
         * array
         *
         * @param mixed $required_modules A shortname or an array of
         * shortnames of required modules -- if a module is set to be
         * updated and it is not required by on of the modules in this
         * array, it will be set as an optional modules.  Defaults to
         * the empty array
         *
         * @returns array.  Keys are:
         *    'failure'      -  string with the reason for failture
         *    'requirements' -  array which has as keys shortnames and
         *                      values files for the requirements.
         *    'removals'     -  array of shortnames that need to be removed.
         *    'optional'     -  array with key shortnames and value true
         *    'moved'        -  array with key  shortnames and value true
         */
        public function checkRequirements($updates,  $disables = array(),  $removals = array(), 
                                          $enabled_modules = array(), $required_modules = array(), $reset_moved = true) {
            if ($reset_moved) {
                $this->moved = array();
            }
            if ($this->storage->setIfIsSet($i2ce_config_file,"config/data/I2CE/file")
                && ($i2ce_config_file   != ($new_i2ce_config_file = rtrim(dirname(dirname(__FILE__)),DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'I2CE_Configuration.xml'))) {
                //we have moved location of I2CE.  Other things have likely gotten moved around as well and we do not want to use the stored paths.
                //the file search has been set in I2CE_Update->_updateSite()->needs_upgrade to  I2CE::setupFileSearch( array( "CLASSES" => "./", 'MODULES'=>array(dirname($site_module_file),dirname(dirname(__FILE__)))), true);

                $this->resetCheckedPaths();
                I2CE::resetFileSearch();
                $file_search = I2CE::getFileSearch();
                if(null === $file_search) {
                    I2CE::raiseError("Resetting file search");
                    I2CE::setupFileSearch();
                    $file_search = I2CE::getFileSearch();
                }


                // just to make sure all of our module paths are loaded.
                $mod_factory = I2CE_ModuleFactory::instance();
                $mod_factory->resetStoredLoadedPaths();
                $mod_factory->loadPaths($mod, $path, true, $file_search, $this->storage->config->data);

            } else {
                $mods = array_unique(array_merge(array('I2CE'), $enabled_modules));            
                $file_search = $this->setupFileSearch($mods);
            }
            $this->findAvailableConfigs($file_search,true);
            if (!$this->storage->is_parent("config/data")) {
                I2CE::raiseError("Data not loaded.", E_ERROR);
                return array('failure' => "Data not loaded");
            }
            I2CE::raiseError("Done looking for available config files.  Available:\n\t" .implode(',',$this->storage->config->data->getKeys()));
            $conflicts = array();
            $available = $this->storage->config->data;
            if (!is_array($required_modules)) {
                $required_modules = array($required_modules);
            }
            foreach ($removals as $indx=>$remove) {
                if (isset($available->$remove)) {
                    // I found the thing I thought I might need to remove
                    // someplace else.  
                    unset($removals[$indx]);
                    $this->moved[$remove] =true;
                    $updates[] = $remove;
                }
            }
            foreach ($updates as $indx=>$update) {
                // make sure our desired updates are really available
                if (!isset($available->$update)) {
                    I2CE::raiseError("Desired update $update cannot be found. Instead it is being removed");
                    I2CE::raiseError(implode(',',$available->getKeys()));
                    $removals[] = $update;
                    unset($updates[$indx]);
                }
            }

            // this now holds everything that needs to be disabled or removed
            $removals = array_merge($disables,$removals);
            $tmp_removals = array();
            $avails = $available->getKeys();
            while(count($removals)>0){             
                $r = array_pop($removals);
                if (!is_scalar($r)) {
                    I2CE::raiseError("Internal misconfiguration on removals" , E_USER_ERROR);
                    continue;
                }
                if (array_key_exists($r,$tmp_removals) && $tmp_removals[$r]) {
                    continue;
                }
                /*
                 * start removing anything that is required by this
                 * guy.  work your way up the requirement tree.  we
                 * will use the avialable rather than currently
                 * installed information as we want the status at the
                 * _end_ of the process to be correct.  we are going to
                 * make sure everything that could be possibly
                 * installed is on the removal list.  we will check
                 * against this list as we process the depndencies for
                 * updates and installs to make sure there are no
                 * conflicts
                 */
                $t_removals = array($r);
                while(count($t_removals) > 0) {
                    $remove = array_pop($t_removals);
                    if (!is_string($remove)) {
                        return array('failure' => "bad removals: " . print_r($remove,true));
                    }
                    if (in_array($remove,$t_removals)) {
                        return array('failure' => "Recursion in dependency ".
                                     "list for removal on $remove:\n" . 
                                     implode(',',$t_removals));
                    }
                    $tmp_removals[$remove] = true;
                    foreach  ($avails as $avail) {
                        // should we check versions here?
                        if (isset($available->$avail->requirement->$remove)) {
                            array_push($t_removals,$avail);
                        }
                    }
                }
            }
            $removals = $tmp_removals;

            //make sure our desired updates are clean of anything set to be removed
            foreach ($removals as $removal=>$do_remove) {
                if (!is_string($removal) || !$do_remove) {
                    continue;
                }
                if (array_key_exists($removal,$updates)) {
                    unset($updates[$removal]);
                }
            }

            //now start working on the dependencies/requirements
            //updates is an array which holds the things we need to
            //install or update, but have not processed the requirements for 
            $requirementsDOM = new DOMDocument('1.0', 'UTF-8');
            $new_req = array();
            //I2CE::raiseError("Making main dependecy tree for:\n\t" . implode(',',$updates));
            foreach ($updates as $i=>$update) {            
                $optional = !$this->moduleRequires($update, $available->$update->version, $required_modules);
                // if ($optional) {
                //     I2CE::raiseError("Optional $update");
                // } else {
                //     I2CE::raiseError("Not optional $update");
                // }
                $t_requirementsDOM = new DOMDocument('1.0', 'UTF-8');
                for ($i =0; $i < $requirementsDOM->childNodes->length; $i++) {
                    $child = $requirementsDOM->childNodes->item($i);
                    $clone = $child->cloneNode(true);
                    $clone = $t_requirementsDOM->importNode($clone,true);
                    $t_requirementsDOM->appendChild($clone);
                }
                $new_req = $this->processRequirementsDOM($update, $t_requirementsDOM, $optional);

                if (is_string($new_req)) {
                    if (!$optional) {
                        return array('failure'=>$new_req);                    
                    } else {
                        //we failed on this update.  Try and recover.  
                        I2CE::raiseError("Unable to process optional module $update, skipping:  $new_req");
                        unset($updates[$i]);
                        continue;
                    }
                }
                //if (count($new_req) > 0) {
                    // if ($optional) {
                    //     I2CE::raiseError("Optional update $update adds the following additional required modules:\n\t" . implode(',',$new_req));
                    // } else {
                    //     I2CE::raiseError("Required update $update adds the following additional required modules:\n\t" . implode(',',$new_req));
                    // }
                //}
                if (!$optional) {
                    foreach ($new_req as $req) {
                        // check to see if this gal is on the removal list
                        if (array_key_exists($req, $removals)) {
                            return array('failure'=>"Module $req  is set to be removed but is also a requirement");
                        }
                    }
                }
                //we are good to to
                $requirementsDOM = $t_requirementsDOM;
            }
            

            list($requirements,$optionals) = $this->flattenRequirements($requirementsDOM);
            //the only optional modules here are the ones that are marked to be updated, but not a part of the required modules.  
            //this does not include, in general, all the optional modules that the site wants to enable.  these are added below
            //I2CE::raiseError("Required modules are=" . implode(',',$requirements));

            // now check the conflicts with what we desire to update/install and what is already installed.
            //I2CE::raiseError("Checking for conflicts in " . implode(" ", $requirements) . "\nagainst enabled: " . implode(" " , $enabled_modules));
            $ret = $this->checkForConflicts($requirements,$enabled_modules);
            if (is_string($ret)) {
                I2CE::raiseError("Found conflict in: $ret\nEnabled: " . implode(" ", $enabled_modules) . "\nRequired by update:" . implode(" ", $requirements));
                
                //OK.  there is a conflict. Let see if the conflict is against an optional module.  this is tedious/slow.
                //first we build a list of modules that are enabled bu not required.
                $modules_optional_enabled = array();
                foreach ($enabled_modules as $e) {
                    if ($this->moduleRequires($e, $available->$e->version, $required_modules)) {
                        continue;
                    }
                    $modules_optional_enabled[] = $e;
                }
                
                if (count($modules_optional_enabled) == 0) {
                    I2CE::raiseError("All currently enabled modules are required");
                    return array ('failure'=>$ret);                
                }
                I2CE::raiseError("The following enabled modules are optional.  Will try to disable some of them:\n" . implode(" ", $modules_optional_enabled));
                $allowed_optional_enabled = array();
                $optional_disables = array();
                $non_optional_enabled = array_diff($enabled_modules,$modules_optional_enabled);
                while (count($modules_optional_enabled) > 0) {
                    $optional_disable = array_pop($modules_optional_enabled);
                    if (is_string($t_ret = $this->checkForConflicts($requirements,array_merge($non_optional_enabled,array($optional_disable))))) {
                        //there is a conflict when trying to enable $optional_disable
                        $optional_disables[] = $optional_disable;
                        
                        I2CE::raiseError("WARNING: The module $optional_disable is not allowed");
                    } else {
                        I2CE::raiseError("The module $optional_disable is allowed");
                    }
                }
                I2CE::raiseError('Adding enabled optional modules to removal list: '.  implode(" ", $optional_disables) );
                $tmp_removals = array();
                $avails = $available->getKeys();
                while(count($optional_disables)>0){             
                    $r = array_pop($optional_disables);
                    if (array_key_exists($r,$tmp_removals) && $tmp_removals[$r]) {
                        continue;
                    }
                    /*
                     * start removing anything that is required by this
                     * guy.  work your way up the requirement tree.  we
                     * will use the avialable rather than currently
                     * installed information as we want the status at the
                     * _end_ of the process to be correct.  we are going to
                     * make sure everything that could be possibly
                     * installed is on the removal list.  we will check
                     * against this list as we process the depndencies for
                     * updates and installs to make sure there are no
                     * conflicts
                     */
                    $t_removals = array($r);
                    while(count($t_removals) > 0) {
                        $remove = array_pop($t_removals);
                        if (!is_string($remove)) {
                            return array('failure' => "bad removals: " . print_r($remove,true));
                        }
                        if (in_array($remove,$t_removals)) {
                            return array('failure' => "Recursion in dependency ".
                                         "list for removal on $remove:\n" . 
                                         implode(',',$t_removals));
                        }
                        $tmp_removals[$remove] = true;
                        foreach  ($avails as $avail) {
                            // should we check versions here?
                            if (isset($available->$avail->requirement->$remove)) {
                                array_push($t_removals,$avail);
                            }
                        }
                    }
                }
                $optional_disables = $tmp_removals;
                $xpath = new DOMXPath($requirementsDOM);
                foreach (array_keys($tmp_removals) as $r) {                    
                    $this->removeRequirement($r,$requirementsDOM);
                }
                I2CE::raiseError('Adding enabled optional modules to removal list (with dependent modules): '.  implode(" ", array_keys($optional_disables)) );
                $removals = array_merge($removals,$optional_disables);
                $enabled_modules = array_diff($enabled_modules,array_keys($optional_disables));                
                $requirements = array_diff($requirements,array_keys($optional_disables));                

            }
            


            // now we check to see if there are any modules that are requested to be enabled.
            $enable_list = array();

            foreach (array_diff($requirements, array_keys($removals)) as $req) {
                $es = array();
                $available->setIfIsSet($es,"$req/enable",true);                
                foreach ($es as $e) {
                    if (in_array($e,$requirements)) {
                        continue;
                    }
                    $enable_list[$e] = true;
                }
            }
            $enable_list = array_keys($enable_list);
                    
            if (count($enable_list) > 0) {
                //I2CE::raiseError("Processing main dependecy for optional modules:". implode(',',$enable_list));
                while (count($enable_list) > 0) {
                    $e = array_shift($enable_list);
                    //I2CE::raiseError("Doing $e from enable list");
                    //I2CE::raiseError("Current optionals on $e=" . implode(" ", $optionals));

                    $t_requirementsDOM = new DOMDocument('1.0', 'UTF-8');
                    for ($i =0; $i < $requirementsDOM->childNodes->length; $i++) {
                        $child = $requirementsDOM->childNodes->item($i);
                        $clone = $child->cloneNode(true);
                        $clone = $t_requirementsDOM->importNode($clone,true);
                        $t_requirementsDOM->appendChild($clone);
                    }
                    //try to see if we can  add in $e as an optional module will cause a conflict.
                    $ret = $this->processRequirementsDOM($e,$t_requirementsDOM,true);
                    if (is_string($ret)) {
                        // we don't really care if this fails but lets put
                        // a notice anyways
                        I2CE::raiseError("Internal Configuration Error for $e -- Could not enable requested optional module: $ret");
                        continue;
                    }
                    //now check that the new requirements added don't have conflicts with existing enabled modules, any requireed modules.  skip the modules set to be removed
                    $conflict = $this->checkForConflicts($requirements, array_merge(array($e), $ret, $enabled_modules));
                    //$conflict = $this->checkForConflicts(array_merge(array($e),$ret), array_diff(array_unique(array_merge($enabled_modules, $requirements)),array_keys($removals)));
                    if (is_string($conflict)) {
                        I2CE::raiseError("Could not enable $e as there was a conflict: $conflict");
                        //skip to the next enable request
                        continue;
                    }
                    // check to see if any of the newly added modules are  on the removal list                
                    foreach ($ret as $nreq) {
                        if (array_key_exists($nreq, $removals)) {
                            I2CE::raiseError("Could not enable module $e as it's ".
                                             "requirement $nreq is set to be removed");
                            continue 2; //skip to the next enable request
                        }                
                    }

                    //if we made it here, all was good.
                    $requirementsDOM= $t_requirementsDOM;
                    list($requirements,$optionals) = $this->flattenRequirements($t_requirementsDOM);
                    I2CE::raiseError("Current requirements on $e=" . implode(" ", $requirements));
                    I2CE::raiseError("Current optionals on $e=" . implode(" ", $optionals));
                    foreach ($ret as $r) {
                        $es = array();
                        $available->setIfIsSet($es,"$r/enable",true);
                        foreach ($es as $e) {
                            if (in_array($e,$requirements) ||
                                in_array($e,$enable_list)) {
                                continue;
                            }
                            $enable_list[]  = $e;
                        }
                    }
                }
            }

            // list out all the module requirements. separated by
            // optional and required
            $optional = array();
            $xpath = new DOMXPath($requirementsDOM);
            $modules = $xpath->query("//module[@optional='1']");
            for ($i=0; $i < $modules->length; $i++) {
                $opt_file = false;
                $opt_mod = $modules->item($i)->getAttribute('name');
                if (!$opt_mod) {
                    continue;
                }
                if ($available->setIfIsSet($opt_file,"$opt_mod/file")) {
                    $optional[$opt_mod] = $opt_file;
                }                
                //$optional[ ] = $available->$req->file;
            }
            $reqs = array();
            foreach ($requirements as $req) {
                $reqs[$req] = $available->$req->file;
            }
            //for any already             
            //we need the optional module to be enabled in reverse order that they are in the dom.
            $optional = array_reverse($optional);  
            //get the list of enabled and up to date modules.
            $ignore_modules = array_diff($enabled_modules,$updates);
            //I2CE::raiseError("Requirements:" . implode(" ", array_keys($reqs)));
            //I2CE::raiseError("Optional:" . implode(" ", array_keys($optional)));
            //I2CE::raiseError("The following modules are being ignored as they are up to date and enabled " . implode(" ", $ignore_modules));
            foreach ($ignore_modules as $mod) {
                if (array_key_exists($mod,$optional)) {
                    unset($optional[$mod]);
                }
                if (array_key_exists($mod,$reqs)) {
                    unset($reqs[$mod]);
                }
            }

            //I2CE::raiseError("Needed Up Enable/Update Requirements:" . implode(" ", array_keys($reqs)));
            //I2CE::raiseError("Need to Enable/Update  Optional:" . implode(" ", array_keys($optional)));
            return array('requirements' => $reqs,
                         'removals'     => $removals,
                         'optional'     => $optional,
                         'moved'        => $this->moved);
        }

        protected function removeRequirement($r, $reqDom) {
            $xpath = new DOMXPath($reqDom);
            if ( ($nodeList = $xpath->query("//module[@name='" . $r . "']")) instanceof DOMNodeList) {
                foreach ($nodeList as $node) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        protected function checkForConflicts($requirements,$enabled_modules) {
            //$requiremnts is the list of modules we want to try enable.
            //$enabled_modules is the list of already enabled and required modules
            $available = $this->storage->traverse("config/data");
            foreach ($requirements as $required) {
                $conflicts =  $available->traverse("{$required}/conflict",false);
                if (!$conflicts instanceof I2CE_MagicDataNode) {
                    //there are now conflicts listed for the enabled module so we can continue
                    continue;
                }
                //now we go the list of conflicting modules for the enabled module to see if it is in our list of requirements
                foreach ($conflicts as $conflict=>$rs) { 
                    if (!in_array($conflict,$enabled_modules)) { 
                        //we can ignore this conflict as it we are not checking against it
                        continue;
                    }
                    //this conflict is in our list of requirements.. .now we need to make sure that the versioning stuff is fine
                    if (!$available->is_scalar("$conflict/version")) {
                        continue;
                    }
                    if (!$rs instanceof I2CE_MagicDataNode) {
                        I2CE::raiseError("Conflict $conflict has no has no details");
                        continue;
                    }
                    $conflicted = true;
                    $version = $available->$conflict->version;
                    // we want each conflict group to be anded together.
                    // we are only interested in failures.
                    foreach ($rs as $r) {
                        if (!$this->isReq($r)) {
                            I2CE::raiseError("Badness for $r");
                            continue;
                        }
                        if (!I2CE_Validate::checkVersion($version, $r->operator,$r->version)) {
                            $conflicted = false;
                            break;
                        }
                    }
                    if ($conflicted) {
                        return  "There is a conflict between $required and $conflict";
                    }
                }
            }
            return true;
        }


        protected function getNextUnchecked($requirementDOM) {
            if ($requirementDOM instanceof DOMElement && (!$requirementDOM->hasAttribute('checked')) && $requirementDOM->hasAttribute('name')) {
                return $requirementDOM;
            }
            $orders = array();
            for ($i=0; $i < $requirementDOM->childNodes->length; $i++) {
                $child = $requirementDOM->childNodes->item($i);
                if (!$child->hasAttribute('order')) {
                    I2CE::raiseError("No order attribute");
                    continue;
                }
                $order = $child->getAttribute('order');
                $orders[$order] =  $child;
            }
            ksort($orders);
            foreach ($orders as $child) {
                $req = $this->getNextUnchecked($child);
                if ($req !== false) {
                    return $req;
                }
            }
            return false;
        }


        /**
         * @param mixed $new_requests a shortname or an array of
         * shortnames of module we wish to be loaded.
         *
         * @param DOMDocument $requests The existing requests.  Each
         * node should have an order attribute.  Optionally hey may
         * have the attribute 'checked' meaning that we consider this
         * node to be checked.
         *
         * @param boolean $options. Defaults to false.  Set to true if
         * the new requests are optional i.e. don't cause a fatal
         * error/add to the DOM if there a conflict
         *
         * @returns an array of the new modules that were installed on
         * sucess. A string on failure describing the failure reason.
         *
         */
        public function processRequirementsDOM($new_requests,&$requests,$optional  = false ) {
            if (is_string($new_requests)) {
                $new_requests = array($new_requests);
            }
            if (!is_array($new_requests)) {
                I2CE::raiseError("Bad request");
                return "Bad request";
            }
            if (!$this->storage->is_parent("config/data")) {
                I2CE::raiseError("No configuration data set");
                return "No configuration data set";
            }

            $all_new_requirements = array(); //the array of all the new modules in and required by $new_requests
            $xpath = new DOMXPath($requests);

            //create a phonebook containing all the modules in the requirements DOM
            $phonebook = array();
            $modules = $xpath->query("//module"); 
            for ($i=0; $i < $modules->length;$i++) {
                $module = $modules->item($i);
                $phonebook[$module->getAttribute('name')] = $module; 
            }
            $max = $this->getMaxChildOrder($requests,$xpath);
            $count = 1;

            foreach ($new_requests as $s) {                
                if (array_key_exists($s, $phonebook) && $phonebook[$s] instanceof DOMNode) {
                    // it is already in the phonebook so we don't need to add it
                    continue;
                }
                $all_new_requirements[] = $s;
                $attrs = array('name'=>$s,'order'=>$count+$max);
                if ($optional) {
                    $attrs['optional'] = '1';
                } else {
                    $attrs['optional'] = '0';
                }
                $sNode = $requests->createElement('module');
                foreach ($attrs as $attr=>$val) {
                    $sNode->setAttribute($attr,$val);
                }
                $requests->appendChild($sNode);
                $phonebook[$s] = $sNode; //add it to the phonebook
                $count++;
            }
            $available = $this->storage->config->data;
            $sNode = $this->getNextUnchecked($requests);
            while ($sNode) {
                $s = $sNode->getAttribute('name');
                if (!$available->is_parent($s)) {
                    I2CE::raiseError("Requested module $s is not available:\n[" .implode(',',$available->getKeys()) . "]");
                    return "Requested module $s is not available";
                }
                //get this guy's requirements. 
                if (!$available->is_parent("$s/requirement")) {
                    $sNode->setAttribute('checked',true); //we have done checking $s
                    $sNode = $this->getNextUnchecked($requests);
                    continue;
                }
                $reqnames =  $available->traverse("$s/requirement");
                foreach ($reqnames as $req=>$reqs) { //$req is the shortname of a module.  $reqs is a parent with $reqs as the requirement data for $req
                    if (!$reqs instanceof I2CE_MagicDataNode) {
                        continue;
                    }
                    if (!$available->is_scalar("$req/version")) {
                        $msg = "Requirement $req by $s is not available\n";
                        I2CE::raiseError($msg . "\n[" .implode(',',$available->getKeys()) . "]") ;
                        return $msg;
                    }
                    foreach ($reqs as $r) {
                        if (!$r instanceof I2CE_MagicDataNode) {
                            continue;
                        }
                        if (!$r->is_scalar("version") || !$r->is_scalar('operator')) {
                            continue;
                        }
                        //check to make sure the requirement is met by what is available                   
                        if (!I2CE_Validate::checkVersion($available->$req->version,
                                                         $r->operator,$r->version)) {
                            return  "Requirement for $s: " . $req ." " .
                                $r->operator . " "  .
                                $r->version .
                                " unavailable to the system";
                        }
                    }
                    if (array_key_exists($req, $phonebook) &&  $phonebook[$req] instanceof DOMNode) {
                        //this requirement is aleady in the tree.
                        $reqNode = $phonebook[$req];
                        // make sure this is marked as unchecked as we
                        // may need to reprocess
                        if ($reqNode->hasAttribute('checked')) {
                            $reqNode->removeAttribute('checked'); 
                        }
                        //$reqNode may have been added previously a a required module of an optional module.  however if $req is not an optional module, we need to set $reqNode, and all its children to not be optional
                        if (!$optional && $reqNode->hasAttribute('optional') && $reqNode->getAttribute('optional')) {
                            $this->makeRequired($reqNode);
                        }
                    } else {//create a new module element
                        $reqNode = $requests->createElement('module');
                        $reqNode->setAttribute('name',$req);
                        if ($optional) {
                             $reqNode->setAttribute('optional',1);
                        } else {
                            $reqNode->setAttribute('optional',0);
                        }
                        $all_new_requirements[] = $req;
                        $phonebook[$req] = $reqNode; //add it to the phonebook
                    }
                    $reqNode->setAttribute('order',$this->getMaxChildOrder($sNode,$xpath)+1);
                    //check that there is no  recursion in the depdencies
                    $pNode = $sNode;
                    $depList = $req;
                    while ($pNode instanceof DOMElement) {
                        $pName = $pNode->getAttribute('name');
                        $depList = $pName . ' => ' . $depList;
                        if ($pName == $req)  {
                            return "Recursion in dependency list: " . $depList;
                        }
                        $pNode = $pNode->parentNode;
                    }
                    $sNode->appendChild($reqNode);
                }
                $sNode->setAttribute('checked',true); //we have done checking $s
                $sNode = $this->getNextUnchecked($requests);
            }
            //if we made it here, all was good. 
            return $all_new_requirements;
        }

        protected function getMaxChildOrder($node,$xpath) {
            $results = $xpath->query('./module',$node);
            $max = 0;
            for ($i = 0; $i < $results->length; $i++) {
                $child = $results->item($i);
                if (!$child->hasAttribute('order')) {
                    I2CE::raiseError("Corruption: RequirementDOM node has no order");
                    continue;
                }
                $order = $child->getAttribute('order');
                $max = max($order,$max);
            } 
            return $max;
        }

        protected function makeRequired($node) {
            if (!$node instanceof DOMElement) {
                return;
            }
            I2CE::raiseError("Marking " . $node->getAttribute('name') . " required");
            $node->setAttribute('optional','0');
            foreach ($node->childNodes as $child) {
                $this->makeRequired($child);
            }
        }

        /**
         * Gets the list of modules depending on the specified module.
         * @param string $shortname The shortname of a module
         */
        public function getDependsList($shortname) {
            $deps = array();
            $config = $this->storage->config->data;
            if (!isset($config->$shortname)) {
                return $deps;
            }
            $mods = $config->getKeys();
            $vers = $config->$shortname->version;
            foreach ($mods as $mod) {
                if ($this->moduleRequires($shortname, $vers, $mod)) {
                    $deps[] = $mod;
                }
            }                    
            return $deps;
        }


        /**
         * Determine if this object is a I2CE_MagicDataNode that can
         * be used as a requirement.
         * @param mixed $string
         * @returns boolean
         */
        private function isReq($req) {
            return $req instanceof I2CE_MagicDataNode &&
                $req->is_scalar('operator') && $req->is_scalar('version');
        }

        /**
         * Gets information about the dependecies for a module
         *
         * @param string $module
         *
         * @returns array indexed by 'requirements', 'conflicts'
         * 'enable', 'path', and 'badness' where 'badness' is an array of
         * strings of error messages and the other are arrays of
         * shortnames
         */
        public function getDependencyList($module) {
            $this->errors = "";
            return array ('requirements' => $this->getRequirements($module),
                          'conflicts'    => $this->getConflicts($module),
                          'enable'       => $this->getEnabled($module),
                          'path'         => $this->getModulePath($module),
                          'badness'      => $this->errors);
        }

        /**
         * Set the root of where this I2CE instance started.
         * 
         * @param string $dir Directory (defaults to getcwd()) where
         * this instance of Configurator was created.
         *
         * @returns the fully resolved path to $dir
         */
        public function setRoot($dir = null) {
            if(null === $dir) {
                $dir = getcwd();
            }
            $this->current_base = realpath($dir).DIRECTORY_SEPARATOR;
            return $this->current_base;
        }

        /**
         * Get the root of where this I2CE instance started.
         *
         * @returns the fully resolved path to the starting directory
         */
        public function getRoot() {
            return $this->current_base;
        }

        /**
         * Set the module's path, usually used for testing.
         *
         * @param string $module
         * @param string $path
         */
        public function setModulePath($module, $path) {
            $this->mod_path[$module] = realpath($path);
            
            if(false === $this->mod_path[$module]) {
                I2CE::raiseError("$path doesn't exist.", E_ERROR);
            }

            return $this->mod_path[$module];
        }

        /**
         * Return the path to the module configuration file
         *
         * @param   string  $module     Module to check.
         * 
         * @returns string  Path to config file.
         */
        public function getModulePath($module) {
            if ($module && array_key_exists($module,$this->mod_path)) {
                return realpath($this->mod_path[$module]);
            } else {
                return false;
            }
        }

        public  function getI2CERoot() {
            return dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
        }

        /**
         * Return the directory of the module
         *
         * @param   string  $module     Module to check.
         * 
         * @returns string  Path to config file.
         */
        public function getModuleDir($module = null) {
            if(null === $module) {
                $module = $this->module;
            }
            $p = $this->getModulePath($module);
            if($p) {
                $p = substr(dirname($p).DIRECTORY_SEPARATOR,
                            strlen($this->getRoot()));
            }
            return $p;
        }

        /**
         * Get enabled sub-modules
         *
         * @param   string  $module     Module to check.
         * 
         * @returns array   List of enabled submodules
         */
        private function getEnabled($module) {
            $path = "config/data/$module/enable";
            if ( $module && $this->storage->is_parent($path)) {
                return $this->storage->getAsArray($path);
            } else {
                return array();
            }
        }

        /**
         * Get a list of requirement objects for the module
         *
         * @param   string  $module     Module to check.
         * 
         * @returns array List of requirement objects
         */          
        public function requirements($module) {
            $path = "config/data/$module/requirement";
            if ( $module &&  $this->storage->is_parent($path)) {
                return $this->storage->getAsArray($path);
            } else {
                return array();
            }
        }

        /**
         * Get a list of External dependencies for the module
         *
         * @param   string  $module     Module to check.
         * 
         * @returns array List of requirement objects
         */
        public function extRequirements($module) {
            $path = "config/data/$module/requirement_external";
            if ( $module &&  $this->storage->is_parent($path)) {
                return $this->storage->getAsArray($path);
            } else {
                return array();
            }
        }

        /**
         * Get a list of requirements for the module
         *
         * @param   string  $module     Module to check.
         * 
         * @returns array List of required modules.
         */          
        private function getRequirements($module) {
            $deps = array();
            $path = "config/$module/requirement";
            if (!$module || !$this->storage->is_parent($path)) {
                return array();
            }

            foreach($this->storage->traverse($path) as $reqName=>$reqs) {
                if (!$requirement->is_parent($reqName)) {
                    continue;
                }
                if (!isset($config->data->$reqName) ||
                    !$config->is_scalar("data/$reqName/version")) {
                    $this->errors .= "Required module $reqName is not available\n";
                    continue;
                }
                foreach ( $reqs as $req) {
                    if ($this->isReq($req)) {
                        $version = $config->data->$reqName->version;
                        if (!I2CE_Validate::checkVersion($version,
                                                         $req->operator,
                                                         $req->version)) {
                            $this->errors .= "Required module $reqName " .
                                $version . ' does not meet the requirement: ' .
                                $req->operator . ' ' . $req->version;
                            break;
                        }
                    }
                }
                //we made it here so we met all the requirements for $reqName
                $deps[]= $reqName;
            }

            return $deps;
        }

        /** 
         * For a given module, find a list of conflicting modules.
         *
         * @param   string  $module        Module to check
         *
         * @returns array  List of conflicts
         */
        private function getConflicts($module) {
            $conflicts = array();
            $path = "config/$module/conflict";
            if (!$module || !$this->storage->is_parent($path)) {
                return array();
            }
            foreach($this->storage->traverse($path) as $conName => $cons) {
                
                if ($conflict->is_parent($conName) &&
                    isset($config->data->$conName) &&
                    $config->is_scalar("data/$conName/version")) {
                    foreach ($cons as $con) {
                        if ($this->isReq($con)) {
                            $version = $config->data->$conName->version;
                            if (I2CE_Validate::checkVersion($version,
                                                            $con->operator,
                                                            $con->version)) {
                                $conflicts[]= $conName;
                                break;
                            }
                        }
                    }
                }
            }
            return $conflicts;
        }

        /**
         * Checks to see if a module is required by another module
         *
         * @param string $module
         * @param string $version
         * @param mixed $requirements a module name or an array of module names
         * @param boolean $deep. Defaults to true meaning we should
         * check requirements of requiremnets of requirements of ...
         *
         * @returns boolean
         */
        public function moduleRequires($module, $version, $requirements,$deep=true, $cat='requirement') {
            $config = $this->storage->config->data;
            $checked = array();
            if (!is_array($requirements)) {
                $requirements= array($requirements);
            }

            while (count($requirements)> 0)  {                
                $reqName = array_pop($requirements);
                if (array_key_exists($reqName, $checked) && $checked[$reqName]) {
                    continue;
                }
                $checked[$reqName] = true;
                if ($reqName == $module) {
                    switch ($cat) {
                    case 'requirement':
                        return true; 
                    case 'conflict':
                        return false;
                    default:
                        I2CE::raiseError("Unrecongnized action: $cat");
                        return null;
                    }
                }
                if (!$config->is_parent("$reqName/$cat")) {
                    continue;
                }
                $reqs = $config->$reqName->$cat;
                foreach ($reqs as  $reqN=> $rs) {
                    if (!$reqs->is_parent($reqN)) {
                        continue;
                    }
                    if ($reqN == $module) {
                        $maybe_required = true;                                
                        foreach ($rs as $req) {
                            if ($this->isReq($req) &&!I2CE_Validate::checkVersion($version,$req->operator,$req->version)) {
                                $maybe_required = false;
                            }
                        }
                        if ($maybe_required == true) {
                            return true;
                        }
                    } 
                }
                if ($deep && $config->is_parent("$reqName/$cat")) {
                    $requirements = array_merge($requirements,$config->$reqName->$cat->getKeys());
                }
            }
            return false;
        }

        /**
         * Checks to see if a module conflicts with  by another module
         *
         * @param   string  $module        Module to check
         * @param   string  $version       Version of the module
         * @param   mixed   $conflicts     Module name or an array of module names
         * @param   boolean $deep          Whether to recursively check the 
         *                                 requirements (true)
         * @returns boolean
         */
        public function moduleConflicts( $module,$version,$conflicts, $deep=true) {
            return $this->moduleRequires($module,$version,$conflicts,$deep, 'conflict');
        }

        /**
         * Return the version for a given module
         * @param string $module name
         * @returns string
         */
        public function getVersion($module) {
            return $this->storage->config->data->$module->version;
        }




        /**
         * Returns a brief one-line description of the module.
         * @param string $module name
         * @returns string
         */
        public function getDescription($module) {
            return $this->storage->config->data->$module->description;
        }

        /**
         * Returns a longer description of the module.
         * @param string $module name
         * @returns string
         */
        public function getLongDescription($module) {
            $config = $this->storage->config->data;
            $desc = null;
            if($config->pathExists("$module/longDescription")) {
                $desc = $config->$module->longDescription;
            }
            if(!is_string($desc)) {
                $desc = '';
                //$desc = "FIXME";    /* Avoid inserting thing string in a bunch of files */
            }
            return $desc;
        }

        /**
         * Returns a longer description of the module.
         * @param string $module name
         * @returns string
         */
        public function getPackageAuthors($module) {
            return $this->storage->config->data->$module->creator.
                '<'.$this->storage->config->data->$module->email.'>';
        }

        /**
         * Set the top level module for a directory.
         */
        public function setTopModule($dir, $module) {
            $dir = realpath($dir);
            $this->top_module[$dir] = $module;
        }

        /**
         * Find the top-level module in a directory.  (e.g. I2CE,
         * ihris-common, ihris-manage)
         *
         * @param string $d Directory to check
         *
         * @param boolean $verbose
         * @return
         */
        public function getTopModule($d, $verbose = false) {
            $d = realpath($d);
            if(!array_key_exists($d, $this->top_module)) {
                I2CE::setupFileSearch(array('MODULES'=>$d));
                $top_module =
                    $this->findAvailableConfigs(I2CE::getFileSearch(), false, "", $verbose);

                I2CE::getFileSearch()->removePath('MODULES', $d);

                if (!is_array($top_module) ||
                    count($top_module) != 1) {
                    return false;
                }

                $this->setTopModule($d, $top_module[0]);
            }
            return $this->top_module[$d];
        }

        public function getModulePathList($module) {
            $m = $this->storage->config->data->$module;
            if(null !== $m) {
                return $m->paths->getAsArray();
            }
            return null;
        }

        /**
         * Get a list of each files under each path.
         *
         * @param   string  $module        The module name
         *
         * @returns array List of file maps in the module.  Paths are
         *                  relative to the directory where the
         *                  module's configuration file is.
         * @todo Contains a hack for the special case where CLASSES directory is ./
         */
        public function getModuleFileMap( $module ) {
            if(!array_key_exists($module, $this->file_list)) {
                $seen = array();
                $base = $this->getRoot().$this->getModuleDir($module);
                $paths = $this->getModulePathList($module);
                $file_search = $this->setupFileSearch();
                $cats = array();
                $file_search->removePath("MODULES", $base, true);

                foreach ($paths as $cat => $dirs) {
                    /* We want all other paths processed before this one. */
                    if($cat === "CLASSES") {
                        array_unshift($cats, $cat);
                    }
                    else {
                        $cats[] = $cat;
                    }
                }

                $path = substr($this->getModulePath($module), strlen($base));
                $this->file_list[$module][""][] = $path;
                $seen[$path] = true;

                foreach (array_reverse($cats) as $cat) {
                    foreach($paths[$cat] as $d) {
                        if($real = realpath("$base$d")) {
                            if(is_dir($real)) {
                                $file_search->addPath($cat, "$real");
                                $file_search->addPath($cat, "$real/**");
                            }
                            else {
                                $this->file_list[$module][$cat][] = 
                                    $path = substr($real, strlen($base));
                            }
                        }
                    }

                    $found =
                        $file_search->findByRegularExpression($cat,
                                                              array('{[^#~]$}'),
                                                              true);
                    foreach($found as $file) {
                        $path = substr(realpath($file), strlen($base));

                        if(!array_key_exists($path, $seen)) {
                            $seen[$path] = true;
                            if(!($cat === 'CLASSES' && 
                                 in_array("./", $paths[$cat]) &&
                                 substr($path, 0, 7) === "modules")) {
                                $this->file_list[$module][$cat][] = $path;
                            }
                        }
                    }

                    foreach($file_search->getSearchPath($cat) as $d) {
                        $file_search->removePaths($cat, $d);
                    }
                }
            }
            return $this->file_list[$module];
        }
        
        /**
         * Get a list of files for a given module
         *
         * @param   string  $module        The module name
         *
         * @returns array   List of files in the module.  Paths are
         *                  relative to the directory where the
         *                  module's configuration file is.
         */
        public function getModuleFileList( $module ) {
            $map = $this->getModuleFileMap($module);
            $ret = array();

            if(array_key_exists("MODULES", $map)) {
                unset($map['MODULES']);
            }

            foreach($map as $key => $list) {
                $ret = array_merge($ret, $list);
            }
            return $ret;
        }

        /**
         * Scan a directory for available modules.
         *
         * @param   array   $search_dirs   List of Directories to search
         * @param   boolean $limit_search  Don't scan by depth       (true)
         * @param   boolean $rescan        Whether to force a rescan (false)
         * @param   boolean $verbose       Verbose errors            (false)
         *
         * @returns array                  List of modules found
         */
        public function getAvailableModules( $search_dirs,
                                             $limit_search = true,
                                             $rescan = false,
                                             $verbose = false ) {
            if($rescan) {
                $this->resetCheckedPaths();
            }
            if ( !is_array( $search_dirs ) ) {
                I2CE::raiseError( "Invalid argument search_dirs passed to getAvailableModules.", E_ERROR );
                return array();
            }

            foreach ($search_dirs as $dir) {
                if(!array_key_exists($dir, $this->checked_paths)) {
                    $this->checked_paths[$dir] = true;
                    foreach (glob($dir) as $d) {
                        $top_module = $this->getTopModule($d, $verbose);
                        if(false === $top_module) {
                            if($verbose) {
                                I2CE::raiseError("No top module found in $d".
                                                 ", skipping");
                            }
                            continue;
                        }

                        if($verbose) {
                            I2CE::raiseError("Found $top_module as top-level ".
                                             "module for $d");
                        }

                        I2CE::getFileSearch()->addPath('MODULES', $d);
                        $avail_modules = array();
                        if ($limit_search) {
                            if($verbose) {
                                I2CE::raiseError("Limiting search to $d");
                            }
                            $avail_modules = $this->findAvailableConfigs(null, true, $d,$verbose);
                        }
                        else {
                            $avail_modules =$this->findAvailableConfigs(null, true, "",$verbose);
                        }
                        I2CE::getFileSearch()->removePath('MODULES', $d, true);

                        foreach ($avail_modules as $m) {
                            if (array_key_exists($m,$this->found_modules)) {
                                if($verbose) {
                                    I2CE::raiseError("WARNING: conflict with module $m.  Found more than once -- Skipping");
                                }
                                $this->found_modules[$m] = false;
                            }
                            else {
                                $this->found_modules[$m] = $d;
                            }
                        }
                    }
                }
                $this->found_modules = array_filter($this->found_modules);
            }
            return array_keys($this->found_modules);
        }
    }
}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
