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

$booleans['remove-strings'] = null;
$template_dir = "./translations/templates";
$usage[] = 
    "[--template_dir=\$template_dir]: The directory to store .pot template files in\n"
   ."\tIf not set, we use $template_dir\n" ;
$usage[] = 
    "[--remove-strings=T/F] set to true to always remove the string from a module's .pot\n"
    ."\twhich are no longer present in the module\n";
require_once ("translate_base.php");  

if (array_key_exists('template_dir',$args)) {
    $template_dir = $args['template_dir'];    
}


if (!is_dir($template_dir)) {
    if (!mkdir($template_dir,0775,true)) {
        usage("Could not make $template_dir");
    }    
}
//fixup permissions if set badly before
chmod($template_dir,0775);
if ( !is_writeable($template_dir)) {
    usage("The direcotory $locales_dir is not a readable directory");
}
$template_dir = realpath($template_dir);


$configs = getTranslatableConfigs(true); //this will just create any en_US that we need to
$templates = getTranslatableDocuments();

function addString(&$strings,$text) {
    if (!array_key_exists($text,$strings)) {
        $strings[$text] = array();
    }
}
function addPluralString(&$strings,$text,$text_plural) {
    addString($strings,$text);
    $strings[$text]['msg_plural']= $text_plural;
}

function setComment(&$strings, $text, $type,$comment) {
    addString($strings,$text);
    if (!array_key_exists('comments',$strings[$text])) {
        $strings[$text]['comments'] = array();
    }
    if (!array_key_exists($type,$strings[$text]['comments'])) {
        $strings[$text]['comments'][$type] = '';
    }
    $strings[$text]['comments'][$type] .= trim($comment) . "\n";
}
function setSourceComment(&$strings, $text, $line_no = null) {
    addString($strings,$text);
    if ( ($link = getLaunchpadLink($line_no)) === false) {
        return;
    }
    setComment($strings,$text,':',$link);
}

function setFormatComment(&$strings, $text, $format) {
    addString($strings,$text);
    $format = trim($format);
    if (!$format) {
        return;
    }
    setComment($strings,$text,',',$format);
}

function setExtractedComment(&$strings, $text, $hint) {
    addString($strings,$text);
    $hint = trim($hint);
    if (!$hint) {
        return;
    }
    setComment($strings,$text,'.',$hint);
}
function setTranslatorComment(&$strings, $text, $hint) {
    addString($strings,$text);
    $hint = trim($hint);
    if (!$hint) {
        return;
    }
    setComment($strings,$text,'',$hint);
}


function getLaunchpadLink($line_no) {
    global $file;
    $base_dir = getcwd();
    $top_module = strtolower(basename($base_dir));
    if ( ($pos = strpos($file,$base_dir)) === false) {
        return false;
    }
    $sub = str_replace('//','/',ltrim(substr($file,$pos + strlen($base_dir)),'/'));
    $link  = 'http://bazaar.launchpad.net/~intrahealth+informatics/' . $top_module. '/4.0-dev/annotate/head%3A/' . $sub;
    $link_text = $top_module. '/4.0-dev/annotate/head%3A/' . $sub;
    if ($line_no != '') {
        $link .= '#L' . $line_no;
	$link_text .= " line " . $line_no;
    }
    $link = "[" . $link . " " . $link_text . "]";
    return $link;
}

