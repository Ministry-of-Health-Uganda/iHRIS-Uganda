<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
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
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2
* @since v3.2
* @filesource 
*/ 
/** 
* Class I2CE_Updater
* 
* @access public
*/


require_once 'I2CE_Configurator.php'; 
require_once 'I2CE_ModuleFactory.php'; 
require_once 'I2CE_FileSearch.php'; 


class I2CE_Updater {

    /**
     * @var public static int $timeout. update authentication time (in minutes) is to remain valid
     */
    public static $timeout = 60; 
    
    /**
     * method to check if authorized to update
     * @returns boolean
     */
    public static function isAuthorizedToUpdate() {
        if (!array_key_exists('HTTP_HOST',$_SERVER)) {
            //command line.  don't check.
            return true;
        }
        if (array_key_exists('update_authentication',$_SESSION) 
            && $_SESSION['update_authentication']
            && array_key_exists('update_authentication_time', $_SESSION)
            && ($_SESSION['update_authentication_time'] + (self::$timeout * 60)  >= time())
            )  {
            
            return true;
        } 
        $_SESSION['update_authentication'] = 0;
        require_once('I2CE_UserAccess_Mechanism.php');
        $userAccess = new I2CE_UserAccess_Mechanism();
        if ((array_key_exists('REQUEST_METHOD',$_SERVER)) 
            && ( $_SERVER['REQUEST_METHOD'] == "POST" )
            && array_key_exists('password',$_POST) 
            && $_POST['password']
            && $userAccess->userHasPassword('i2ce_admin',$_POST['password'])
            ) {                
            $_SESSION['update_authentication'] = 1;
            $_SESSION['update_authentication_time'] = time();
            return true;
        } 
        //we are not authenticated.  Ask for the password
        echo "<html><body>" . I2CE_Error::$errorImage .
            "<div style='position:relative;left:150px;top:50px'>".
            "<h2 style='color:#993300'>iHRIS Site Update</h2>".
            "<div    style='text-align:left;width:70%;height:30%;
            font-family:monospace;
            font-height:70%;
            overflow:none;margin-top:0;
            background-color:#ffffcc;border:dashed;border-width:3px;border-color:#ffcc99;opacity:0.8;'>".
            "<form action='' method='post'>Please enter the administrative (database) password to proceed. <p style='position:relative;left:2em'><b>Password:</b><input  type='password' name='password'/></p></form></div></div></body></html>";
        die();
    }

