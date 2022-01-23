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
mb_internal_encoding("UTF-8");



if (!isset($categories) || !is_array($categories)) {
    //$categories = array('TEMPLATES','STATIC');    
    $categories = array('TEMPLATES');
}

if (!isset($set_categories) || $set_categories !== false) {
    $usage[] = 
        "[--categories=\$cat1,\$cat2]: The categories to search\n" .
        "\tIf not specificed we search " . implode(',', $categories) . "\n" ;
}
if (!isset($set_configs) || $set_configs !== false) {
    $usage[] = 
        "[--create-configs=T/F]  set to true to always create ./configs\n" .
        "\tdirectory and add to to config.xml if there are translatable strings.\n" ;
    $usage[] = 
        "[--overwrite-configs=T/F] set to true to always overwrite the translated\n".
        "\tconfig.xml\n"; 
}
$booleans['overwrite-configs'] = null;
$booleans['create-configs'] = null;
require_once("base.php");
require_once ("I2CE_Locales.php");



if (isset($set_categories) && $set_categories !== false && array_key_exists('categories',$args)) {
    $val = $args['categories'];
    $categories = preg_split('/,/',$val,-1,PREG_SPLIT_NO_EMPTY);
    foreach ($categories as &$cat) {
        $cat = strtoupper($cat);
    }
}


function launchpad($str) {
    $func = create_function('$c', 'return strtolower($c[1]) . "_" . strtolower($c[2]);');
    return strtr(strtolower(preg_replace_callback('/([a-z])([A-Z])/', $func, $str)),'_','-');
}



function getTranslatableConfigs($create) {
    global $found_modules;
    global $storage;
    global $booleans;
    getAvailableModules();
    $translatable = array();
    foreach ($found_modules as $module=>$top_module) {
        $file = null;
        if ( ! $storage->setIfIsSet($file,"/config/data/$module/file")) {
            I2CE::raiseError( "No config file for $module -- Skipping");
            continue;
        }
        if (!is_readable($file)) {
            I2CE::raiseError( "Config file ($file) for $module is not readable -- Skipping");
            continue;
        }
        $config_dirs = array();
        $storage->setIfIsSet($config_dirs, "/config/data/$module/paths/CONFIGS",true);
        if (count($config_dirs) >  1) {
            I2CE::raiseError( "Found more than one configs directory for $module. -- Skipping");
             continue;
        }
        $config_dir = dirname($file) . DIRECTORY_SEPARATOR . "configs";
        $config_dir_en = $config_dir . DIRECTORY_SEPARATOR . I2CE_Locales::DEFAULT_LOCALE;        
        if ((!$create)) {
            if (count($config_dirs) == 1  ) {
                //echo "Module $module has configuration translations\n";
                $translatable[$module] = $config_dir;                        
            }
            continue;
        }
        //check to see if there are translatable strings in the main config file

	$dom = new DOMDocument('1.0','UTF-8');
	$dom->substituteEntities = false;
	$dom->encoding = 'UTF-8';
	$dom->preserveWhiteSpace = true;
	$dom->formatOuput=false;

//        $dom = new DOMDocument();
        if (!$dom->load($file)) {
            I2CE::raiseError( "Could not load config file $file -- Skipiing");
        }
        $xpath = new DOMXPath($dom);
        $qry = 
            '/I2CEConfiguration/configurationGroup//value[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"] ' .
//value nodes inherit locale
            '| /I2CEConfiguration/configurationGroup/descendant-or-self::node()[@locale="' . I2CE_Locales::DEFAULT_LOCALE . '"]//value' .
//            '| /I2CEConfiguration/configurationGroup//description[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"] ' .
//            '| /I2CEConfiguration/configurationGroup//displayName[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"] ' .
//           description and displayname nodes do not inherit locale so we don't clutter up the .pot files
//            '| /I2CEConfiguration/configurationGroup/descendant-or-self::node[@locale="' . I2CE_Locales::DEFAULT_LOCALE . '"]//description' .
//            '| /I2CEConfiguration/configurationGroup/descendant-or-self::node[@locale="' . I2CE_Locales::DEFAULT_LOCALE . '"]//displayName' .
            '| /I2CEConfiguration/metadata/description[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"]' .
            '| /I2CEConfigurUation/metadata/displayName[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"]' .
            '| /I2CEConfiguration/metadata/category[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"]' ;
        $results = $xpath->query($qry );        
        if ($results->length == 0 ) { 
            continue;
        }                
        I2CE::raiseError("Module $module has configuration translations");
        if (count($config_dirs) == 0 ) {
            //we found some translation but no translation exists for congigs
            if (!prompt("There are translatable nodes (for the module $module), but no configurations  directory.\nShould we create one if needed:\n\t(as "
                        . $config_dir_en . ")\nand add it to the config file?",$booleans['create-configs'])) {
                continue;
            }
            //we need to (possibly) create the config directory
            if (!is_dir($config_dir_en)) {
                if (!mkdir($config_dir_en,0775,true)) {
                    I2CE::raiseError( "Could not make $config_dir_en for $module. -- Skipping");
                    continue;
                }
            }
        } else {
            if (!is_dir($config_dir_en) || !is_readable($config_dir_en)) {
                if ($create) {
                    if (!mkdir($config_dir_en,0775,true)) {
                        I2CE::raiseError( "Could not make $config_dir_en for $module. -- Skipping");
                        continue;
                    } 
                } else {
                    I2CE::raiseError( "Module $module has a configuration directory set in magic data as:\n\t$config_dir_en\n\tbut it does not exist or is not readable -- Skipping"); 
                    continue;
                }
            }
        } 
        //now we need to double check permissions that i messed up before
        chmod($config_dir,0775);
        chmod($config_dir_en,0775);

        //now create the config file in the default locale with thr translation strings. and make sure locale is only set at the top level configuration
        $config = $config_dir_en . DIRECTORY_SEPARATOR . basename($file);
        if (!file_exists($config) || prompt("The config file:\n\t($config for the module $module)\nalready exists.  Should we overwrite?", $booleans['overwrite-configs'])) {
            if (!createConfigFile($module,$results,$config,$dom)) {
                I2CE::raiseError( "Could not create $config for $module -- Skipping");
                continue;
            }
        }
        if (count($config_dirs) == 0) {
            //now we need to add the config directory to the existing config xml just after the version information and save it.
            $results = $xpath->query('/I2CEConfiguration/metadata/version');
            if ($results->length != 1) {
                I2CE::raiseError( "Weirdness in config file $file for $module -- Skipping");
                continue;
            }
            $versionNode = $results->item(0);
            $pathNode = $dom->createElement('path');
            $pathNode->setAttribute('name','configs');
            $pathNode->appendChild($dom->createElement('value','./configs'));
            if ($versionNode->nextSibling instanceof DOMNode) {
                $versionNode->parentNode->insertBefore($pathNode,$versionNode->nextSibling);
            } else {
                $versionNode->parentNode->appendChild($pathNode);
            }            
            $imports = $xpath->query('//*[@import_index]');
            for ($i=0; $i < $imports->length; $i++) {
                $imports->item($i)->removeAttribute('import_index');
            }
            $out = $dom->saveXML();
            if ( function_exists('tidy_get_output')) {
                $tidy = new tidy();
                $tidy_config = array(
                    'input-xml'=>true,
                    'output-xml'=>true,
                    'indent'=>true,
                    'wrap'=>0,
                    );
                $tidy->isXML();
                $tidy->parseString($out,$tidy_config,'UTF8');
                $tidy->cleanRepair();
                $out = tidy_get_output($tidy);
            }
            if (!file_put_contents($file,$out)) {
                I2CE::raiseError( "Could not save config file $file with updated configs directory");
                continue;
            }
        } 
        $translatable[$module] = $config_dir;        
    }
    if (count($translatable) > 0) {
        I2CE::raiseError( "The following modules has translatable configuration strings:\n\t" . implode(",",array_keys($translatable)) );
    } else {
        I2CE::raiseError("There were no modules found with translatable configuration strings\n");
    }
    return $translatable;
}