foreach ($found_modules  as $module=>$top_module) {
    if (!array_key_exists($module,$configs) && !array_key_exists($module,$templates)) {
        continue;
    }
    I2CE::raiseError( "Pulling strings from $module");
    $vers = '';
    I2CE::getConfig()->setIfIsSet($vers,"/config/data/$module/version");
    if (strlen($vers) == 0) {
        I2CE::raiseError( "No module version found -- Skipping");
        return false;
    }
    $mod_strings = array();
    if (array_key_exists($module,$configs)) {
        I2CE::raiseError( "Checking for translatable configuration strings in $module");
        getConfigurationStrings($module,$configs[$module], $mod_strings);
    }
    if (!array_key_exists($module,$templates)) {
        $templates[$module] = array();
    }
    foreach ($templates[$module] as $path=>$files) {
        foreach ($files as $file) {
            //strings is an array with keys the msgstr and values iether true or a translator's comment
            if ( (extractStringsFromDocument($file,$mod_strings)) === false) {
                I2CE::raiseError( "WARNING: Could not extract strings from $file. -- Skipping");
            }
        }
    }
    $launch_module = launchpad($module);
    $pot_dir =  $template_dir . DIRECTORY_SEPARATOR . $launch_module;
    if (!is_dir($pot_dir)) {
        if (!mkdir($pot_dir,0775,true)) {
            usage("Could not make $pot_dir");
        }    
    }
    $pot_file = $pot_dir . DIRECTORY_SEPARATOR . $launch_module . '.pot';
    $existing_template = loadPOT($pot_file);
    $changed_comments = true;
    $changed_plural = false;
    $meta = false;
    if ($existing_template === false) {
        $existing_template= array();
    } 
    if (array_key_exists('',$existing_template)) {
        //remove metat data for the time being so we can easily check for new/changed/removed strings below.
        $meta = $existing_template[''];
        unset($existing_template['']);
        if (array_key_exists('msgstr',$meta)) {
            if (preg_match('/^\s*Project\-Id\-Version\:\s*(\S+)\@(\S+)[ \t]+([0-9a-zA-Z\.]+)\s*$/im',$meta['msgstr'],$matches)) {
                if ( ($top_module !== $matches[1])) {
                    I2CE::raiseError( "Changed top-level module");
                } else  if ( ($module !== $matches[2])) {
                    I2CE::raiseError( "Module name mismatch $module != {$matches[2]}.  Ignoring found $pot_file");
                    $existing_template = array();
                    $meta = false;
                } else  if (($vers != $matches[3])) {
                    I2CE::raiseError( "Changed module $module from version {$matches[3]} to $vers");
                } else {
                    //this is the only case we are allowed to presever the meta data for the existing template
                    $changed_comments = false;
                }
            } else {
                I2CE::raiseError( "Existing .pot file is not recognized as being for the module $module under $top_module.  Ignoring found $pot_file");
                $existing_template = array();
                $meta = false;
            }
        } else {
            I2CE::raiseError( "Warning: No meta data found in $pot_file");
            //no meta data was found in the string.
            $meta = false;
        }
    }
    if (count($existing_template) == 0 && count($mod_strings) == 0) {
        I2CE::raiseError( "No strings for the module $module.  -- Skipping");
        continue;
    }
    $removed_strings = array_diff_key($existing_template,$mod_strings);
    $removed = array();
    foreach ($removed_strings as $string=>$data) {
        if (prompt(
                'The string "'. $string . '" was in the existing .pot file for the module ' . $module .' but not is not currently found in the module.  Remove?',
                $booleans['remove-strings'])) {
            unset($existing_template[$string]);
            $removed[] = $string;
        }
    }

    
    $new_strings = array();
    unset($data);
    $changed_comments = false;
    foreach ($mod_strings as $string=>&$data) {
        if (array_key_exists($string,$existing_template)) {
            $existing_comments = array();
            if (array_key_exists('comments',$existing_template[$string])) {
                $existing_comments = $existing_template[$string]['comments'];
            }
            if (array_key_exists('msgid_plural',$existing_template[$string])) {
                $plural = $existing_template[$string]['msgid_plural'];
            } else {
                $plural = false;
            }

            unset($comments);
            if (!array_key_exists('comments',$data) || !is_array($data['comments'])) {
                $data['comments'] = array();
            }
            $comments = &$data['comments'];

            foreach (array('','.',',',':') as $type) {
                if (!array_key_exists($type,$comments) && !array_key_exists($type,$existing_comments)) {
                    continue;
                }
                if (!array_key_exists($type,$comments) ) {
                    if (trim(implode("\n",$existing_comments[$type])) !='') {
                        $changed_comments = true;
                        break;
                    }
                    continue;
                }
                if (!array_key_exists($type,$existing_comments) ) {
                    if (trim($comments[$type]) != '') {
                        $changed_comments = true;
                        break;
                    }
                    continue;
                }
                if (trim($comments[$type]) != trim(implode("\n",$existing_comments[$type]))) {
                    $changed_comments = true;
                    break;
                }
            }
            //now preserve any translators comments.
            if (array_key_exists('',$existing_comments)) {
                if ($c = trim(implode("\n",$existing_comments['']))) {
                    if (!array_key_exists('',$comments)) {
                        $comments[''] = '';
                    }
                    $comments[''] .= $c;
                }
            }
            unset($comments);
            if (array_key_exists('plural', $data)) {
                if ($data['plural'] !== $plural) {
                    $changed_plural = true;
                }
            } else  if ($plural !== false) {
                $changed_plural = true;
            }
        } else {
            $new_strings[]  = $string;
        }
        $existing_template[$string] = $data;
    }
    unset($data);
    if ((!$changed_comments) && (count($new_strings) == 0) && count($removed) == 0) {
        //we have the same strings.  just double check that the meta data (e.g. comments have not changed).
        I2CE::raiseError( "No change in strings or their comments for the module $module.  -- Skipping");
        continue;
    }
    // if (file_exists($pot_file)) {
    //     I2CE::raiseError("Removing $pot_file");
    //     if (!unlink($pot_file)) {
    //         I2CE::raiseError( "Could not remove existing $launchpad_module.pot in $tpot_dir -- Skipping");
    //         continue;
    //     }
    // }
    if ($meta) { //add back in the meta data if it exists
        if (array_key_exists('',$existing_template)) {
            array_unshift($meta,$existing_template['']);
        }
    }    
    if (writeOutPOT($pot_file,$top_module,$module,$vers,$existing_template)) {
        I2CE::raiseError( "Created $pot_file for $module $vers");
        if (count($new_strings) > 0) {
            I2CE::raiseError( "Have added " . count($new_strings) . " new string(s)");
        }
        if (count($removed) > 0) {
            I2CE::raiseError( "Removed " . count($removed) . " string(s)");
        }
    } else {
        I2CE::raiseError( "Failed to write out $pot_file for $module", E_USER_WARNING);
    }
}