    /**
     * Controls the site update
     * @param string $site_module_file
     * @param boolean $verbose_errors.  Defaults to true
     */
    public static function updateSite($site_module_file, $verbose_errors =true) {
        if (!self::isAuthorizedToUpdate()) {
            return false;
        }
        I2CE::longExecution();
        $site_module_file = I2CE_FileSearch::absolut($site_module_file,1);
        I2CE::siteInitialized(false); 
        if (self::_updateSite($site_module_file,$verbose_errors)) {
            $config = I2CE::getConfig();
            $config->__set("/config/site/installation",'done');
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                if (array_key_exists('request',$_GET)) {                    
                    $url = $_GET['request'];
                } else {
                    $url = I2CE::getAccessedBaseURL();
                }
                echo "<br/><span style='color:#993300'>Site was succesully updated.  Continue on to your <a href='$url'>site</a>?</span>";
                $msg = "Site was succesully updated.  Continue on to your site?";
                
                echo "<script type='text/javascript'>if (confirm('$msg')) {setTimeout(function() {window.location= '$url';},500)}</script>"; //reload the requested page after 5 seconds
                flush();
                exit();
            } else {
                echo "Site was succesully updated\n";
                $enable=  preg_split( '/,/',I2CE::getRuntimeVariable('enable','' ),-1,PREG_SPLIT_NO_EMPTY);
                $disable=  preg_split( '/,/',I2CE::getRuntimeVariable('disable','' ),-1,PREG_SPLIT_NO_EMPTY);
                if ((count($enable) + count($disable)) > 0) {
                    $succ = self::updateModules($enable,$disable);                    
                    I2CE::siteInitialized(false); 
                    return $succ;
                } else {
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    protected static function _updateSite($site_module_file, $verbose_errors=true) {
        $config = I2CE::getConfig();
        $status = I2CE::allSystemsAreGoGo($site_module_file,true);
        I2CE::raiseError("Updating site for $site_module_file: $status");
        if (substr($status,0,11) == 'in_progress') {
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                if ( array_key_exists('restart',$_GET)) {
                    $status = 'restart_' .substr($status,12);                
                }
            } else {
                if (I2CE::getRuntimeVariable('force-restart' )) {
                    $status = 'restart_' .substr($status,12);                
                }           
            }
        }
        switch ($status) {
        case 'done':
            return true;
        case 'no_site':
            I2CE::raiseError("Cannot determine the site");
            return false;
        case 'restart_install':
        case 'needs_install':
            $config->__set("/config/site/installation",'in_progress_install');
            if (!self::install($site_module_file,(substr($status,0,7) == 'restart'), $verbose_errors)) {
                I2CE::raiseError("Unseting at " . $config->getPath());
                if (isset($config->config) && isset($config->config->site) && isset($config->config->site->installation)) {
                    unset($config->config->site->installation);
                }
                I2CE::raiseError("Installation of the site failed",E_USER_ERROR);                
                return false;
            }
            return !I2CE::hasWarnings();
        case 'restart_reinstall':
        case 'needs_reinstall':
            $config->__set("/config/site/installation",'in_progress_reinstall');
            if (!self::reinstall($site_module_file,(substr($status,0,7) == 'restart'),$verbose_errors)) {
                I2CE::raiseError("Reintiaizliation of site to new file $site_module_file failed" , E_USER_ERROR);
                return false;
            }
            return !I2CE::hasWarnings();
        case 'restart_reenable':
        case 'needs_reenable':
            $config->__set("/config/site/installation",'in_progress_reenable');
        I2CE::setupFileSearch( array( "CLASSES" => "./", 'MODULES'=>array(dirname($site_module_file),dirname(dirname(__FILE__)))), true);
            if ( $config->setIfIsSet($site_module, '/config/site/module')) {
                I2CE::raiseError("Somehow the site module $site_module was disabled.  Attempting to enable");
                $mod_factory=I2CE_ModuleFactory::instance();
                $mod_factory->resetStoredLoadedPaths();
                //$mod_factory->loadPaths(null,'CLASSES'); //make sure all of our classes are loaded.
                //somehow the site module got disabled.  this could happen, for example, if the paths got screwed up.  lets try and correct things
                if (!self::_updateModules($site_module)) {
                    I2CE::raiseError("Renabling failed for $site_module ",E_USER_ERROR);
                    return false;
                } 
                return !I2CE::hasWarnings();
            } 
            I2CE::raiseError("Lost the site module");
            return false;
        case 'restart_upgrade':
        case 'needs_upgrade':
            $config->__set("/config/site/installation",'in_progress_upgrade');
            $mod_factory=I2CE_ModuleFactory::instance();
            $mod_factory->resetStoredLoadedPaths();
            //$mod_factory->loadPaths(null,'CLASSES'); //make sure all of our classes are loaded.
            I2CE::setupFileSearch( array( "CLASSES" => "./", 'MODULES'=>array(dirname($site_module_file),dirname(dirname(__FILE__)))), true);
            if (!self::updateOutOfDateConfigFiles()) { 
                I2CE::raiseError("Error in checking configuration files", E_USER_ERROR);
                return false;
            }
            $config->__set("/I2CE/update/times/last", time());
            return !I2CE::hasWarnings();
            //Ireturn true;
        case 'in_progress_reenable':  
        case 'in_progress_upgrade':  
        case 'in_progress_install':
        case 'in_progress_reinstall':
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                I2CE::raiseError("System update is already in progress");
                $url = $_SERVER['REQUEST_URI'];
                if (!array_key_exists('restart',$_GET)) {
                    if (strlen($_SERVER['QUERY_STRING']) > 0) {
                        $url .= '&restart';
                    } else {
                        $url .= '?restart';
                    }
                }
                echo "<br/>Site update in progress.  <a href='$url'>Restart</a>?";
                $msg = "Site update in progress. Restart?";
                
                echo "<script type='text/javascript'>if (confirm('$msg')) {setTimeout(function() {window.location= '$url';},500)}</script>"; //reload the requested page after 5 seconds
                flush();
            } else {
                I2CE::raiseError("System update is already in progress.  To force the restart add  --force-restart=1");
            }
            return false;
        default:
            I2CE::raiseError("Unrecognized site status: $status");
            return false;
        }
    }
    



    /**
     * Worker function for  the first time installation.  Absolutely nothing has been done yet.
     * @param string $site_module_file The site module file  
     * @param boolean $verbose_errors.  defaults to true.  
     */
    protected static function install($site_module_file,$restart, $verbose_errors =true ) {
        $db = I2CE::PDO();
        I2CE::raiseError("Beginning new installation");
        I2CE::longExecution();
        I2CE::getFileSearch()->addPath('MODULES',dirname(dirname(__FILE__)),'EVEN_HIGHER');
        $configurator =new I2CE_Configurator(I2CE::getConfig());
        $site_module = $configurator->processConfigFile($site_module_file,false,$verbose_errors,true);
        I2CE::raiseError("Site module is: $site_module");
        //we don't want to process the site config data at the moment, just the meta data
        if (!(is_string($site_module) && strlen($site_module) > 0)) {
            I2CE::raiseError("Installation Failed for site.  Invalid configuration file $site_module_file.  No name given.",E_USER_ERROR);
            return false;
        }
        I2CE::raiseError("Enabling I2CE");
        I2CE::raiseError("Setting site module to $site_module");
        I2CE::getConfig()->config->site->module = $site_module;
        $mod_factory =  I2CE_ModuleFactory::instance();

        $success = self::_updateModules('I2CE');
        if (!$success) {
            I2CE::raiseError("Installation Failed for I2CE",E_USER_ERROR);
            return false;
        }
        if (I2CE::getConfig()->setIfIsSet($site_module,'/config/site/module')) {
            I2CE::getConfig()->config->data->$site_module->file = I2CE_FileSearch::relativePath($site_module_file);
        } else {
            I2CE::raiseError("Site is not set");
            return false;
        }
        I2CE::getConfig()->config->I2CE->update->times->last = time();
        I2CE::setupFileSearch( array( "CLASSES" => "./", 'MODULES'=>array(dirname(dirname(__FILE__)), dirname($site_module_file))), true);
        $mod_factory->resetStoredLoadedPaths();
        if ($restart) {
            //make sure all of our classes are loaded in case this was a restart
            $mod_factory->loadPaths(null,'CLASSES'); 
        } else {
            $mod_factory->loadPaths('I2CE',array('CLASSES','MODULES'),true);  
        }
        I2CE::raiseError("Begining initializiation of site {$site_module}", E_USER_NOTICE);
        if (!self::_updateModules($site_module)) {
            I2CE::raiseError("Site Module Installation Failed",E_USER_ERROR);
            return false;
        }
        //we got through I2CE's installation
        if ($db->inTransaction()) {
            I2CE::raiseError("Warning: ending installation still in transaction -- committing");
            $db->commit();
        }
        if (I2CE::hasWarnings()) {
            I2CE::raiseError( "Your site was not initialized. Please refer to the warning messages above.");                                
            flush();
            return false;
        } else {
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                $url = array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : "/";
                I2CE::raiseError("Congratulations, your site has been initialized. You may <a href='$url'>continue</a> onto the site.");
                echo "<script type='text/javascript'>window.location= '$url';</script>"; //reload the requested page.
                flush();
            }
            return true;
        }
    }

    
    protected static function reinstall($site_module_file, $restart ) {
        I2CE::raiseError("Beginning re-intiaizliation of site to new file $site_module_file");
        //we have at one point installed the system, however we changed
        //the config file that we are using or the name of the module we are using
        // we need to update the system starting with the site module
        $config = I2CE::getConfig();
        $site_module = '';
        $config->setIfIsSet($site_module,'/config/site/module');
        $mod_factory =  I2CE_ModuleFactory::instance();
        $previous_site_file = '';        
        $config->setIfIsSet($previous_site_file,'/config/data/' . $site_module . '/file');    
        $installed = '';

        I2CE::longExecution();
        $mod_factory =  I2CE_ModuleFactory::instance();


        $tmp_config = I2CE_MagicData::instance( "temp_ReInit" );
        I2CE::getFileSearch()->addPath('MODULES',dirname(dirname(__FILE__)),'EVEN_HIGHER');
        $configurator =new I2CE_Configurator($tmp_config,false);
        $site_module_config_file_path = '/config/data/' . $site_module . '/file';
        unset($config->$site_module_config_file_path);
        $new_site_module = $configurator->processConfigFile($site_module_file,true,false,true);
        I2CE::raiseError("Reinitializing: " . $new_site_module);
        if (!(is_string($new_site_module) && strlen($new_site_module) > 0)) {
            I2CE::raiseError("Installation Failed for site.  Invalid configuration file $site_module_file.  No name given.");
            return false;
        }
        if ($site_module !== $new_site_module) {
            I2CE::raiseError("You are not permitted to change the site from $site_module to $new_site_module");
            return false;
        }
        I2CE::raiseError( "Begining re-initializiation of site {$site_module} -- moving config file from {$previous_site_file} to {$site_module_file}");
        if ($config->is_parent("/config/data")) {
            I2CE::raiseError("Erasing all previously found config file locations");
            foreach ($config->traverse("/config/data") as $module=>$mod_config) {
                if ($module == $site_module || !$mod_config instanceof I2CE_MagicDataNode) {
                    continue;
                }
                $mod_config->__unset("file");
            }
        }        
        $config->$site_module_config_file_path = I2CE_FileSearch::relativePath($site_module_file); //make sure we are looking at the correct config file for the site module
        I2CE::setupFileSearch( array( "CLASSES" => "./", 'MODULES'=>array(dirname(dirname(__FILE__)),dirname($site_module_file))), true);
        $mod_factory->resetStoredLoadedPaths();
        $configurator->findAvailableConfigs(I2CE::getFileSearch(),true);
        if (!self::updateOutOfDateConfigFiles()) { //maybe something that wasn't in the site module was changed
            I2CE::raiseError("Re-installation failed for $site_module ");
            return false;
        }
        I2CE::setupFileSearch();
        $mod_factory->resetStoredLoadedPaths();
        $mod_factory->loadPaths(null,'CLASSES'); //make sure all of our classes are loaded.        
        $mod_factory->updateHooks(); 
        return true;
    }