function createConfigFile($module,$results,$config, $dom) {
    global $storage;
    $trans = new DOMDocument('1.0','UTF-8');
    $trans->substituteEntities = false;
    $trans->encoding = 'UTF-8';
    $trans->preserveWhiteSpace = true;
    $trans->formatOuput=false;

    $trans->loadXML('<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE I2CEConfiguration SYSTEM "I2CE_Configuration.dtd">
<I2CEConfiguration>
</I2CEConfiguration>');
    $xpath = new DOMXPath($trans);
    $dom_xpath = new DOMXPath($dom);
    $dom_config = $dom_xpath->query('/I2CEConfiguration/configurationGroup');
    $i2ceconfig = $xpath->query('/I2CEConfiguration');
    if ($i2ceconfig->length != 1) {
        I2CE::raiseError("Yell at carl");
        return false;
    }
    $i2ceconfig=$i2ceconfig->item(0);
    $i2ceconfig->setAttribute('name',$module);
    if ($dom_config->length == 1) {
        $dom_config = $dom_config->item(0);
        foreach (array('path','config','name') as $attr) {
            if  ($dom_config->hasAttribute($attr)) {
                $i2ceconfig->setAttribute($attr,$dom_config->getAttribute($attr));
            }
        }        
    }
    $meta= $trans->createElement('metadata');
    $i2ceconfig->appendChild($meta);
    $version = $trans->createElement('version',$storage->config->data->$module->version);
    $meta->appendChild($version);
    //$trans->documentElement->appendChild($i2ceconfig);
    $trans->appendChild($i2ceconfig);
    $imports = array();
    $burned = false;
        
    for ($i=0; $i < $results->length; $i++) {
        $node = $results->item($i);
        if ($node->hasAttribute('import_index')) { 
            I2CE::raiseError("weird double import in $module config file. " . $node->tagName . ":" . $node->getAttribute('import_index') . "/" . count($import_index));
            return false;
        }
        $t_node = $node;
        if ($node->tagName == 'value') {
            // (for the moment) we only alow the following types to be translatable:
            // single string
            // single delimited
            // many delimited
            $parent = $node->parentNode;
            if ($parent->hasAttribute('type')) {
                $type = $parent->getAttribute('type');
            } else {
                $type = 'string';
            }
            if ( $parent->hasAttribute('values') && ($parent->getAttribute('values') == 'many')){
                if ($type != 'delimited') {
                    //skip this node
                    continue;
                }
            } else { //values ='single' or is implicitly single
                if (!  ( ( $type == 'string') || ( $type =='delimited'))) {
                    //skip this node
                    continue;
                }
            }
        }
        $import = $trans->importNode($node,true); //import the <value/description/displayNam/category> node with its attributes and contents
        if ($import->tagName == 'value') {
                //just to keep it a bit cleaner -- values inhereit locale and we will set the top level inheritence
                $import->removeAttribute('locale');
        }
        $locale = false;
        $t_import = null;
        $cat = null;
        $desc = null;
        while ($t_node instanceof DOMElement) {
            if ( (!$locale) && $t_node->hasAttribute('locale')) {
                if ($t_node->getAttribute('locale') != I2CE_Locales::DEFAULT_LOCALE) {
                    //double check that this node is really en_US:
                    //for example we could have done <configurationGroup locale='en_Us'><cofiguration locale='fr_FR'><value>me</value>
                    continue 2; //continue to the next <value/description/displayName/category> node
                } else {
                    $locale = true;
                }
            } 
            if ($t_import instanceof DOMNode) {
                if ($t_node->hasAttribute('import_index')) {
                    //we already imported this node.  
                    $imports[$t_node->getAttribute('import_index')]->appendChild($t_import);
                    break; //out of while
                }    
                $import->appendChild($t_import);
            }
            $t_node->setAttribute('import_index',count($imports));
            $imports[] = $import;
            if ($t_node->parentNode->tagName == 'I2CEConfiguration') { 
                //$import and $t_node are the top-level configurationGroup.  this should only happen once
                $import->setAttribute('locale', I2CE_Locales::DEFAULT_LOCALE);
                $i2ceconfig->appendChild($import);                            
                if ($burned) { 
                    I2CE::raiseError( "Burned in $module ");
                    return false;
                }
                $burned = true;
                break; //out of while
            } else if ($t_node->parentNode->tagName == 'metadata') {
                $import->setAttribute('locale',I2CE_Locales::DEFAULT_LOCALE);
                switch ($import->tagName) {
                case 'description':
                    $desc = $import;
                    $meta->insertBefore($desc,$version);
                    break 2; //out of while
                case 'category':
                    $cat = $import;
                    if ($desc instanceof DOMElement) {
                        $meta->insertBefore($cat,$desc);
                    } else {
                        $meta->insertBefore($cat,$version);
                    }
                    break 2; //out of while
                case 'displayName':
                    $disp = $import;
                    if ($cat instanceof DOMElement) {
                        $meta->insertBefore($disp,$cat);
                    } else if ($desc instanceof DOMElement) {
                        $meta->insertBefore($disp,$desc);
                    }  else {
                        $meta->insertBefore($disp,$version);
                    }
                    break 2; //out of while
                default:
                    I2CE::raiseError( "Unexpected tag " . $import->tagName . " in metadata");
                    return false;
                }
            }
            $t_node = $t_node->parentNode;
            if (!$t_node instanceof DOMElement) {
                I2CE::raiseError( "Expected element but not gotten in $module.  ");
                return false;
            }
            $t_import = $import;
            $import = $trans->importNode($t_node,false);                            
            foreach (array('name','path','values','type','config') as $attr) {
                if ($t_node->hasAttribute($attr)) {
                    $import->setAttribute($attr,$t_node->getAttribute($attr));
                }
            }

            //check for <status>version:XXXX</status>
            $statii = $dom_xpath->query('./status',$t_node);
            for ($j=0; $j < $statii->length; $j++) {
                $status = $statii->item($j);                            
                if (preg_match('/^version:.+$/',trim($status->textContent), $matches)) {
                    $import->appendChild($trans->createElement('status',trim($status->textContent)));
                }
            }
        }
    }
    if (!$trans->documentElement->hasChildNodes()) {
        //should be exactly one, the top-level configurationGroup
        I2CE::raiseError( "Was not able to extract translatable strings from $file for $module ");
        return false;
    }
    $results = $xpath->query('/I2CEConfiguration/configurationGroup');
    if ($results->length > 0) {
        $version_node = $trans->createElement('version',$storage->config->data->$module->version);
        $results->item(0)->insertBefore($version_node,$results->item(0)->firstChild);
    }


    
    $out = $trans->saveXML();
    if (function_exists('tidy_get_output')) {
        $tidy = new tidy();
        $tidy_config = array(
            'input-xml'=>true,
            'output-xml'=>true,
            'indent'=>true,
            'wrap'=>0,
            );
        $tidy->isXML();
        $tidy->parseString($out,$tidy_config,'UTF8');
        $tidy->cleanRepair();
        $out = tidy_get_output($tidy);
    }
    I2CE::raiseError("Creating $config");
    if (!file_put_contents($config,$out)) {
        I2CE::raiseError( "Could not save $config for $module  ");
        return false;
    }
    return true;
}


function getTranslatableDocuments($show_bad = true) {   
    global $categories;
    global $found_modules;
    I2CE::raiseError( "Getting Translate-able Documents");
    getAvailableModules();
    I2CE::raiseError("Will attempt to  template files for the following modules:\n\t" . implode(",",array_keys($found_modules)));
    $factory = I2CE_ModuleFactory::instance();
    $templates = array();
    foreach ($found_modules as $module=>$top_module) {
        I2CE::setupFileSearch(); //reset the file search.
        $fileSearch = I2CE::getFileSearch();
        $good_paths = array();
        $bad_paths = array();
        foreach ($categories as $cat) {
            $fileSearch->setPreferredLocales($cat,I2CE_Locales::DEFAULT_LOCALE); //only search the en_US locale    
            $factory->loadPaths($module,$cat,true,$fileSearch); //load all template paths
            $ordered_paths = $fileSearch->getSearchPath($cat,true); //get the paths found with their localization;
            if (count($ordered_paths) == 0) {
                //echo "\tNo $cat directories for $module. -- Skipping\n";                
                continue;
            }
            foreach ($ordered_paths as $paths) {
                foreach ($paths as $path=>$locale) {
                    if ($locale !== I2CE_Locales::DEFAULT_LOCALE) {
                        //should not happen.
                        var_dump($locale);
                        die("Yell at Carl -- you have locale $locale instead of " . I2CE_Locales::DEFAULT_LOCALE .  "\n");
                    }
                    $dir = basename($path);
                    if ($dir != I2CE_Locales::DEFAULT_LOCALE) {
                        $bad_paths[] = $path;
                    } else {
                        $good_paths[$path] = I2CE_Locales::DEFAULT_LOCALE;
                    }
                }
            }
        }
        if ($show_bad && count($bad_paths)  > 0) {
            I2CE::raiseError( "The following template paths for $module were not localized:\n\t" . implode("\n\t",$bad_paths) );
        }
        if (count($good_paths) == 0) {
            //echo "\tNo localized template files for $module -- Skipping\n";
            continue;
        }
        foreach ($good_paths as $path=>$locale) {
            $rec_path = $path . DIRECTORY_SEPARATOR . '**'; //do a recursive search
            $files = $fileSearch->resolve(array('/^.*\.html?$/'),array($rec_path=>I2CE_Locales::DEFAULT_LOCALE),true);    
            if (is_array($files) && count($files) > 0) {
                $templates[$module][$path] =  $files;
            }
            
        }
    }
    if (count($templates) == 0) {
        I2CE::raiseError( "None of the modules available are setup with localized tempaltes.  Nothing to do.");
    } else {
        I2CE::raiseError("The following modules has translatable templates:\n\t" . implode(",",array_keys($templates)) );
    }
    return $templates;
}




//this is copied from I2CE_Template.  
function wrapHTMLinUTF8($text ) {
    if (preg_match("/^(.*?<\/?head((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>)(.*)/ism", $text, $matches)) {        
        //there is an existing head tag
        $text = $matches[1] . "\n" . '<meta http-equiv="content-type" content="text/html; charset=utf-8"/>' . $matches[6];
    } else {
        //no head.  see if there is an html or not
        if (preg_match("/^(.*?<\/?html((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>)(.*)/ism", $text, $matches)) {
            //there is an html tag, but no head tag
            $text = $matches[1] . '<head><meta http-equiv="content-type" content="text/html; charset=utf-8"/></head>' . $matches[7];
        } else {
            //no html tag, no head tag.  
            //Check for a body tag
            if (preg_match('/\<\s*body/m', $text)) {
                $text =  '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"/></head>' . $text . "</html>";                    
            } else {
                $text = '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"/></head><body>'.$text.'</body></html>';
            }
        }
    }
    return $text;
}


function _read($fh , $num = 1, $big = true) {
    if ($big) {
        $read = unpack('n' . $num,fread($fh,2*$num));  //read in two words
        $ret = 0;
        for ($i =1; $i <= $num; $i++) {
            $ret = ($ret << 16) | $read[$i];
        }
    } else {
        $read = unpack('v' . $num ,fread($fh,2*$num));  //read in two words
        $ret = 0;
        for ($i =$num; $i > 0; $i--) {
            $ret = ($ret << 16) | $read[$i];
        }
    }
    return $ret;
}




function loadMO($file) {
    $translations = array();
    if (is_resource($file)) {
        $fh = $file;
        fseek($fh,0);
    } else if (is_string($file)) {
        $fh = fopen($file,'r');
        if ($fh === false) {
            I2CE::raiseError( "Could not read $file");
            return $translations;
        }
    } else {
        I2CE::raiseError( "Don't know how to read what you gave me");
        return $translations;
    }
    //http://www.gnu.org/software/autoconf/manual/gettext/MO-Files.html
    $big_mo_magic =  (0x9504 << 16) |  (0x12DE) ;   
    $litle_mo_magic = (0xDE12 << 16) | (0x0495) ;
    $read_magic = _read($fh,2);
    if ($read_magic == $big_mo_magic) {
        // "big endian"
        $big = true;
    } else if ($read_magic == $litle_mo_magic) {
        //"litle endian ";
        $big = false;
    } else {
        I2CE::raiseError( 
            "Cannot determin magic number 0x" . dechex($read_magic) . " as mo file for $file\n".
            "\tBig magic number 0x" . dechex($big_mo_magic) . "\n".
            "\tLitle magic number 0x" . dechex($litle_mo_magic));
        fclose($fh);
        return $translations;
    }
    //now check the revision number is zero
    //echo "At byte " . ftell($fh) . "\n";
    if (_read($fh,2) != 0) {
        I2CE::raiseError( "Wrong revision number -- not zero");
        fclose($fh);
        return $translation;
    }
    $num = _read($fh,2,$big);
    $orig_offset = _read($fh,2, $big);

    $trans_offset = _read($fh,2, $big);
    fseek($fh,$orig_offset);
    $orig_offsets = array();
    for ($i=0; $i < $num; $i++) {
        $orig_offsets[] = array(_read($fh,2,$big), _read($fh,2,$big)); //length and offset        
    }
    fseek($fh,$trans_offset);
    $tran_offsets = array();
    for ($i=0; $i < $num; $i++) {        
        $tran_offsets[] = array(_read($fh,2,$big), _read($fh,2,$big)); //length and offset        
    }
    for ($i=0; $i < $num; $i++) {
        if ($orig_offsets[$i][0] <= 0) {
            continue;
        }
        fseek($fh,$orig_offsets[$i][1]);
        $orig = mb_convert_encoding(fread($fh,$orig_offsets[$i][0]), 'UTF-8', 'ASCII');
        
        fseek($fh,$tran_offsets[$i][1]);
        $tran = explode(0,fread($fh,$tran_offsets[$i][0])); //explode for plural forms
        if (count($tran) == 1) {
            $tran = $tran[0];
        }
        $translations[$orig]=$tran;
    }
    fclose($fh);
    return $translations;
}


function loadPOT($file) {
    if (!file_exists($file)) {
        return array();
    }
    @$contents = file($file);
    if ($contents === false) {
        if (file_exists($file)) {
            I2CE::raiseError("Could not read existing template file $file");
            return false;
        }
        return array();
    }
    I2CE::raiseError( "reading existing template file $file");
    return loadPOTByContent($contents);
}

function loadPOTByContent($contents){
    $string_template = array();
    $in_msgid = false;
    $in_msgid_pl = false;
    $in_msgstr = false;
    $in_msg = false;
    $msg_id = null;
    $msg_id_pl = null;
    $msg_str = null;
    $comments = array();
    $contents[] = ""; //add an empty line to close off the last message in case there was no trailing new line in the file.
    foreach ($contents as $i=>$line) {
        $line = rtrim($line);
        if (strlen($line) == 0) {
            if ($in_msg) { //we are closing up the previous message
                $in_msg = false;
                if ($msg_id !== null) {
                    if  (($msg_id  !== null) && ($msg_str === null)) {
                        I2CE::raiseError( "Mesage id $msg_id given but has no msgstr at line $i: $line");
                        return false;
                    }                               
                    $msg_id = strtr($msg_id,array('\\"'=>'"','\\n'=>"\n"));
                    $msg_str = strtr($msg_str,array('\\"'=>'"','\\n'=>"\n"));
                    if (array_key_exists($msg_id,$string_template)) {
                        I2CE::raiseError("Warning duplicated msgid <$msgid>. Skipping");
                    } else {
                        $string_template[$msg_id] = array('comments'=>$comments,'msgstr'=>$msg_str);
                        if ($msg_id_pl) {
                            $string_template[$msg_id]['msgid_plural'] = strtr($msg_id_pl,array('\\"'=>'"','\\n'=>"\n"));
                        }
                    }
                } else {
                    if (count($comments) > 0) {
                        I2CE::raiseError("Warning: trailing comments found");
                    }
                }
                $comments = array();
                $msg_id = null;
                $msg_id_pl = null;
                $msg_str = null;
                $in_msgstr = false;
                $in_msgid = false;
                $in_msgid_pl = false;
            }
            continue;
        } else if (substr($line,0,5) == 'msgid') {
            $in_msg = true;
            if ($in_msgstr) {
                I2CE::raiseError( "Found msgid tag while looking in msgstr at line $i: $line");
                return false;
            }       
            if ($in_msgid) {
                I2CE::raiseError( "Found msgid tag while looking in msgid at line $i: $line");
                return false;
            }
            if (!preg_match('/^\s+"(.*)"\s*$/',substr($line,5), $matches)) {
                I2CE::raiseError( "Could not find message string at $i: $line");
                return false;
            }
            $in_msgid = true;
            $msg_id .= $matches[1];
        } else if (substr($line,0,5) == 'msgid_plural') {
            if (!$in_msg) {
                I2CE::raiseError( "Found message id plural string at line $i but not in a message: $line");
                return false;
            }
            if ($in_msgstr) {
                I2CE::raiseError( "Found msgid_plural tag while looking in msgstr at line $i: $line");
                return false;
            }       
            if ($in_msgstr_pl) {
                I2CE::raiseError( "Found msgid_plural tag while looking in msgstr_plural at line $i: $line");
                return false;
            }       
            if ($msgid=== null) {
                I2CE::raiseError( "Found msgid_plural string at line $i but no message id has been set: $line");
                return false;
            }            
            if (!preg_match('/^\s+"(.*)"\s*$/',substr($line,12), $matches)) {
                I2CE::raiseError( "Could not find message string at $i: $line");
                return false;
            }
            $in_msgid_pl = true;
            $msg_id_pl .= $matches[1];            
        } else if (substr($line,0,6) == 'msgstr') {
            if (!$in_msg) {
                I2CE::raiseError( "Found message string at line $i but not in a message: $line");
                return false;
            }
            if ($in_msgstr) {
                I2CE::raiseError( "Found msgid tag while looking in msgstr at line $i: $line");
                return false;
            }                   
            if ($msg_id === null) {
                I2CE::raiseError( "Found message string at line $i but no message id has been set: $line");
                return false;
            }
            $in_msgid = false;
            $in_msgid_pl = false;
            $in_msgstr = true;
            if (!preg_match('/^\s+"(.*)"\s*$/',substr($line,6), $matches)) {
                I2CE::raiseError( "Could not find message string at $i: $line");
                return false;
            }
            $msg_str .= $matches[1];
        } else if (($line[0] == '#')) {
            $in_msg = true;
            if ($in_msgid) {
                I2CE::raiseError( "Unexpected comment ($line) found  on line $i while in msgid");
                return false;
            } else if ($in_msgid_pl) {
                I2CE::raiseError( "Unexpected comment ($line) found  on line $i while in msgid_plural");
                return false;
            } else if ($in_msgstr) {
                I2CE::raiseError( "Unexpected comment ($line) found  on line $i while in msgstr");
                return false;
            }
            //'.' comments are "extracted comments"
            //''  comments are tranlator comments
            //':' comnets are refeernce comments
            //',' comments are format/flag comments and are treated specially
            if (strlen($line) > 1) {
                $prefix = $line[1];
                if (in_array($prefix,array('.',',',':','~'))) {
                    $comment = substr($line,2);
                } else if ($prefix == ' ') {
                    $prefix = '';
                    $comment = substr($line,2);
                } else {
                    I2CE::raiseError( "Unreconized comment style on line $i: $line"); 
                    return false;
                }
            } else {
                $prefix = '';
                $comment = '';
            }
            $comments[$prefix][] = $comment;
        } else if (preg_match('/^\s*"(.*)"\s*/',$line,$matches)) {
            if ($in_msgstr) {
                $msg_str .= $matches[1];
            } else if ($in_msgid) {
                $msg_id .= $matches[1];
            } else if ($in_msgid_pl) {
                $msg_id_pl .= $matches[1];
            } else {
                I2CE::raiseError( "Unexpected string on line $i: $line");
                return false;
            }
        } else {
            I2CE::raiseError( "Unreconized line $i: $line\n"); 
            return false;
        }
    }
    return $string_template;
}

function writeOutPOT($file,$top_module,$module,$module_vers,$strings_template) {   
    $out = createPOT($top_module,$module,$module_vers,$strings_template);
    if ($out === false) {
        return false;
    }
    I2CE::raiseError("Looking at $file");
    if (file_exists($file)) {
        I2CE::raiseError("Looking at $file -- exists");
        //first let's check to see if the only difference is the date.  if so then don't make any changes.
        if ( ($in = file_get_contents($file))) {
            $reg = '/"POT-Creation-Date:.*/';
            $t_out = preg_replace($reg,'',$out);
            $in = preg_replace($reg,'',$in);
            if (strcmp($in,$t_out) === 0) {
                I2CE::raiseError(basename($file) . ' has not changes');
                return true;
            }
        }
        if (!unlink($file)) {
            I2CE::raiseError("Could not remove existing .pot file for $module and there are not strings to translate");
            return false;
        }
    }
    if (is_string($out)) {
        if (!file_put_contents($file,$out)) {
            I2CE::raiseError( "WARNING: Unable to create translation template at $file\n");
            return false;
        }
    }
    return true;
}




function createPOT($top_module,$module,$module_vers,$strings_template, $preserve_date = false) {
    if (!$module) {
        I2CE::raiseError( "No module");
        return false;
    }
    if ($module_vers) {
        if (!array_key_exists('',$strings_template)) {
            $comments = array(
                ''=> array(            
                    "Translation Template of $top_module module (". $module ." Version " . $module_vers . ")",
                    "Copyright (C) " . date("Y") . " <hris@capacityproject.org> Capacity Project partnership via IntraHealth International, Inc."
                    ),
                );
            $msgstr  = "Project-Id-Version: $top_module@$module $module_vers\n";
            $msgstr .= "POT-Creation-Date: " . date("Y-m-d H:iO") . "\n";
            $msgstr .= "Last-Translator: Intrahealth <hris@capacityproject.org>\n";
            $msgstr .= "Language-Team: Intrahealth <hris@capacityproject.org>\n";
            $msgstr .= "MIME-Version: 1.0\n";
            $msgstr .= "Content-Type: text/plain; charset=utf-8\n";
            $msgstr .= "Content-Transfer-Encoding: 8bit\n";
            $msgstr .= "Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;\n";
            $strings_template = array_merge(
                array(''=>  array(
                          'comments'=>$comments,
                          'msgstr'=>$msgstr
                          )),
                $strings_template);
        } else {
            $msgstr = explode("\n",$strings_template['']['msgstr']);        
            foreach ($msgstr as &$str) {
                if (!$preserve_date) {
                    $str = preg_replace('/^\s*POT\-Creation\-Date.*$/i',"POT-Creation-Date: " . date("Y-m-d H:iO") , $str);
                }
                $str = preg_replace('/^\s*Project\-Id\-Version.*$/i',"Project-Id-Version: $top_module@$module $module_vers", $str);
            }
            $strings_template['']['msgstr'] = implode("\n",$msgstr) ;
            if (array_key_exists('comments', $strings_template[''])
                &&is_array($strings_template['']['comments'])
                &&array_key_exists('',$strings_template['']['comments'])
                &&is_array($strings_template['']['comments'][''])) {
                foreach ($strings_template['']['comments'][''] as &$str) {
                    $str = preg_replace('/^\s*Translation Template of.*$/i',"Translation Template of $top_module module (". $module ." Version " . $module_vers . ")", $str);
                }
            }
        }
    }
    I2CE::raiseError("Module $module has " . count($strings_template) . " strings");
    if (count($strings_template) == 1) {
        //there are no strings to translate.
        return true;
    }
    $out =  createPOContent($strings_template);
    if (!$out) {
	return true;
    }
    return $out;
}


function createPOContent($strings_template) {
    $out = '';
    $has_strings = false;
    foreach ($strings_template as $string=>$data) {
        if (!is_string($string)) {
            $string = $string . '';
        }
        $is_meta = (strlen(trim($string)) == 0);
        if (!$is_meta) { //this is not the meta-data string 
	    $has_strings = true;
        }
        if (is_string($data)) {
            $data = array('comments'=>array(''=>explode("\n",$data)),'msgstr'=>'');
        } else if (is_bool($data)) {
            $data = array('comments'=>array(),'msgstr'=>'');
        }
        if (!is_array($data['comments'])) {
            $data['comments'] = array();
        }
        if (!array_key_exists(',',$data['comments']) || !is_array($data['comments'][',']) || count($data['comments'][',']) == 0) {
            if ($is_meta) {
                $data['comments'][','] = array('fuzzy');
            } else {
                $data['comments'][','] = array('no-c-format');
            }
        }
        //'.' comments are "extracted comments"
        //''  comments are tranlator comments
        //':' comnets are refeernce comments
        //',' comments are format/flag comments 
        foreach (array('','.',':',',') as $prefix) {
            if (!array_key_exists($prefix, $data['comments'])) {
                continue;
            }
            $comments = $data['comments'][$prefix];
            if (is_string($comments)) {
                $comments = explode("\n",trim($comments));
            }
            if (!is_array($comments)) {
                $comments = array();
            }
            foreach ($comments as $comment) {
                if (strlen($comment)>0 && $comment[0] != ' '){ 
                    $comment = ' ' . $comment;
                }
                $out .= '#' . $prefix .   $comment  . "\n";
            }
        }
        $out .= prepareString('msgid',$string);
        if (array_key_exists('msgid_plural',$data) && $data['msgid_plural']) {
            $out .= prepareString('msgid_plural',$data['msgid_plural']);
        }
        if (array_key_exists('msgstr',$data) && $data['msgstr']) {
            $msgstr = $data['msgstr'];
        } else {
            $msgstr = '';
        }
        $out .= prepareString('msgstr',$msgstr);
        $out .= "\n";
    }
    if (!$has_strings) {
	return false;
    }
    return $out;

}


function prepareString($key,$string) {
    $out = '';
    if (strpos($string,"\n") !== false) {
        $strings = explode("\n",$string);        
        $out .= "$key \"\"\n";
        $num = count($strings);
        for ($i = 0; $i < $num -1; $i++) {
            $out .= "\"" . addcslashes($strings[$i],'\\"'). "\\n\"\n";
        }
        $out .= "\"" . addcslashes($strings[$num -1],'\\"'). "\"\n";
    } else {
        $out .= "$key \"" . addcslashes($string,'\\"') . "\"\n";
    }
    return $out;    
}


//thanks to: http://stackoverflow.com/questions/2934563/how-to-decode-unicode-escape-sequences-like-u00ed-to-proper-utf-8-encoded-cha
function replace_unicode_escape_sequence($match) {
    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
}

//thanks  http://stackoverflow.com/questions/1805802/php-convert-unicode-codepoint-to-utf-8
function codepoint_utf8($num)
{
    if($num<=0x7F)       return chr($num);
    if($num<=0x7FF)      return chr(($num>>6)+192).chr(($num&63)+128);
    if($num<=0xFFFF)     return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
    if($num<=0x1FFFFF)   return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128).chr(($num&63)+128);
    return '';
}
function uniord($c)
{
    $ord0 = ord($c{0}); if ($ord0>=0   && $ord0<=127) return $ord0;
    $ord1 = ord($c{1}); if ($ord0>=192 && $ord0<=223) return ($ord0-192)*64 + ($ord1-128);
    $ord2 = ord($c{2}); if ($ord0>=224 && $ord0<=239) return ($ord0-224)*4096 + ($ord1-128)*64 + ($ord2-128);
    $ord3 = ord($c{3}); if ($ord0>=240 && $ord0<=247) return ($ord0-240)*262144 + ($ord1-128)*4096 + ($ord2-128)*64 + ($ord3-128);
    return false;
}

