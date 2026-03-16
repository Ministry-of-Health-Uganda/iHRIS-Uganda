#!/usr/bin/php
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


$templates_dir ="." . DIRECTORY_SEPARATOR . "translations" .  DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;


$set_configs  = false;
require_once ("translate_base.php");  

$templates = getTranslatableDocuments(false);
$configs = getTranslatableConfigs(false);
foreach (array_keys($found_modules) as $module) {
    if ((!array_key_exists($module,$templates) || !is_array($templates[$module]))
        && (!array_key_exists($module,$configs) || !$configs[$module])) {
        I2CE::raiseError("No translatable templates or configs for $module");
        continue;
    }
    $lp_modules[] = launchpad($module);
    $modules[] = $module;
}
ksort($modules);


do {    
    $idx = chooseMenuIndex("Which module would you like to fixup a english source text for?" ,$lp_modules);
    $module = $modules[$idx];
    $lp_module = $lp_modules[$idx];

    if ((!array_key_exists($module,$templates) || !is_array($templates[$module]))
        && (!array_key_exists($module,$configs) || !$configs[$module])) {
        I2CE::raiseError("No translatable templates or configs for $module");
        continue;
    }
    $template_dir = $templates_dir . $lp_module . DIRECTORY_SEPARATOR;

    $module_vers = '';
    I2CE::getConfig()->setIfIsSet($module_vers,"/config/data/$module/version");
    if (strlen($module_vers) == 0) {
        I2CE::raiseError( "No module version found -- Skipping");
        continue;
    }

    $avail_locales = array();
    foreach (glob($template_dir . '*.po') as $avail_locale) {
        $avail_locales[basename($avail_locale,'.po')] = $avail_locale;
    }

    $pot_file = $template_dir . $lp_module . '.pot';
    if (!is_readable($pot_file)) {
        I2CE::raiseError($pot_file  . " is not readable for $module");
        continue;
    }                
    $old_translations = loadPOT($pot_file);
    $sources = array_keys($old_translations);
    foreach ($sources as $i=>$source) {
        if (strlen(trim($source)) == 0) {
            unset($sources[$i]);
            continue;
        }
    }
    $old_src = chooseMenuValue("Which string would you like to change?",$sources);
    do {
        $new_src = trim(ask("What is the new source?"));
    } while (!simple_prompt("Replace:\n\t$old_src\nto:\n\t$new_src"));

    if (simple_prompt("Would you like to preserve the existing translations in the .po files for the locales " . implode(",",array_keys($avail_locales)) . "?")) {
        foreach ($avail_locales as $avail_locale=>$avail_locale_file) {
            I2CE::raiseError("Updateing $lp_module's $avail_locale.po");
            $loc_translations = loadPot($avail_locale_file);
            $top_module = $found_modules[$module];
            $new_loc_translations = array();
            foreach ($loc_translations as $key=>$vals) {
                if ($key == $old_src) {
                    $key = $new_src;
                }
                $new_loc_translations[$key] = $vals;
            }
            writeOutPOT($avail_locale_file,$top_module,$module,$module_vers,$new_loc_translations);
        }
    }
    $new_translations = array();
    foreach ($old_translations as $key=>$vals) {
        if ($key == $old_src) {
            $new_translations[$key] = $new_src;
        } else {
            $new_translations[$key] = $key;
        }

    }
    $change_template = null;
    if (array_key_exists($module,$templates)) {
        foreach ($templates[$module] as $basePath=>$template_files) {  //basePath ends in en_US;
            foreach ($template_files as $template_file) {
                if (!prompt("Change template file $template_file?",$change_template)) {
                    continue;
                }
                $translations = $new_translations;
                $new_template = translateTemplate($template_file);
                if (false === $new_template) {
                    I2CE::raiseError( "WARNING: Could not translate $template_file");
                }
                if (!trim($new_template)) { //empty contents
                    continue;
                }
                file_put_contents($template_file,$new_template);
            }
        }
    }
    $config_file = null;
    if ( ! $storage->setIfIsSet($config_file,"/config/data/$module/file") ) {
        I2CE::raiseError( "No config file for $module -- Skipping");
        continue;
    }    
    if (!is_readable($config_file)) {
        I2CE::raiseError( "Config file $config_file is not readable. -- Skipping");
        continue;
    }    
    $dom = new DOMDocument();
    $dom = new DOMDocument('1.0','UTF-8');
    $dom->substituteEntities = false;
    $dom->encoding = 'UTF-8';
    $dom->preserveWhiteSpace = true;
    $dom->formatOuput=false;
    if (!$dom->load($config_file)) {
        I2CE::raiseError( "Could not load $config_file -- Skipping");
        continue;
    }

    $bump_module_vers = explode('.',trim($module_vers));
    end($bump_module_vers);
    $k = key($bump_module_vers);
    if ($k >= 3) {
        //sub-minor
        $bump_module_vers[$k]++;
        $bump_module_vers  = implode('.',$bump_module_vers);
    } else if ($k == 2) {
        $bump_module_vers .= '.0.1';
    } else if ($k == 1) {
        $bump_module_vers .= '0.0.1';
    } else {
        I2CE::raiseError("Bad module version $module_vers");
        continue;
    }


    $xpath = new DOMXPath($dom);
    $results = $xpath->query(
        '/I2CEConfiguration/configurationGroup//value[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"] ' .
//value nodes inherit locale
        '| /I2CEConfiguration/configurationGroup[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"]//value ' .
        '| /I2CEConfiguration/configurationGroup/descendant-or-self::node()[@locale="' . I2CE_Locales::DEFAULT_LOCALE . '"]//value' .
//                '| /I2CEConfiguration/configurationGroup//description[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"] ' .
//                '| /I2CEConfiguration/configurationGroup//displayName[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"] ' .
//           description and displayname nodes do not inherit locale so we don't clutter up the .pot files
//            '| /I2CEConfiguration/configurationGroup/descendant-or-self::node[@locale="' . I2CE_Locales::DEFAULT_LOCALE . '"]//description' .
//            '| /I2CEConfiguration/configurationGroup/descendant-or-self::node[@locale="' . I2CE_Locales::DEFAULT_LOCALE . '"]//displayName' .
        '| /I2CEConfiguration/metadata/description[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"]' .
        '| /I2CEConfiguration/metadata/displayName[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"]' .
        '| /I2CEConfiguration/metadata/category[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"]' 
        );
    if ($results->length == 0 ) {
        I2CE::raiseError( "No translatable strings found in $config_file -- Skipping");
        continue;
    }
    $changed_text = false;
    $has_trans = false;
    $already_changed = null;
    for ($i=0; $i < $results->length; $i++) {
        $node = $results->item($i);
        $text = trim($node->textContent);
        $key = false;
        if ($node->tagName == 'value' && ($node->parentNode->getAttribute('type') == 'delimited')) {                  
            list($key,$text) = explode(':',$text);                
        }
        $trans = translate($text);
        if ($trans === false) { 
            continue;
        } 
        if (!$already_changed && !simple_prompt("Update the config file $config_file with the new source text?")) {
            $changed_text = false;
            break;
        }
        $already_changed = true;
        //we have a translation. first make sure we can do a version bump on a value node
        if ($node->tagName == 'value') {
            $pNode  = $node->parentNode;
            $ver_results = $xpath->query('version',$pNode);
            if ($ver_results->length == 1) {
                //we have an existing version
                $verNode = $ver_results->item(0);
                while ($versNode->hasChildNodes()) {
                    $versNode->removeChild($versNode->firstChild);
                }
                $versNode->appendChild($dom->createTextNode($bump_module_version));
            } else {
                //create a version node to put before the first value node
                $val_results = $xpath->query('value',$pNode);
                if ($val_results->length == 0) {
                    I2CE::raiseError("Could not update $config_file due to problem in finding the first value node");
                    continue;
                }
                $versNode = $dom->createElement('version',$bump_module_vers);
                $pNode->insertBefore($versNode,$val_results->item(0));
            }

        }

        $has_trans = true;
        while ($node->hasChildNodes()) {
            $node->removeChild($node->firstChild);
        }
        if ($key !== false) {
            $trans = $key . ':' . $trans;
        }
        $node->appendChild($dom->createTextNode($trans));
    
    }        
    if (!$changed_text) {
        continue;
    }
    I2CE::raiseError( "Bumped $module_vers to $bump_module_vers on $config_file");
    $results = $xpath->query('/I2CEConfiguration/metadata/version');
    foreach ($results as $versNode) {
        while ($versNode->hasChildNodes()) {
            $versNode->removeChild($versNode->firstChild);
        }
        $versNode->appendChild($dom->createTextNode($bump_module_vers));
    }
    $new_config = $dom->saveXML();

    file_put_contents($config_file,$new_config);

} while (simple_prompt("Would you like to fixup another translation source string?"));