    /**
     * Checks the registered config files {@see addConfigFile() } to make sure
     * that they have been initialized.  If the modified time is more recent than the
     * last access time, then it will re-initialize the files.  It also checks all
     * loaded config files for enabled modules/components to make sure that they are 
     * up-to-date in a simmiar way.  
     * @return boolean true on success.   Future desired behavior: redirect to an error/admin page on failure
     * or if the site module is removed.
     */
    protected static function updateOutOfDateConfigFiles( $shortnames = null ) {
        $mf = I2CE_ModuleFactory::instance();
        $updates = $mf->getOutOfDateConfigFiles($shortnames,false);
        if ( count($updates['updates']) + count($updates['removals']) > 0) {
            if (count($updates['updates']) > 0) {
                I2CE::raiseError("Will attempt to update: " . implode(',',$updates['updates']), E_USER_NOTICE);
            }
            if (count($updates['removals']) > 0) {
                I2CE::raiseError("Possibly removing the following modules because old location is invalid:\n" . implode(',',$updates['removals']));
            }
            //need to enable search paths for good modules.
            
            if (!self::_updateModules($updates['updates'],$updates['removals'], array(),array())) {
                I2CE::raiseError("Could not update our of date config files", E_USER_ERROR);
                return false;
            }
        }
        return true;
    }