function solidifySourcePieces($text) {
    mb_regex_encoding( 'UTF8' );
    $nbsp = codepoint_utf8(160); //160 is unicode codepoint A0 which is utf-8 c2a0 which is nbsp;
    $text = str_replace($nbsp,' ',$text);
    $leftstrip = preg_quote(':;[]|\\~@#$^&*_-+=,');
    $rightstrip = preg_quote(':;[]|\\~@#^&*_-+=,');
    if (!preg_match("/^([\s$leftstrip]*)(.*?)([\s$rightstrip]*)$/s",$text,$matches)) {
	//echo "<<$text>>\m";
        //this really shouldn't happen.
        $text = preg_replace("/\s+/s"," ",trim($text));  //no weird characters.  just 
        return array('',$text,'');
    } 
    $text = $matches[2];
    $text = preg_replace("/\s+/su"," ",trim($text));
    return array($matches[1],$text,$matches[3]);
}
function solidifySource($text) {
    list($left,$text,$right) = solidifySourcePieces($text);
    return $text;
    //this should be equivalent to:
    //$text = preg_replace("/\s+/s"," ",$text);
    //$text = rtrim($text,":;[] \r\t\n\0\x0B|\\~@#\$^&*_-+=,");
    //$text = ltrim($text,":;[] \r\t\n\0\x0B|\\~@#^&*_-+=,"); // don't want to get rid of leading $ for variable/printf substituion
    //return $text;
}



