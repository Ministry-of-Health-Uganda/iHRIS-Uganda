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
$archive_dir ="." . DIRECTORY_SEPARATOR . "translations" .  DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR;

$locale = false;
$usage[] =     
    "[--template_dir=\$template_dir]: The directory to store .po template files in\n"
    ."\tIf not set, we use $template_dir\n" 
    . "[--locale=\$locale]: The locale we wish to create a launchpad import for \n"
    . "<FILE>: The filename of the spreadsheet we are pulling translations from\n";

$set_configs  = false;
require_once('translate_base.php');


set_include_path( get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

require_once ("Structures/DataGrid/DataSource/Excel.php");
if (!class_exists('Structures_DataGrid_DataSource_Excel')) {
    usage('Structures_DataGrid_DataSource_Excel: sudo pear install -d --alldeps Structures_DataGrid_DataSource_Excel ');
}


foreach ($args as $key=>$val) {
    switch($key) {
    case 'archive':
        $archive = $val;
        break;
    case 'locale':
        $locale =$val;
        break;
    }
}


if (!$locale)  {
    usage("No locale specified");
}
if (count($arg_files) != 1) {
    usage("No spreadsheet specified");
}
reset($arg_files);
$spreadsheet_file = realpath(current($arg_files));
if (!$spreadsheet_file || !is_readable($spreadsheet_file)) {
    usage("Spreadsheet $spreadsheet_file is not readable\n");
}
I2CE::raiseError("Using spreadsheet: " . $spreadsheet_file);




if (count($found_modules) == 0) {
    usage("No modules found");
}
I2CE::raiseError( "Extractrting translation spreadsheet for locale $locale ");

$workbook = new Spreadsheet_Excel_Reader();
if (I2CE::pearError($workbook,"Could not create workbook", E_USER_ERROR)) {
    die();   
}
$workbook->setOutputEncoding('UTF-8');
$workbook->setUTFEncoder($encoder = 'mb');
if (I2CE::pearError($workbook->read($spreadsheet_file), "Could not read workbook", E_USER_ERROR)) {
    die();
}
$translations_array = array();

function IsLatin1($str) {  //http://www.php.net/manual/en/function.mb-detect-encoding.php#95962
    return (preg_match("/^[\\x00-\\xFF]*$/u", $str) === 1);
} 

foreach ($workbook->sheets as $k=>$data) {
    if (!array_key_exists('cells',$data) || !is_array($data['cells'])) {
        continue;
    }
    $trans = array();
    foreach ($data['cells'] as $row=>$cols) {
        if (!array_key_exists(2,$data['cells'][$row])|| !array_key_exists(1,$data['cells'][$row])) {
            unset ($data['cells'][$row]);
            continue;
        }
        $enc =  mb_detect_encoding($data['cells'][$row][2] . 'a' , 'UTF-8, ISO-8859-1, ASCII');  //http://www.php.net/manual/en/function.mb-detect-encoding.php#81936

        $trans[$data['cells'][$row][1]] = mb_convert_encoding($data['cells'][$row][2], 'UTF-8',$enc);
    }
    $translations_array[$workbook->boundsheets[$k]['name']] = $trans;
}





$found_modules = getAvailableModules();
$pot_array = array();
I2CE::raiseError("Found the following modules:\n\t" . implode(",", array_keys($found_modules)));;



foreach ($found_modules as $module=>$top_module) {
    $launchpad_name = launchpad($module);
    // $chops = array('ihris-','manage-','qualify-','common-','i2ce');
    // foreach ($chops as $chop) {
    //     if (substr($launchpad_name,0,strlen($chop)) == $chop) {
    //         $launchpad_name = substr($launchpad_name,strlen($chop));
    //     }
    // }
    $pot_file = $template_dir . DIRECTORY_SEPARATOR . $launchpad_name . DIRECTORY_SEPARATOR . $launchpad_name . '.pot';
    $existing_template = loadPOT($pot_file);
    if ($existing_template === false) {
        continue;
    }
    $pot_array[$module] = $existing_template;
}
if (count($pot_array) == 0) {
    I2CE::raiseError("No .pot files found" , E_USER_ERROR);
}

    

$overwrite = null;
foreach ($found_modules as $module=>$top_module) {
    if (!array_key_exists($module,$pot_array)) {
        continue;
    }
    $pot = $pot_array[$module];
    if (count($pot) <= 1) { 
        //nothing to translate
        continue;
    }    
    $vers = '';
    I2CE::getConfig()->setIfIsSet($vers,"/config/data/$module/version");
    if (strlen($vers) == 0) {
        I2CE::raiseError( "No module version found -- Skipping");
        return false;
    }
    $l_module = launchpad($module);
    $chops = array('ihris-','manage-','qualify-','common-','i2ce');
    $ws_module = $l_module;
    foreach ($chops as $chop) {
        if (substr($ws_module,0,strlen($chop)) == $chop) {
            $ws_module = substr($ws_module,strlen($chop));
        }
    }

    $l_file = $template_dir . DIRECTORY_SEPARATOR . $l_module . DIRECTORY_SEPARATOR . $locale .'.po';
    if (file_exists($l_file) && !prompt("Overwrite existing $locale.po files?", $overwrite)) {
        continue;
        
    }

    if (!array_key_exists( $ws_module, $translations_array) || !is_array($translations_array[$ws_module])) {
        I2CE::raiseError("$ws_module not found in worksheet but has a .pot file");
        continue;
    }
    $trans = $translations_array[$ws_module];
    $untrans = array();
    foreach ($pot as $msgid=>&$data) {
        if (strlen(trim($msgid)) == 0) {
            continue;
        }
        if (!array_key_exists($msgid,$trans) || !is_string($trans[$msgid])) {
            $untrans[] = $msgid;
            continue;
        }
        if (strlen(trim($trans[$msgid])) == 0) {
            $untrans[] = $msgid;
            continue;
        }
        $data['msgstr'] = $trans[$msgid];
    }
    if  (count($untrans) > 0) {
        I2CE::raiseError("$module has the following untranslated strings:" . print_r($untrans,true));
    }
    unset($data);
    $po_file =  createPOT($top_module,$module,$vers,$pot);  
    if (!is_string($po_file)) {
        continue;
    }
    if (!file_put_contents($l_file , $po_file)){
        I2CE::raiseError("Could not write:" . $l_file, E_USER_ERROR);
    }
    I2CE::raiseError("Wrote:" . $l_file);
    
    
}