    /***
     * Attempts to update the system on the modules indicated by $shortnames
     * @param mixed $updates a shortname or a an array of shortnames of modules to update
     * @param mixed $removals a shortname or an array of shortnames of modules that may need to be removed b/c i couldn't 
     * find them where they said they were.  they may have just moved, so we need to check that.
     * Defaults to the empty 
     * @return mixed true on success, on failure.
     */
    public static function updateModules($updates, $removals=array(),
                                         $optional_excludes = array() , $disables = array()) {        
        $mod_factory = I2CE_ModuleFactory::instance();
        $enabled = $mod_factory->getEnabled();
        $outofdate = $mod_factory->getOutOfDateConfigFiles($enabled,false);
        if ( count($outofdate['updates']) + count($outofdate['removals']) > 0) {
            I2CE::raiseError("Cannot update modules when some are out of date:" . print_r($updates,true));
            return false;
        }
        I2CE::raiseError("Updating Modules");
        I2CE::setupFileSearch();
        $mod_factory->resetStoredLoadedPaths();
        // make sure all of our classes are loaded.
        //$mod_factory->loadPaths(null,'CLASSES');
        I2CE::getFileSearch()->addPath('MODULES',dirname(dirname(__FILE__)),'EVEN_HIGHER');
        I2CE::getConfig()->config->site->installation = 'in_progress_upgrade';
        $ret = self::_updateModules( $updates, $removals, $optional_excludes, $disables );
        if ($ret == false) {
            I2CE::raiseError("Could not update modules", E_USER_ERROR);
        } else {
            I2CE::getConfig()->config->site->installation = 'done';
        }
        return $ret;
    }