function translateTemplate($template) {
    libxml_use_internal_errors(true);
    libxml_clear_errors();
    $dom = new DOMDocument('1.0','UTF-8');
    $dom->substituteEntities = false;
    $dom->encoding = 'UTF-8';
    $dom->preserveWhiteSpace = true;
    $dom->formatOuput=false;
    $contents = file_get_contents($template);
    if ($contents === false) {
        I2CE::raiseError( "Could not read file $template");
        return false;
    }
    $html = false;
    $body = false;
    $head = false;
    if (preg_match('/\<\s*head/m', $contents)) {
        $head = true;
    }
    if (preg_match('/\<\s*body/m', $contents)) {
        $body = true;
    }
    if (preg_match('/\<\s*html/m', $contents)) {
        $html = true;
    }    
    if (!$head) {
        if (!$html) {
            $template_contents = '<html>
 <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
 </head><body>' . $contents . '</body></html>';            
        } else {
            I2CE::raiseError( "ARGH no head but html on $template");
            return false;
        }
    } else {
        if (!preg_match("/^(.*?<\/?head((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>)(.*)/ism", $contents, $matches)) {
            I2CE::raiseError( "ARGH head but wierdness on $template for:\n$trans");
            return false;                
        }
        $template_contents = $matches[1] . '<meta http-equiv="content-type" content="text/html; charset=utf-8">' . $matches[6];
    }
    if (!preg_match("/\<\!DOCTYPE/",$template_contents)) {
        $template_contents = 
            '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
            . "\n" . $template_contents;
    }
            
    libxml_clear_errors();

    if (!$dom->loadHTML($template_contents)){
        $tmp_dom = new DOMDocument();
        if (!$tmp_dom->loadHTML($contents)) {
            I2CE::raiseError( "Could not load file $template");
            return false;
        } else {
            I2CE::raiseError( "Could not load wrapped file $template -- yell at carl");
            return false;
        }
    }
    $errors = libxml_get_errors();
    libxml_clear_errors();
    if (count($errors ) > 0) {
        $tmp_dom = new DOMDocument();
        I2CE::raiseError( "Warning loading wrapped file $template:\nDOM Error -- yell at carl or check your templates:\n" . print_r($errors,true) . "\n" . $template_contents);
    }
    translateNode($dom->documentElement);    
    $first_tag = '';
    $trans = $dom->saveHTML();    
    if ($html) {
        $preamble = '';
        if (preg_match("/^(.*?)<\/?html((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>/ism", $contents, $matches)) { 
            $preamble = $matches[1];
        }
        if (preg_match("/<\/?html((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>.*<\/html>/ism", $trans, $matches)) { 
            $trans = $preamble . $matches[0];
        } else {
            I2CE::raiseError( "Could not find html tag in translation");
            return false;
        }
    } else if ($body) {
        if (preg_match("/<\/?body((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>.*<\/body>/ism", $trans, $matches)) { 
            $trans = $matches[0];
        } else {
            I2CE::raiseError( "Could not find body tag in translation");
            return false;
        }
    } else {
        if (preg_match("/<\/?body((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>(.*)<\/body>/ism", $trans, $matches)) { 
            $trans = $matches[5];
        } else {
            I2CE::raiseError( "Could not find body tag in translation");
            return false;
        }
    }
    return $trans;
}






