<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 */
/**
 * Set up all the configuration variables that can be set for any I2CE installation.
 * 
 * The implementation include file will include this file.
 * @package I2CE
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get") && DIRECTORY_SEPARATOR != '\\') {
    $tz = ini_get('date.timezone');    
    if (!$tz && is_readable('/etc/timezone')) {
        $tz = trim(file_get_contents('/etc/timezone'));
    }
    if (!$tz) {
        die("Please set timezone in your php.ini");
    }
    @date_default_timezone_set($tz);
}

if (! file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR .  'I2CE.php' )) {
    clearstatcache();
    if (function_exists('apc_clear_cache')) {
        apc_clear_cache();
        apc_clear_cache('user');
    }
}
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' );
require_once  'I2CE.php';


/**
 * The autoload function is used to load class files when needed
 * 
 * This function will be used to load any class files when they
 * are required instead of in every file.
 * 
 * It searchs the configuration array for the common class directory
 * as well as the class directory specifically for this project.
 * @global array
 * @param string $class_name The name of the class being loaded
 */
function i2ce_class_autoload( $class_name ) {
    $class_file = I2CE::getfileSearch()->search( 'CLASSES', $class_name . '.php',false,true );


    $class_found = false;
    if ( $class_file ) {
        require_once($class_file);
        if (class_exists($class_name,false) || interface_exists($class_name,false)) {
            $class_found = true;
        }  else {
            I2CE::raiseError("Defintion for class {$class_name} is not contained in {$class_file}", E_USER_WARNING);
        }
    }
    if (!$class_found) {
        $classes = I2CE_ModuleFactory::callHooks('autoload_search_for_class',$class_name);
        $count = count($classes);
        foreach ($classes as $class) {
            if ((!is_string($class)) || strlen($class)  == 0) {
                continue;
            }
            if (false === eval($class)) {
                I2CE::raiseError("While looking for $class_name, could not parse: $class");
            }
            if (class_exists($class_name,false)) {
                $class_found = true;
                break;
            }
        }
    }
    if (!$class_found) {
        $debug = debug_backtrace();
        $msg =  "Cannot find the defintion for class ({$class_name})";
        if (array_key_exists(1,$debug) && array_key_exists('line',$debug[1])) {
            $msg .= "called from  line " .$debug[1]['line'] .' of file ' . $debug[1]['file'] ;
        }
        $msg .= "\nSearch Path is:\n" 
            . print_r(I2CE::getFileSearch()->getSearchPath('CLASSES'), true); 
//        I2CE::raiseError( $msg, E_USER_NOTICE);
    }
} 

spl_autoload_register('i2ce_class_autoload');

set_error_handler(array('I2CE','handleError'));


register_shutdown_function(array('I2CE','raiseError'));





# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