    /***
     * Attempts to update the system on the modules indicated by $shortnames
     * @param mixed $updates a shortname or a an array of shortnames of modules to update
     * @param mixed $removals a shortname or an array of shortnames of modules that may need to be removed b/c i couldn't 
     * find them where they said they were.  they may have just moved, so we need to check that.
     * Defaults to the empty 
     * @param mixed $disables a shortname or an array of shortnames of modules that may need to be removed b/c i couldn't 
     * find them where they said they were.  they may have just moved, so we need to check that.
     * @return mixed true on success, on failure.
     */
    protected static function _updateModules($updates,$removals=array(), $optional_excludes = array(), $disables = array() ) {
        I2CE::raiseError("Updating Modules");
        //make sure everything is nice and fresh
        clearstatcache(); 
        $mod_factory = I2CE_ModuleFactory::instance();
        $exec = array('max_execution_time'=>20*60, 'memory_limit'=> (256 * 1048576));
        I2CE::longExecution($exec);
        if (!is_array($updates)) {
            $updates = array($updates);
        }
        if (!is_array($removals)) {
            $removals = array($removals);
        }
        
        $msg = "Will attempt to update:\n";
        foreach (array('Updates'=>$updates,'Removals'=>$removals,'Disables'=>$disables) as $k=>$v) {
            if (count($v) > 0) {
                $msg .= "\t$k:\n\t\t" . implode(',',$v) . "\n";
            }
        }
        I2CE::raiseError($msg);
        $storage = I2CE::getConfig();
        
        $tmp_storage = I2CE_MagicData::instance( "temp_ModuleFactory" );
        $configurator =new I2CE_Configurator($tmp_storage);
        if ($storage->setIfIsSet($sitemodule,"config/site/module")) {
            I2CE::raiseError("Site is set at " . $storage->getPath() . ' to be ' . $sitemodule);
            //make sure the site direcotry is added in to the config path.
            $data = $configurator->checkRequirements($updates,$disables,$removals,$mod_factory->getEnabled(),$sitemodule);
        } else {
            I2CE::raiseError("Site is not set at " . $storage->getPath());
            $data = $configurator->checkRequirements($updates,$disables,$removals,$mod_factory->getEnabled());
        }        

        //note that checkRequirements has the result of putting _all_ valid config module metadata under /config/data of $tmp_storage
        if (isset($data['failure'])) {
            $storage->clearCache();  
            I2CE::raiseError("Installation failed: " . $data['failure']);
            return false;
        } 
        foreach (array_keys($data['removals']) as $shortname) {
            if (!$mod_factory->disable($shortname)) { 
                $storage->clearCache();
                I2CE::raiseError("Unable to disable $shortname",E_USER_NOTICE);
                return false;
            }
        }
        //now we remove from the requirements list anything that is already enabled and  up-to-date 
        if (count($data['moved']) > 0) {
            I2CE::raiseError("Found the following in another location.  Attempting to move:" . implode(',',array_keys($data['moved'])));
            I2CE::setupFileSearch(array(),true);//reset the file search and clear its cache
            I2CE::getFileSearch()->addPath('MODULES',dirname(dirname(__FILE__)),'EVEN_HIGHER');
        }
        $skipped = array();
        $moved = array();
        foreach ($data['requirements'] as $shortname=>$file) { 
            if (!$mod_factory->isEnabled($shortname)) {
                continue;
            }
            if ($mod_factory->isUpToDate($shortname,$file) && $mod_factory->isUpToDateModule($shortname)) {
                //everything is in the correct place and up to date.
                $skipped[] = $shortname;
                $mod_factory->loadPaths($shortname,null,true); //for the loading of all categories for this module
                $storage->config->data->$shortname->file =  $data['requirements'][$shortname];
                I2CE::raiseError("Updated $shortname config file to be " . $data['requirements'][$shortname]);
                unset($data['requirements'][$shortname]); //this module is enabled and the config is up-to-date so we dont need to do anything
                continue;
            }
            //let us see if this module has been moved
            if (!$storage->__isset("config/data/$shortname")) {
                continue;
            }
            if (!$tmp_storage->__isset("config/data/$shortname")) {
                continue;
            }
            $meta = $storage->config->data->$shortname;
            $tmp_meta = $tmp_storage->config->data->$shortname;
            foreach (array("hash","last_access") as $key) {
                if (!isset($meta->$key) || !isset($tmp_meta->$key) || ($tmp_meta->$key !== $meta->$key)) {
                    continue 2;
                }
            }
            $class_file = null;
            $tmp_meta->setIfIsSet($class_file,"class/file");
            if ($class_file && $class_file = I2CE_FileSearch::realPath($class_file)) {
                if (!$meta->__isset("class/hash") ) {
                    continue;
                }
                if (!is_readable($class_file)) {
                    continue;
                }
                $contents = file_get_contents($class_file);
                if (!$contents) {
                    continue;
                }
                if ( $meta->class->hash !== md5($contents)) {
                    continue;
                }
            }
            $mtimes = array();
            foreach (array("class/file", "file") as $f) {
                if (!isset($tmp_meta->$f)) {
                    continue;
                }
                @$mtimes[$f] = filemtime(I2CE_FileSearch::realPath($tmp_meta->$f));
                if (!$mtimes[$f]) {
                    continue 2;
                }
            }
            if (false == $mod_factory->checkLocalesUpToDate($shortname,$class_file)) {
                //the locales for this module are not up to date
                continue;
            }
            //we made it here.  we can skip the update.
            I2CE::raiseError("Able to move config file for $shortname from:\n  " . $meta->file . "\nto:\n  ". $tmp_meta->file);
            foreach (array("class/file"=>"class/last_access", "file"=>"last_access") as $f=>$a) {
                $val = null;
                $tmp_meta->setIfIsSet($val,$f);
                if ($val === null)  {
                    continue;
                }
                $meta->$f = $val;
                $meta->$a = $mtimes[$f];
                $tmp_meta->$a = $mtimes[$f];
            }                
            unset($data['requirements'][$shortname]); //this module is enabled and the config is up-to-date so we dont need to do anything
            $mod_factory->loadPaths($shortname,null,true); //for the loading of all categories for this module
            $moved[] = $shortname;
        } 
        if (count($skipped) > 0) {
            I2CE::raiseError("Skipping update on the following up-to-date modules:" . implode(',',$skipped));
        }
        if (count($moved) > 0) {
            I2CE::raiseError("Moved the following  modules:" . implode(',',$moved));
        }
        I2CE::raiseError("Attempting to update/enable the following out of date modules: " . implode(',',array_keys($data['requirements'])));

        //make sure all of our class paths for existing moduels are loaded.
        $good_modules = array_diff($mod_factory->getEnabled(),$data['removals'], array_keys($data['requirements']));
        I2CE::raiseError("The following modules class paths are being added:\n\t". implode(',',$good_modules));
        $mod_factory->loadPaths($good_modules,'CLASSES',true); 

        if (!array_key_exists('optional',$data) || !is_array($data['optional'])) {
            $data['optional'] = array();
        }
        if (is_string($optional_excludes)) {
            $optional_excludes = array($optional_excludes);
        }
        if (!is_array($optional_excludes)) {
            $optional_excludes = array();
        }
        $to_enable = array_merge($data['requirements'],$data['optional']);
        //while (count ($data['requirements']) > 0) {
        I2CE::raiseError("Trying to enable the following required:\n"  . implode(" ",array_keys($data['requirements'])));
        I2CE::raiseError("Trying to enable the following optional:\n"  . implode(" ",array_keys($data['optional'])));
        I2CE::raiseError("Trying to enable the following:\n"  . implode(" ",array_keys($to_enable)));
        while (count($to_enable) > 0) {
            $shortname = key($to_enable);
            // reset ($data['requirements']);
            // $shortname = key($data['requirements']);
            if ( (!is_string($shortname)) || strlen($shortname) == 0) {
                I2CE::raiseError("Invalid Shortname");
                continue;
            }
            $file =  array_shift($to_enable);
            if ( (array_key_exists($shortname,$data['optional'] )) &&  (in_array($shortname,$optional_excludes)) ) {
                continue;
            }            

            $old_vers = '0';
            $storage->setIfIsSet($old_vers,"/config/data/$shortname/version");        
            $new_vers = null;
            $tmp_storage->setIfIsSet($new_vers,"/config/data/$shortname/version");
            $mod_config = $tmp_storage->config->data->$shortname;
            $storage->__unset("/config/data/$shortname");
            //set the module's metadata to the new stuff.
            $storage->config->data->$shortname = $mod_config;
            //keep the old version set around until we know that the module was upgraded
            $storage->config->data->$shortname->version = $old_vers; 
            if (!$tmp_storage->__isset("/config/data/$shortname/class/name")) { 
                //there is no class associated in the new version of  this module.
                if ($storage->__isset("/config/data/$shortname/class/name")) {
                    //there was a class previously assoicated to this module -- remove its hooks/fuzzy methods,
                    $mod_factory->removeHooks($shortname);                     
                    unset($storage->config->data->$shortname->class);
                }
            }
            foreach (array('conflict'=>'conflict_external','requirement'=>'requirement_external') as $type=>$key) {
                if ($mod_config->is_parent($key)) {
                    foreach ($mod_config->$key as $ext=>$req_data) {
                        if ($req_data instanceof I2CE_MagicDataNode) {
                            $req_data = $req_data->getAsArray();
                        } else {
                            $req_data = array();
                        }
                        foreach ($req_data as $req_d) {
                            if (!is_array($req_d) || !array_key_exists('eval', $req_d) || !$req_d['eval']) {
                                continue;
                            }
                            $eval = null;
                            @eval('$eval = ' . $req_d['eval'] . ';');                            
                            if (is_bool($eval) && !$eval) {
                                if (self::failedRequiredUpdate($shortname,$data,"Could not verify external $type $ext for $shortname", $configurator)) {
                                    return false;
                                } else {
                                    continue 4;
                                }
                            }
                        }
                    }
                }
            }

            $mod_storage = I2CE_MagicData::instance( "temp_ModuleFactory_" . $shortname );
            I2CE::getFileSearch()->addPath('MODULES',dirname(dirname(__FILE__)),'EVEN_HIGHER');
            $r_file = I2CE_FileSearch::realPath($file);
            $mod_configurator =new I2CE_Configurator($mod_storage);      
            $s = $mod_configurator->processConfigFile($r_file,false,true,true,false); 
            if ( !is_string($s)) {
                if (self::failedRequiredUpdate($shortname,$data,"Could load configuration file",$configurator)) {
                    return false;
                } else {
                    continue;
                }
            }

            if  ($s != $shortname) { //be super safe        
                if (self::failedRequiredUpdate($shortname,$data,"Configuration shortname mismatch ($s/$shortname)", $configurator)) {
                    return false;
                } else {
                    continue;
                }
            }
            self::processErasers($mod_config,$old_vers);                       
            $loaded = self::loadModuleMagicData($shortname,$r_file, $old_vers,$new_vers,$mod_configurator);

            if ($loaded === false) {
                if (self::failedRequiredUpdate($shortname,$data,"Could not load magic data", $configurator)) {
                    return false;
                } else {
                    continue;
                }
            }

            $loaded_mod_config = $mod_storage->config->data->$shortname;
            self::processErasers($loaded_mod_config,$old_vers);


            if (!self::preUpgradeModule($shortname,$old_vers,$new_vers,$mod_storage)) {
                if (self::failedRequiredUpdate($shortname,$data,"Could not pre-update module", $configurator)) {
                    return false;
                } else {
                    continue;
                }
            }

            //if $loaded === true, then there was no magic data to update, so we can skip the store.
            if (is_array($loaded) && !self::storeModuleMagicData($shortname,$old_vers,$new_vers,$mod_configurator, $loaded)) {
                if (self::failedRequiredUpdate($shortname,$data,"Could not store magic data", $configurator)) {
                    return false;
                } else {
                    continue;
                }
            }


            if (!self::upgradeModule($shortname,$old_vers,$new_vers)) {
                if (self::failedRequiredUpdate($shortname,$data,"Could not upgrade module", $configurator)) {
                    return false;
                } else {
                    continue;
                }
            }

            if (!self::postUpdateModule($shortname,$old_vers,$new_vers)) {
                if (self::failedRequiredUpdate($shortname,$data,"Could not post update module", $configurator)) {
                    return false;
                } else {
                    continue;
                }
            }
            $mod_factory->setModuleHash($shortname); 
            $mod_factory->setModuleClassHash($shortname,false); 
            $mod_configurator->__destruct();
            $mod_configurator = null;
            $mod_storage->erase();
            $mod_storage = null;

            $storage = I2CE::getConfig(); //just to make sure that any upgrades did not change the storage.  this happens with i2ce install for example
            $storage->config->data->$shortname->version = $new_vers; 
            //we updated this module.  update the permanent modules config data with the temporary
            $mod_factory->loadPaths($shortname,null,true); //for the loading of all categories for this module
        }        
        I2CE::raiseError("Enabled Modules: " . implode(',',$mod_factory->getEnabled()));            
        return true; 
    }