function translate($text) {
    global $changed_text;
    global $translations;
    if (!is_string($text)) {
        return false;
    }
    list($left,$text,$right) = solidifySourcePieces($text);
    //$text = preg_replace('/\s\s+/',' ', $text);
    if (!$text) {
        return false;
    }    
    if (!array_key_exists($text,$translations)) {
        return false;
    }
    $trans = $translations[$text];

    if (!is_string($trans)) {
        return false;
    }
    if ($trans === $text) {
        return false;
    }
    $changed_text = true;
    return $left . $trans . $right;
}



// $plural_forms =    
//     array(
//         'af'=>2,  'am'=>2,  'ar'=>6,  'arn'=>2,  'az'=>2,  'be'=>3,  'bg'=>2,  'bn'=>2,  'bo'=>1,  'bs'=>3, 
//         'ca'=>2,  'cs'=>3,  'cy'=>4,  'da'=>2,  'de'=>2,  'dz'=>1,  'el'=>2,  'en'=>2,  'eo'=>2,  'es'=>2, 
//         'es_AR'=>2,  'et'=>2,  'eu'=>2,  'fa'=>1,  'fi'=>2,  'fil'=>2,  'fo'=>2,  'fr'=>2,  'fur'=>2,  'fy'=>2, 
//         'ga'=>5,  'gl'=>2,  'gu'=>2,  'gun'=>1,  'ha'=>2,  'he'=>2,  'hi'=>2,  'hy'=>1,  'hr'=>3,  'hu'=>1, 
//         'id'=>1,  'is'=>2,  'it'=>2,  'ja'=>1,  'jv'=>2,  'ka'=>1,  'km'=>1,  'kn'=>1,  'ko'=>1,  'ku'=>2, 
//         'ky'=>1,  'lb'=>2,  'ln'=>2,  'lt'=>3,  'lv'=>3,  'mk'=>3,  'mg'=>2,  'mi'=>2,  'ml'=>2,  'ms'=>1, 
//         'mt'=>4,  'mr'=>2,  'mn'=>2,  'nah'=>2,  'nb'=>2,  'ne'=>2,  'nl'=>2,  'nn'=>2,  'no'=>2,  'nso'=>2, 
//         'or'=>2,  'pa'=>2,  'pap'=>2,  'pl'=>3,  'pt'=>2,  'pt_BR'=>2,  'ro'=>3,  'ru'=>3,  'sco'=>2,  'sk'=>3, 
//         'sl'=>4,  'so'=>2,  'sq'=>2,  'sr'=>4,  'su'=>1,  'sv'=>2,  'ta'=>2,  'te'=>2,  'tg'=>2,  'ti'=>2, 
//         'th'=>1,  'tk'=>2,  'tr'=>1,  'uk'=>3,  'ur'=>2,  'uz'=>1,  'vi'=>1
//         );