function getConfigurationStrings($module, $config_dir, &$strings) {
    global $file;
    $config_file = null;
    $strings = array();
    global $storage;
    if (  !$storage->setIfIsSet($config_file,"/config/data/$module/file")) {
        I2CE::raiseError( "No config file for $module -- Skipping");
        return;
    }    
    $file = $config_dir . DIRECTORY_SEPARATOR . I2CE_Locales::DEFAULT_LOCALE . DIRECTORY_SEPARATOR . basename($config_file);
    if (!is_readable($file)) {
        I2CE::raiseError( "Config file $source_file is not readable. -- Skipping");
        return ;
    }    
    $dom = new DOMDocument();
    if (!$dom->load($file)) {
        I2CE::raiseError( "Could not load {$file} -- Skipping");
        return;
    }
    $xpath = new DOMXPath($dom);
    $qry = 
        '/I2CEConfiguration/configurationGroup//value[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"] ' .
//value nodes inherit locale
        '| /I2CEConfiguration/configurationGroup[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"]//value ' .
        '| /I2CEConfiguration/configurationGroup/descendant-or-self::node()[@locale="' . I2CE_Locales::DEFAULT_LOCALE . '"]//value' .
//        '| /I2CEConfiguration/configurationGroup//description[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"] ' .
//        '| /I2CEConfiguration/configurationGroup//displayName[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"] ' .
//           description and displayname nodes do not inherit locale so we don't clutter up the .pot files
//            '| /I2CEConfiguration/configurationGroup/descendant-or-self::node[@locale="' . I2CE_Locales::DEFAULT_LOCALE . '"]//description' .
//            '| /I2CEConfiguration/configurationGroup/descendant-or-self::node[@locale="' . I2CE_Locales::DEFAULT_LOCALE . '"]//displayName' .
        '| /I2CEConfiguration/metadata/description[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"]' .
        '| /I2CEConfiguration/metadata/displayName[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"]' .
        '| /I2CEConfiguration/metadata/category[@locale="' . I2CE_Locales::DEFAULT_LOCALE  . '"]' ;
    $results = $xpath->query($qry);
    if ($results->length == 0 ) {
        I2CE::raiseError( "No translatable strings found in $file -- Skipping");
        return ;
    }
    I2CE::raiseError( $results->length . " translatable strings found in $file");
    for ($i=0; $i < $results->length; $i++) {        
        $node = $results->item($i);
        $p_node = $node->parentNode;
        $content = $results->item($i)->textContent;        
        if ($node->nodeName == 'value' && $p_node->nodeName == 'configuration' && $p_node->hasAttribute('type') && $p_node->getAttribute('type') == 'delimited') {
            $content = array_pop(array_pad(explode(':',$content,2),2,''));
        }
        $string = checkExtractedString($content);
        if (!$string) {
            continue;
        }
        $line_no = null;
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            $line_no = $node->getLineNo();
        }        
        setSourceComment($strings, $string, $line_no);
    }
}






function checkExtractedPrintF($text) {
    $text = checkExtractedString($text);
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
                    return substr($text,1,$i-1); //return the unquoted part of the string
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
    return '';
}