    protected static function processErasers($mod_config,$old_vers) {
        if ($mod_config->is_parent('erasers')) {
            foreach ($mod_config->erasers as $erase_data) {
                if (!$erase_data instanceof I2CE_MagicDataNode || !$erase_data->is_scalar('path')) {
                    I2CE::raiseError("Bad erase data at $i");
                    continue;
                }
                $md_path = trim($erase_data->path);
                if (strlen($md_path)  ==0  || $md_path[0] != '/') {
                    I2CE::raiseError("Bad Path");
                    continue;
                }
                $passed = true;
                if ($erase_data->is_parent('requirements')) {
                    foreach ($erase_data->requirements as $req) {
                        if (!$req instanceof I2CE_MagicDataNode || !$req->is_scalar('operator') || !$req->is_scalar('version') ) {
                            continue;
                        }
                        $passed &= I2CE_Validate::checkVersion($old_vers,  $req->operator, $req->version);
                    }
                }
                if (!$passed) {
                    continue;
                }
                $eraseNode = I2CE::getConfig()->traverse($md_path,false,false);
                if (!$eraseNode instanceof I2CE_MagicDataNode) {
                    I2CE::raiseError("data at $md_path already not present");
                    continue;
                }
                I2CE::raiseError("Erasing magic data at " . $eraseNode->getPath(false));
                if (!$eraseNode->erase()) {
                    if (self::failedRequiredUpdate($shortname,$data,"Could not erase magic data at " . $eraseNode->getPath(), $configurator)) {
                        return false;
                    } else {
                        continue;
                    }
                }
            }
        }
        if ( ($erase_md =$mod_config->traverse('erasers',false,false)) instanceof I2CE_MagicDataNode) {
            $erase_md->erase();
        }
        if ($mod_config->is_parent('eraseVals')) {
            foreach ($mod_config->eraseVals as $erase_data) {
                if (!$erase_data instanceof I2CE_MagicDataNode || !$erase_data->is_scalar('path') || !$erase_data->is_parent('values')) {
                    I2CE::raiseError("Bad erase data at $i");
                    continue;
                }
                $md_path = trim($erase_data->path);
                if (strlen($md_path)  ==0  || $md_path[0] != '/') {
                    continue;
                }
                $passed = true;
                if ($erase_data->is_parent('requirements')) {
                    foreach ($erase_data->requirements as $req) {
                        if (!$req instanceof I2CE_MagicDataNode || !$req->is_scalar('operator') || !$req->is_scalar('version') ) {
                            continue;
                        }
                        $passed &= I2CE_Validate::checkVersion($old_vers,  $req->operator, $req->version);
                    }
                }
                if (!$passed) {
                    continue;
                }
                $eraseNode = I2CE::getConfig()->traverse($md_path,false,false);
                if (!$eraseNode instanceof I2CE_MagicDataNode) {
                    I2CE::raiseError("data at $md_path already not present");
                    continue;
                }
                if (!$eraseNode->is_parent()) {
                    I2CE::raiseError("data at $md_path is not a parent node");
                    continue;
                }
                I2CE::raiseError("Erasing Values of magic data at " . $eraseNode->getPath(false));
                $good =true;
                foreach ($erase_data->values as $valData) {
                    if (!$valData instanceof I2CE_MagicDataNode) {
                        I2CE::raiseError("bad erase val data");
                        $good =false;
                        continue;
                    }
                    $locale = null;
                    if ($valData->is_scalar('locale') && $valData->locale) {
                        $locale = $valData->locale;
                    }
                    if (!$valData->is_scalar('val') || !$valData->val) {
                        I2CE::raiseError("No val set");
                        $good =false;
                        continue;
                    }   
                    $val = $valData->val;
                    if (!$locale) {
                        $locale = null;
                    }
                    $vals = $eraseNode->getAsArray(null,$locale);
                    $index = array_search($val,$vals);
                    if ($index === false ) {
                        //not there, so ignore
                        continue;
                    }
                    $valNode = $eraseNode->traverse($index,false,false);
                    if (!$valNode instanceof I2CE_MagicDataNode) {
                        I2CE::raiseError("Could not access $index when found $val");
                        $good = false;
                        continue;
                    }
                    I2CE::raiseError("Erasiing $locale $md_path/$index = $val");
                    if ($locale === null) {
                        $valNode->erase();
                    } else if ($eraseNode->is_translated($locale)) {                            
                        $valNode->removeTranslation($locale);
                    }
                }

                if (!$good) {
                    if (self::failedRequiredUpdate($shortname,$data,"Could not erase values of  magic data at " . $eraseNode->getPath(), $configurator)) {
                        return false;
                    } else {
                        continue;
                    }
                }
            }


        }
        if ( ($erase_md =$mod_config->traverse('eraseVals',false,false)) instanceof I2CE_MagicDataNode) {
            $erase_md->erase();
        }


    }

