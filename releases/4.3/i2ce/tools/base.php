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
 * Translate Templates
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2008, 2008 IntraHealth International, Inc. 
 * @version 1.0
 */




if (!isset($search_dirs) || !is_array($search_dirs)) {
    $search_dirs = array(getcwd(), getcwd() . DIRECTORY_SEPARATOR . 'sites' . DIRECTORY_SEPARATOR . '*');
}

$usage[] =
     "[--modules=\$module1,\$module2..\$moduleN]: The module(s) for which we wish  to operate on\n"
    . "\tIf not specified, it uses  every valid module\n";
$usage[] =
     "[--search_dirs=\$dir1,\$dir2]: Set the search directories for modules\n"
    . "\tIf not specified, we search " . implode(',',$search_dirs) . "\n";
$usage[] = 
     "[--limit_search=T/F]: Limit the module search results of found sub-modules of a top-level module to those that are real subdirectories of top-level's given directory\n"
    . "\tDefaults to T.\n";

if (!isset($booleans) || !is_array($booleans)) {
    $booleans[] = array();
}


$booleans['limit_search'] = true;

require_once("CLI.php");
require_once ("I2CE_MagicData.php");
require_once ("I2CE.php");
require_once ("I2CE_ModuleFactory.php");
require_once ("I2CE_Configurator.php");
require_once ("I2CE_FileSearch.php");




$storage = I2CE_MagicData::instance( "CLI_application" );
I2CE::setConfig($storage);
$mod_factory = I2CE_ModuleFactory::instance();
$configurator =new I2CE_Configurator($storage,false);


$found_modules = false;
function getAvailableModules() {
    global $configurator;
    global $modules;
    global $search_dirs;
    global $found_modules;
    global $booleans;
    if (is_array($found_modules)) {
        return $found_modules;
    }
    $found_modules = array();
    $bad_modules = array();
    foreach ($search_dirs as $dir) {
        foreach (glob($dir) as $d) {
            $d = realpath($d);
            I2CE::raiseError( "Searching $d");
            I2CE::setupFileSearch(array('MODULES'=>$d));
            $fileSearch = I2CE::getFileSearch();
            $top_module = $configurator->findAvailableConfigs($fileSearch,false);        
            if (!is_array($top_module) || count($top_module) != 1) {
                I2CE::raiseError( "WARNING:  no top-level module found for $dir -- Skipping.");
                continue;
            }
            $top_module = $top_module[0];
            I2CE::raiseError( "Found $top_module as top-level module for $d");
            $searchPath = $fileSearch->getSearchPath('MODULES',true);
            if ($booleans['limit_search']) {
                I2CE::raiseError( "Limiting search to $d");
                $avail_modules = $configurator->findAvailableConfigs($fileSearch,true ,$d);
            } else {
                $avail_modules = $configurator->findAvailableConfigs($fileSearch,true );
            }
            if (is_array($modules)) {
                $avail_modules = array_intersect($modules,$avail_modules);
            }
            foreach ($avail_modules as $m) {
                if (array_key_exists($m,$found_modules)) {
                    I2CE::raiseError( "WARNING: conflict with module $m.  Found more than once -- Skipping");
                    $found_modules[$m] = false;
                    $bad_modules[] = $m;
                } else {
                    $found_modules[$m] = $top_module;
                }
            }
        }
    }
    foreach ($bad_modules as $m) {
        unset($found_modules[$m]);
    }
    if (count($found_modules) == 0) {
        usage("No modules files found in this directory:\n\t" . implode("\n\t", $search_dirs) . "\n");
    }
    return $found_modules;
}