function extractTextFromNode($node,&$strings) {
    global $file;
    $attrs = array('span'=>array('text', 'head','title'));
    if ($node instanceof DOMText) {        
        $text = checkExtractedString($node->textContent);      
        if (strlen($text) == 0) {
            return;
        }
        $line_no = null;
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            $line_no = $node->getLineNo();
        }      
        setSourceComment($strings, $text, $line_no);  
        return;
    }     
    if ($node instanceof DOMElement) {
        $check_children = true;
        if (strtolower($node->tagName) == 'script' || strtolower($node->tagName) == 'style') {
            //skip a script node:  Thanks to Federico Vera for pointing this out.
            return;
        }
        if ($node->hasAttribute('printf')) {
            $text = checkExtractedPrintF($node->getAttribute('printf'));
            if (strlen($text) == 0) {
                continue;
            }
            if ($node->hasAttribute('printf_plural') && $node->hasAttribute('printf_form')) {
                $text_form = checkExtractedString($node->getAttribute('printf_form'));
                if (strlen($text_form) == 0) {
                    continue;
                }
                $text_plural = checkExtractedString($node->getAttribute('printf_plural'));
                if (strlen($text_plural) == 0) {
                    continue;
                }
                addPluralString($strings,$text,$text_plural);            
            }  else { //assume it is only the singular form
                addString($strings,$text);
            }
            setFormatComment($strings, $text, 'c-format');
            $line_no = null;
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                $line_no = $node->getLineNo();
            }      
            setSourceComment($strings, $text, $line_no);  
            if ($node->hasAttribute('translator_comment')) {
                setExtractedComment($strings, $text, $node->getAttribute('translator_comment'));
            }
            return;
        } else if ($node->hasAttribute('lang')) {
            $lang = $node->getAttribute('lang');
            if ( (strtr($lang,'-','_') ==  I2CE_Locales::DEFAULT_LOCALE)) {
                //if we have lang='en-US' of lang='en_US' attribute, treat this as a block
                $block = $node->tagName;
                $text = checkExtractedString(getInnerHTML($node));
                if (strlen($text) == 0) {
                    return;
                }
                $line_no = null;
                if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                    $line_no = $node->getLineNo();
                }      
                setSourceComment($strings, $text, $line_no);  
                if ($node->hasAttribute('translator_comment')) {
                    setExtractedComment($strings, $text, $node->getAttribute('translator_comment'));
                }
                $check_children = false;
            }
        }
        if (array_key_exists($node->tagName,$attrs)) {
            foreach ($attrs[$node->tagName] as $attr) {
                if ($node->hasAttribute($attr)) {
                    $text = checkExtractedString($node->getAttribute($attr));
                    if (strlen($text)== 0) {
                        continue;
                    }
                    $line_no = null;
                    if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                        $line_no = $node->getLineNo();
                    }      
                    setSourceComment($strings, $text, $line_no);  
                    
                }
            }
        }
        if ($check_children) {
            for ($i = 0; $i < $node->childNodes->length; $i++) {
                extractTextFromNode($node->childNodes->item($i),$strings);        
            }
        }
    }
}


function extractStringsFromDocument($template,&$strings) {
    libxml_use_internal_errors(true);
    libxml_clear_errors();
    $dom = new DOMDocument('1.0','UTF-8');
    $dom->substituteEntities = false;
    $dom->encoding = 'UTF-8';
    $contents = file_get_contents($template);
    if ($contents === false) {
        I2CE::raiseError( "Could not read file $template");
        return false;
    }
    libxml_clear_errors();
     
    if (!$dom->loadHTML(wrapHTMLinUTF8($contents))) {
        I2CE::raiseError("Problem importing $template.  Something is wrong with the html.");
        return false;
    }
    
    $errors = libxml_get_errors();
    libxml_clear_errors();
    //$success = ($success && (count($errors) ==  0));
    if (count($errors) > 0) {
        I2CE::raiseError("Warning on loading $template:\n" . print_r($errors,true));
    }
    $start_node = null;
    $xpath = new DOMXPath($dom);
    if (preg_match('/\<\s*html/m', $contents)) {
        $start_node = $xpath->query('//html');
        if ($start_node->length != 1 ) {
            I2CE::raiseError( "Could not find html node");
            return false;
        }
        $start_node = $start_node->item(0);
    } else  if (preg_match('/\<\s*head/m', $contents)) {
        $start_node = $xpath->query('//head');            
        if ($start_node->length != 1 ) {
            I2CE::raiseError( "Could not find head node");
            return false;
        }
        $start_node = $start_node->item(0);        
        if (preg_match('/\<\s*body/m', $contents)) {
            $start_node = $start_node->parentNode;
        }
    } else {
        $start_node = $xpath->query('//body');            
        if ($start_node->length != 1 ) {
            I2CE::raiseError( "Could not find body node");
            return false;
        }
        $start_node = $start_node->item(0);
    }
    extractTextFromNode($start_node,$strings);
    return true;
}





