    protected static function failedRequiredUpdate($shortname,&$data,$msg, $configurator) {
        I2CE::raiseError("Failing on module $shortname: $msg");
        $config = I2CE::getConfig();
        $config->clearCache();
        if (isset($data['optional'][$shortname]) && $data['optional'][$shortname]) {            
            I2CE::raiseError("Optional module $shortname could not be enabled/upgraded.  Disabling it any modules requiring it");
            $mod_factory = I2CE_ModuleFactory::instance();
            $deps = $configurator->getDependsList($shortname);
            I2CE::raiseError("Optional module $shortname has dependencies:\n\t" . implode(',',$deps));
            foreach ($deps as $dep) {
                unset($data['requirements'][$dep]);
                unset($data['optional'][$dep]);
                if (!isset($config->config->data->$dep) ||!$mod_factory->isEnabled($dep)) {
                    continue;
                }
                $mod_factory->disable($dep);
            }                    
            return false;
        } else {
            I2CE::raiseError("Required module $shortname could not be enabled/upgraded.  Failing system update");
            return true;
        }
    }


    protected static function storeModuleMagicData($shortname,$old_vers,$new_vers,$mod_configurator, $imported) {
        $mod_storage = $mod_configurator->getStorage();
        if (!$mod_storage instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Expecting magic data");
            return false;
        }
        //processconfig data
        $storage = I2CE::getConfig();
        $merges = $mod_configurator->getMerges();
        foreach ($merges as $path=>$merge) {
            if ($storage->is_scalar($path)) {
                I2CE::raiseError("Trying to merge arrays into $path where target is scalar valued. Skipping");
                continue;
            }
            if ($mod_storage->is_scalar($path)) {
                I2CE::raiseError("Trying to merge arrays into $path where source is scalar valued. Skipping");
                continue;
            }
            $old_arr = $storage->getAsArray($path);
            $new_arr = $mod_storage->getAsArray($path);
            $mod_storage->__unset($path);
            if (!is_array($old_arr)) { //in case the target did not exist
                $old_arr = array();
            }
            if (!is_array($new_arr)) { //in case no values were set for the source
                $new_arr = array();
            }
            switch ($merge) {
            case 'uniquemerge':
                $new_arr = I2CE_Util::array_unique(array_merge($old_arr,$new_arr));
                break;
            case 'merge':
                $new_arr =array_merge($old_arr,$new_arr);
                break;
            case 'mergerecursive':
                I2CE_Util::merge_recursive($old_arr, $new_arr);
                $new_arr = $old_arr;
                break;
            }
            $storage->__unset($path);
            $storage->$path = $new_arr;
        }
        //we took care of all array merges.  anything that is left is an overwrite.
        foreach ($mod_storage as $k=>$v) {
            if ($k == 'config') { 
                //don't update config info that might be here. It's handled below.
                continue;
            }
            if (is_scalar($v) && $mod_storage->is_translatable($k) && (!$storage->is_parent($k)))  {
                $storage->setTranslatable($k);
                $translations = $mod_storage->traverse($k,true,false)->getTranslations();
                foreach ($translations as $locale => $trans) {
                    if (strlen($trans) == 0) {
                        continue;
                    }
                    $storage->setTranslation($locale,$trans,$k);
                }
            } else {
                $storage->$k->setValue($v,null,false);
            }
            if ($storage->$k instanceof I2CE_MagicDataNode) {
                //free up some memory.  
                $storage->$k->unpopulate(true);
            }
        }
        $storage->config->status->config_processed->$shortname = $new_vers; //set the config data as processed
        if (isset($storage->config->status->initialized->$shortname)) {
            $storage->config->status->initialized->$shortname = 1; //set the config data as processed
        }
        foreach ($imported as $locale=>&$data) {
            unset($data['old_vers']);
        }
        $storage->config->status->localized->$shortname = $imported;
        return true;
    }


