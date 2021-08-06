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
$locales = false;
$usage[] = 
    "[--template_dir=\$template_dir]: The directory to store .pot template files in\n"
    ."\tIf not set, we use $template_dir\n" ;
$usage[] =     
     "[--archive_dir=\$archive_dir]: The archive consisting of all .po\n\tDefaults to $archive_dir\n"
    . "[--locales=\$locale1,\$locale2..\$localeN]: The locales we wish create .po files for\n";

$set_configs  = false;
require_once('translate_base.php');

@require_once ("Archive/Tar.php");
if (!class_exists('Archive_Tar')) {
    usage('Please install the PEAR Archive_Tar package');
}


foreach ($args as $key=>$val) {
    switch($key) {
    case 'locales':
        $locales = preg_split('/,/',$val,-1,PREG_SPLIT_NO_EMPTY);
        break;
    case 'archive_dir':
        $archive_dir = $val;
    }   
}


//get/verify locales are there.
if ($locales == false) {
    usage("Please specify locales");
}
$found_modules = getAvailableModules();
$launchpad_modules = array();
foreach ($found_modules as $mod=>$top_mod) {
    $launchpad_modules[ launchpad($mod) ] =$top_mod;
}
if (count($found_modules) == 0) {
    usage("No modules found");
}
if (count($locales)==0) {
    usage("No valid locales specified");
}
I2CE::raiseError( "Creating .po files for locales:\n\t" . implode(',',$locales) );

$basename = launchpad(basename(getcwd()));
foreach ($locales as $i=>$locale) {
    $files= glob(getcwd() . DIRECTORY_SEPARATOR . $template_dir  . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR  . $locale . '.po');
    $existing = array();
    foreach ($files as $file) {
        $existing[basename(dirname($file))] = realpath($file); //key is launchpad module name
    }
    if (count($existing) ==0) {
        I2CE::raiseError("WARNING: the locale $locale has no .po files   -- the created .po files wiil have no translations\n");
    } else {
        I2CE::raiseError("Using existing translations:\n\t" . implode(',', array_keys($existing)) . "\nThe rest will be empty of translations");
    }
    $archive = $basename . '-po-'  . $locale . '.tgz';
    $tar =  new Archive_Tar(getcwd() . DIRECTORY_SEPARATOR . $archive_dir . DIRECTORY_SEPARATOR . $archive);
    $tar->setErrorHandling(PEAR_ERROR_CALLBACK, array('I2CE','raiseError'));
    if (!$tar->create(array())) {
        usage("Could not create tar $archive in $archive_dir");
    }
    foreach ($existing as $l_mod => $file) {
        if (!array_key_exists($l_mod, $launchpad_modules)) {
            I2CE::raiseError("The module $l_mod has a .pot files but is not a module in the system -- skipping");
            continue;
        }
        if (!$tar->addModify($file,$l_mod,  dirname($file) )) {
            usage("Could not add $file");
        }        
        unset($launchpad_modules[$l_mod]);
    }
    if (count($launchpad_modules) > 0) {
        I2CE::raiseError("The following:\n\t" . implode(",", array_keys($launchpad_modules)) ."\ndid not have an existing .po file.  Creating a blank one");
    }
    foreach ($launchpad_modules as $l_mod => $top_mod) {
        $po = loadPOT(getcwd() . DIRECTORY_SEPARATOR . $template_dir . DIRECTORY_SEPARATOR . $l_mod . DIRECTORY_SEPARATOR . $l_mod . '.pot');
        if (!is_array($po) || count($po) < 1) {
            //I2CE::raiseError("Problem with .pot for $l_mod.  Skipping");  no translations
            continue;
        }
        $cs = $po['']['comments'][''];
        array_unshift($cs,"$locale translation for " . $basename );
        $po['']['comments'][''] = $cs;
        $po = createPOT($top_mod, $l_mod, false, $po);
        if (!$tar->addString($l_mod . DIRECTORY_SEPARATOR . $locale . '.po', $po)) {
            I2CE::raiseError("Could not add untranslated $l_mod");
        }

    }
    I2CE::raiseError("Created $archive in $archive_dir");
}