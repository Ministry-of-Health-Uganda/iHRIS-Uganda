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

$template_dir = "./translations/templates";
$locales = false;
$usage[] = 
    "[--template_dir=\$template_dir]: The directory to store .pot template files in\n"
    ."\tIf not set, we use $template_dir\n" ;
$usage[] = 
    "[--locales=\$locale1,\$locale2..\$localeN]: The locales we wish to translate for\n"
    . "\tIf not specified, it uses  every valid subdirectory of in the translations template dir\n";
$set_configs  = false;
require_once('translate_base.php');




foreach ($args as $key=>$val) {
    switch($key) {
    case 'locales':
        $locales = preg_split('/,/',$val,-1,PREG_SPLIT_NO_EMPTY);
        break;
    }   
}

if ($locales == false) {
    $locales = array();
    $files= glob($template_dir . DIRECTORY_SEPARATOR  . '*' . DIRECTORY_SEPARATOR  . '*.po');
    foreach ($files as $file) {
        $locale = basename($file, '.po');
        $locales[$locale] = true;
    }
    $locales = array_keys($locales);
}

$found_modules = getAvailableModules();
if (count($found_modules) == 0) {
    usage("No modules found");
}
if (!$locales || count($locales)==0) {
    usage("No valid locales specified");
}
I2CE::raiseError( "Updating translations locales:\n\t" . implode(',',$locales) );




foreach ($found_modules as $module=>$top_module) {
    $launchpad_name = launchpad($module);
    $pot_file = $template_dir . DIRECTORY_SEPARATOR . $launchpad_name . DIRECTORY_SEPARATOR . $launchpad_name . '.pot';
    $existing_template = loadPOT($pot_file);
    if (!is_array($existing_template) || count($existing_template) == 0) {
        continue; 
    }
    
    $vers = '';
    if (!I2CE::getConfig()->setIfIsSet($vers,"/config/data/$module/version") || strlen($vers) == 0) {
        I2CE::raiseError( "No module version found -- Skipping");
        continue;
    }
    $converted_template = array();
    foreach ($existing_template as $msg_id=>$data) {
        if ($msg_id != '') {
            $data['msgstr'] = solidifySource($data['msgstr']);
        }
        $converted_template[solidifySource($msg_id)] = $data;
    }
    $pot_contents =  createPOT($top_module,$module,$vers,$converted_template,true);  
    if (!is_string($pot_contents)) {
        continue;
    }
    if (!file_put_contents($pot_file , $pot_contents)){
        I2CE::raiseError("Could not write:" . $pot_file, E_USER_ERROR);
    }
    I2CE::raiseError("Wrote:" . $pot_file);

    foreach ($locales as $locale) {    
        $po_file = $template_dir   . DIRECTORY_SEPARATOR . $launchpad_name . DIRECTORY_SEPARATOR . $locale . '.po';
        if (!is_readable($po_file)) {
            //I2CE::raiseError($po_file  . " is not readable for $module");
            continue;
        }                
        $existing_translations = loadPOT($po_file);
        if (!is_array($existing_translations) || count($existing_translations) == 0) {
            continue;
        }
        $converted_translations = array();
        foreach ($existing_translations as $msg_id=>$data) {
            if ($msg_id != '') {
                $data['msgstr'] = solidifySource($data['msgstr']);
            }
            $converted_translations[solidifySource($msg_id)] = $data;
        }            
        $po_contents =  createPOT($top_module,$module,$vers,$converted_translations,true);  
        if (!is_string($po_contents)) {
            continue;
        }
        if (!file_put_contents($po_file , $po_contents)){
            I2CE::raiseError("Could not write:" . $po_file, E_USER_ERROR);
        }
        I2CE::raiseError("Wrote:" . $po_file);
    }
}