    protected static function loadModuleMagicData($shortname,$file,$old_vers,$new_vers,$mod_configurator) {
        $mod_storage = $mod_configurator->getStorage();
        if (!$mod_storage instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Expecting magic data");
            return false;
        }
        $storage = I2CE::getConfig();
        $processed = '0';
        //$processed = $old_vers;
        $storage->setIfIsSet($processed, "/config/status/config_processed/$shortname");
        I2CE::raiseError("Previously processed config data for $shortname with version <= $processed. New version is $new_vers");
        $uptodate = I2CE_Validate::checkVersion($processed, '>=', $new_vers);
        $localized = array();
        $storage->setIfIsSet($localized, "/config/status/localized/$shortname",true);        
        $mod_configurator->setLocales(I2CE_Locales::getAvailableLocales());
        $imported = $mod_configurator->importLocalizedTemplates($localized);
        //I2CE::raiseError("Imported for $shortname:" . print_r($imported,true) . "\nfrom available locales:" . implode(',',I2CE_Locales::getAvailableLocales()) . "\nWith:" . print_r($localized,true));
        $outofdate_locales = array();
        foreach ($imported as $locale=>$data) {
            if (I2CE_Validate::checkVersion($data['old_vers'], '<', $data['vers'])) {
                $uptodate = false;
                $outofdate_locales[$locale] = $data;
            }
        }
        if ($uptodate && count($outofdate_locales) == 0) {
            I2CE::raiseError("Main config is up to date form $shortname and there are no out of date locales");
            $storage->config->status->localized->$shortname = $imported;
            return true;//main config is up to date, and there are no imported configs which are out of date
        }
        if ($shortname != $mod_configurator->processConfig($processed,true,false,$outofdate_locales)) {
            I2CE::raiseError("Unable to process config data for $shortname with locales: " . implode(',', $outofdate_locales));            
            return false;
        }
        foreach ($imported as $locale=>&$data) {
            unset($data['old_vers']);
        }
        if ((count($mod_storage) == 0) || (count($mod_storage) == 1 && isset($mod_storage->config))) {
            I2CE::raiseError("No data to update for $shortname");
            $storage->config->status->localized->$shortname = $imported;
            //no new data was loaded in the config files.  so we wont need to store magic data
            return true;
        }
        //there is stuff to store in magic data and we are good to go
        $msg = "Loaded in magic data for $shortname to memory";
        if (count($outofdate_locales) > 0) {
            $msg .= "\nThe data for locale(s) " . implode(',',array_keys($outofdate_locales)) . " was out of date";
        }
        I2CE::raiseError($msg);
        $storage->config->status->localized->$shortname = $imported;
        return $imported;
    }



    protected static function preUpgradeModule($shortname, $old_vers,$new_vers,$new_storage) {
        $mod_factory = I2CE_ModuleFactory::instance();        
        if (!$mod_factory->enable($shortname)) {  //enabling failed
            I2CE::raiseError("Could not enable $shortname");
            return false;
        }
        $module = $mod_factory->getClass($shortname);
        if ($module instanceof I2CE_Module && $old_vers !== null && !I2CE_Validate::checkVersion($old_vers, '=', '0')    && I2CE_Validate::checkVersion($old_vers , '<', $new_vers)) {
            //we already have this installed.   upgrade it
            I2CE::raiseError("Pre-Upgrading module $shortname from version $old_vers to $new_vers");
            if (!$module->pre_upgrade($old_vers,$new_vers,$new_storage)) {
                I2CE::raiseError("Could not upgrade module");
                return false;
            }
        }
        return true;
    }


    protected static function upgradeModule($shortname, $old_vers,$new_vers) {
        $module = I2CE_ModuleFactory::instance()->getClass($shortname);
        if ($module instanceof I2CE_Module 
                && $old_vers !== null && !I2CE_Validate::checkVersion($old_vers, '=', '0') 
                && I2CE_Validate::checkVersion($old_vers , '<', $new_vers) ) {
            //we already have this installed.   upgrade it
            I2CE::raiseError("Upgrading module $shortname from version $old_vers to $new_vers");
            if (!$module->upgrade($old_vers,$new_vers)) {
                I2CE::raiseError("Could not upgrade module");
                return false;
            }
        }
        return true;
    }

    protected static function postUpdateModule($shortname, $old_vers,$new_vers) {
        $module = I2CE_ModuleFactory::instance()->getClass($shortname);
        if ( $module instanceof I2CE_Module ) {
            //we already have this installed.   upgrade it
            I2CE::raiseError("Post Updating module $shortname from version $old_vers to $new_vers");
            if (!$module->post_update($old_vers,$new_vers)) {
                I2CE::raiseError("Could not post update module");
                return false;
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