function extractPrintF($text) {
    $text = trim($text);
    if (strlen($text) == 0) {
        return '';
    }
    $in_escape = false;
    $begin_quote = false;
    for ($i=0; $i < strlen($text); $i++) {
        $c = $text[$i];
        if ($in_escape) {
            $in_escape = false;
        } else {
            switch ($c) {
            case '"':
            case "'":
                if (!$begin_quote) {
                    $begin_quote = $c;
                } else  if ($begin_quote == $c) {
                    return array(substr($text,1,$i-1),substr($text,$i+1)); //return the unquoted part of the string as well as it's remainder
                } else {
                    //no nothing
                }
                break;
            case '\\':
                $in_escape = true;
                break;
            default:
                //do nothing;
            }
        }
    }
    I2CE::raiseError( "Invalid string $text -- could not extract first full string.  Ignoring");
    return array('','');
}


function checkExtractedString($text) {
    $text =solidifySource($text);    
    if (!preg_match('/[a-z]{2}/i',$text)) {
        //ignore any string that does not have at least two consectuive alphabetical characters in it
        return '';
    }
    if (preg_match('/^\s*(mailto:)?[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\s*$/i',$text)) {         
        I2CE::raiseError( "Ignoring email: $text");
        //ignore email address
        return '';
    }
    if (is_numeric($text)) {
        I2CE::raiseError( "Ignoring numeric: $text");
        return '';
    }
    if (preg_match('/^[a-z]{2,4}:\\/\\/([a-z0-9\-_\\.]+@)?[a-z0-9\-_]+(\\.[a-z0-9\-_])*(\\/[~\'!\\(\\)a-z0-9\-_%&\\?\\.=]*)?/i',$text)) {
        I2CE::raiseError( "Ignoring URL: $text");
        return '';
    }        
    if (preg_match('/^www(\\.[a-z0-9\-_]+)+$/',$text)) {
        I2CE::raiseError( "Ignoring web address bt www prefix: $text");
        return '';
    }
    $tld = 'com|org|net|mil|ac|edu|de|uk|ru|biz|gov|us|asia|aero|nl|fr|ch|info|jobs|tel|name|in|jp|ca|br';
    if (preg_match('/^([a-z0-9\-_]+\\.)+(' . $tld . ')$/',$text)) {
        I2CE::raiseError( "Ignoring web address by TLD: $text");
        return '';
    }
    return preg_replace('/\s\s+/',' ',$text);

}

