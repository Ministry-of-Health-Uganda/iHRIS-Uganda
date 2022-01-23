#!/usr/bin/php
<?php
/*
 * © Copyright 2012 IntraHealth International, Inc.
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
 * @copyright Copyright &copy; 2012 IntraHealth International, Inc. 
 * @version 1.0
 */


$templates_dir ="." . DIRECTORY_SEPARATOR . "translations" .  DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;


$usage[] = 
    "[--templates_dir=\$read_po_files]: Where  to read .po files from\n\tDefaults to $templates_dir\n";
$usage[] = 
    "[--rev=XXX]: revision number to merge from\n";
$set_configs  = false;

require_once ("translate_base.php");  
if (!$args['rev']) {
    usage("No revision specified");
}


getAvailableModules();
$changed_pos = array();
foreach ($found_modules  as $module=>$top_module) {
    $launch_module = launchpad($module);
    $pot_dir =  $templates_dir . DIRECTORY_SEPARATOR . $launch_module;
    if (!is_dir($pot_dir)) {
        if (!mkdir($pot_dir,0775,true)) {
            usage("Could not make $pot_dir");
        }    
    }
    $pot_file = $pot_dir . DIRECTORY_SEPARATOR . $launch_module . '.pot';
    if (!is_array($current_template = loadPOT($pot_file))) {
	continue;
    }
    $po_files = glob(dirname($pot_file) . DIRECTORY_SEPARATOR . "*.po");

    foreach ($po_files as $po_file) {
	if (!is_array($new_trans= loadPot($po_file))) { 
	    continue;
	}   
	$old_content = array();
	exec ("bzr cat -r " . $args['rev'] . " " . $po_file,$old_content);
	$old_content = $old_content;
	if (!is_array($old_trans = loadPotByContent($old_content))) {
	    continue;
	}
	$changed = 0;
	foreach ($current_template as $english_source=>$data) {
	    if (!array_key_exists($english_source,$old_trans) 
		|| !is_array($old_trans[$english_source])
		|| !array_key_exists('msgstr',$old_trans[$english_source])
		|| !trim($old_trans[$english_source]['msgstr'])) {
		continue;
	    } 
	    if (!array_key_exists($english_source,$new_trans) 
		|| !is_array($new_trans[$english_source])
		|| !array_key_exists('msgstr',$new_trans[$english_source])
		|| !trim($new_trans[$english_source]['msgstr'])) {
		echo "$po_file: " . $old_trans[$english_source]['msgstr'] . " missing ($english_source)\n";
		$changed++;

		$new_trans[$english_source] = $old_trans[$english_source];
	    }
	}
	if ($changed == 0 
	    || !prompt("Save  " . $po_file . " with $changed missing translations?",$save)
	    ) {
	    continue;
	} 
	if (!$content = createPOContent($new_trans)) {
	    echo("Could not create content for $po_file\n");
	    continue;
	}

	if (! file_put_contents($po_file,$content)) {
	    echo("Could not save $po_file\n");
	}
    }
}