function getInnerHTML($node) {
    $inner = '';
    //from http://www.php.net/manual/en/class.domelement.php#101243
    $children = $node->childNodes;
    foreach ($children as $child) {
        $inner .= $child->ownerDocument->saveXML( $child );
    }
    return $inner;
}


function translateNode($node) {
    if (!$node instanceof DOMNode) {
        var_dump($node);
        die();
        return;
    }
    global $locale_dashed;
    global $translations;
    global $changed_text;
    global $rtl;
    $attrs = array('span'=>array('text', 'head','title'));
    if ($node instanceof DOMText) {        
        $trans = translate($node->textContent);       
        if ($trans !== false) {
            $node->deleteData(0,$node->length);
            $node->appendData($trans);            
            if ($rtl && $node->parentNode instanceof DOMElement) {
                $node->parentNode->setAttribute('dir','rtl');
            }
        }
    } 
    $block = false;
    if ($node instanceof DOMElement) {
        if (strtolower($node->tagName) == 'script' || strtolower($node->tagName) == 'style') {
            return;
        }
        if ($node->hasAttribute('translator_comment')) {
            $node->removeAttribute('translator_comment');
        }
        if ($node->hasAttribute('printf')) {
            list($trans_sing,$trans_remainder) = extractPrintF($node->getAttribute('printf'));
            if ($node->hasAttribute('printf_plural') && $node->hasAttribute('printf_form'))  {
                if (array_key_exists($trans_sing,$translations)  && is_array($translations[$trans_sing])) {
                    $changed_text = true;
                    //we have translated plural information.  
                    $node->removeAttribute('printf_plural');
                    $node->removeAttribute('printf_sing');
                    foreach ($translations[$trans_sing] as $n=>$trans) {
                        $node->setAttribute('printf_' . $n , "'" . $trans . "'");
                    }
                    $node->setAttribute('lang',$locale_dashed);
                    if ($rtl) {
                        $node->setAttribute('dir','rtl');
                    }
                }
            }  else {
                $trans_sing = translate($trans_sing);
                if ($trans_sing !== false) {
                    $node->setAttribute('printf',"'" . $trans_sing ."'" .  $trans_remainder);
                    if ($rtl) {
                        $node->setAttribute('dir','rtl');
                    }
                }
            }
            return;
        } else  if ($node->hasAttribute('lang')) {
            $lang = $node->getAttribute('lang');
            if ( (strtr($lang,'-','-') ==  I2CE_Locales::DEFAULT_LOCALE)) {
                //if we have lang='en-US' of lang='en_US' attribute, treat this as a block
                $block = $node->tagName;
            }
        } else {
            if (array_key_exists($node->tagName,$attrs)) {
                foreach ($attrs[$node->tagName] as $attr) {
                    if ($node->hasAttribute($attr)) {
                        $trans = translate($node->getAttribute($attr));
                        if ($trans !== false) {
                            $node->setAttribute($attr,$trans);
                        }
                    }
                }
            }
        }
    }
    if (!$block) {
        if ($node->childNodes instanceof DOMNodeList) {
            for ($i = 0; $i < $node->childNodes->length; $i++) {
                translateNode($node->childNodes->item($i));        
            }
        }
    } else {
        $text = checkExtractedString(getInnerHTML($node));
        if (strlen($text) > 0 &&      ($trans = translate($text)) !== false) {
            $html = false;
            $body = false;
            $head = false;
            $wrapped = "<$block>$trans</$block>";
            $wrapped = wrapHTMLinUTF8($wrapped);
            $import_dom = new DOMDocument();
            $orig_dom = $node->ownerDocument;
            libxml_clear_errors();
            $success = $import_dom->loadHTML($wrapped);
            $success = ($success && (count(libxml_get_errors()) ==  0));
            if (!$success) {
                $tmp_dom = new DOMDocument();
                if (!$tmp_dom->loadHTML($wrapped)) {
                    die("Problem importing translated block");
                } else {
                    die("Yell at carl -- problem with wrapping");
                }
            }
            $xpath = new DOMXpath($import_dom);
            $nodeList = $xpath->query('//'. $block . '[1]');
            if ($nodeList->length != 1) {
                die("Yell at carl about finding blocks //$block" . "[1]" . " -- " . $nodeList->length . "\n");
            }
            $new_node = $orig_dom->importNode($nodeList->item(0),true);
            // var_dump($orig_dom);
            // var_dump($import_dom);
            // var_dump($new_node->ownerDocument);
            $new_node->setAttribute('lang', $locale_dashed);
            if ($rtl) {
                $new_node->setAttribute('dir','rtl');
            }
            $node->parentNode->replaceChild($new_node,$node);
        }
    }
}




