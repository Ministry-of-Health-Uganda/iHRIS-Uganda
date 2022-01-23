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



$archive ="." . DIRECTORY_SEPARATOR . "translations" .  DIRECTORY_SEPARATOR . 'launchpad-export.tar.gz';
$archive_dir ="." . DIRECTORY_SEPARATOR . "translations" .  DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR;
$templates_dir ="." . DIRECTORY_SEPARATOR . "translations" .  DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;

$locales = false;
$usage[] = 
    "[--read_po_files=\$read_po_files]: Tries to read .po files for the given locale rather than an export\n\tDefaults to true\n";
$usage[] = 
    "[--overwrite-all=T/F]: Defaults to not set.  If true we overwite all new translations";
$usage[] = 
    "[--templates_dir=\$read_po_files]: Where  to read .po files from\n\tDefaults to $templates_dir\n";
$usage[] =     
     "[--archive=\$archive]: The archive consisting of all translationd\n\tDefaults to $archive\n"
    . "[--locales=\$locale1,\$locale2..\$localeN]: The locales we wish to translate for\n"
    . "\tIf not specified, it uses  every valid subdirectory of in the translations archive file\n";
$usage[] = 
     "[--only_changed=T/F]: produce tranlslated files only when something was translated from the source document.\n"
    . "\tDefaults to T=true\n";
$usage[] =
    "[--create_archive=T/F]: generate the tarball and debian packaging info.\n" 
    ."\tIf F (default), it output translated files within each e module as approriate.\n"
    ."\tIf T, it outputs archive under $archive_dir with a sub-directory for each locale\n";
$usage[] = 
    "[--archive_dir=\$archive_dir]: The directory to store  archive in.\n"
    ."\tDefaults to $archive_dir\n";
$usage[] = 
    "[--only_archive=T/F]: Only create the archive -- do not recreate template files.\n"
    ."\tDefaults to F\n";

$booleans['read_po_files'] = true;
$booleans['overwrite-all'] = null;
$booleans['only_changed'] = true;
$booleans['only_archive'] = false;
$booleans['create_archive'] = false;


$set_configs  = false;
require_once ("translate_base.php");  

@require_once ("Archive/Tar.php");
if (!class_exists('Archive_Tar')) {
    usage('Please install the PEAR Archive_Tar package');
}



foreach ($args as $key=>$val) {
    switch($key) {
    case 'archive':
        $archive = $val;
        break;
    case 'locales':
        $locales = preg_split('/,/',$val,-1,PREG_SPLIT_NO_EMPTY);
        break;
    case 'archive_dir':
        $archive_dir = $val;
    }   
}
if (!$booleans['read_po_files']) {
    $archive = realpath($archive);
    if (!is_readable($archive)) {
        usage("The file $archive is not readable");
    }
}

if ($booleans['create_archive']) {
    $archive_dir .= DIRECTORY_SEPARATOR;
    if (!is_dir($archive_dir)) {
        if (!mkdir($archive_dir,0775,true)) {
            usage("Could not create $archive_dir");
        }
    }
    if (!is_writable($archive_dir)) {
        usage("The file $archive_dir is not a writable directory");
    }
}

var_dump($booleans);


$BASE_MODULE = trim(shell_exec( 'grep \'<I2CEConfiguration\' *xml | sed s/^.*name=[\\"\\\']// | sed s/[\\"\\\'].*$//'));
if (!$BASE_MODULE || strpos($BASE_MODULE, "\n") !== false) {
    I2CE::raiseError("Unable to determine base module", E_USER_ERROR);
}
I2CE::raiseError("Base module is $BASE_MODULE");

$BASE_VERSION = trim(shell_exec('grep -m 1 \'<version>.*</version>\' *.xml | sed s/.*\\<version\\>// | sed s/\\<\\\\/version\\>.*//'));
if (!$BASE_VERSION || strpos($BASE_VERSION, "\n") !== false) {
    I2CE::raiseError("Unable to determine base version", E_USER_ERROR);
}
if (count(explode('.', $BASE_VERSION)) < 3) {
    I2CE::raiseError("Bad version $BASE_VERSION");
}
I2CE::raiseError("Base version is $BASE_VERSION");
$BASE_VERSION_SHORT = implode('.',array_slice(explode('.',$BASE_VERSION),0,2));
$BASE_VERSION_SHORT_NEXT = array_slice(explode('.',$BASE_VERSION),0,2);
$BASE_VERSION_SHORT_NEXT[1]++;
$BASE_VERSION_SHORT_NEXT = implode('.',$BASE_VERSION_SHORT_NEXT);


if (!$booleans['read_po_files']) {
    I2CE::raiseError("Using archive: " . realpath($archive)); 
    $tar =  new Archive_Tar($archive);
    $tar->setErrorHandling(PEAR_ERROR_CALLBACK, array('I2CE','raiseError'));
    $tar_files =array();
    $tar_dirs =array();
    $files = $tar->listContent();
    foreach ($files as $data) {
        if (!array_key_exists('filename',$data)) {
            continue;
        }
        if ($data['typeflag'] == 5) {
            $tar_dirs[] =$data['filename'];
        } else  if ($data['typeflag'] == 0) {
            $tar_files[] =$data['filename'];
        }
    }

    if ($locales == false) {
        $locales = array();
        foreach ($tar_dirs as $dir) {
            if (preg_match('/^([a-zA-Z_\-]+)\/LC_MESSAGES\/$/', $dir,$matches)) {
                $locales[] = $matches[1];
            }
        }
    } else {
        foreach ($locales as $i=>$locale) {
            if(!in_array($locale .'/' ,$tar_dirs)) {
                echo "WARNING: the locale $locale does not exist in " . basename($archive) .  "  -- Skipping\n";
                unset($locales[$i]);
            }
        }
    }
}else {
    //try to read $locale.po files
   if ($locales == false) {
        $locales = array();
        $files= glob($templates_dir . DIRECTORY_SEPARATOR  . '*' . DIRECTORY_SEPARATOR  . '*.po');
        foreach ($files as $file) {
            $locale = basename($file, '.po');
            $locales[$locale] = true;
        }
        $locales = array_keys($locales);
   } else {
       foreach ($locales as $i=>$locale) {
           $files= glob($templates_dir  . '*' . DIRECTORY_SEPARATOR  . '*.po');
           if (count($files) ==0) {
               echo "WARNING: the locale $locale has no .po files   -- Skipping\n";
               unset($locales[$i]);
           }
       }
   }
}



if (count($locales)==0) {
    usage("No valid locales specified");
}
I2CE::raiseError( "Translating for locales:\n\t" . implode(',',$locales) );




$templates = getTranslatableDocuments(false);
$configs = getTranslatableConfigs(false);





//http://www.i18nguy.com/temp/rtl.html
//http://en.wikipedia.org/wiki/List_of_ISO_639-2_codes
$RTLS = array('ar','ara','arc','aze','az','cmc','div','dv','fa','fas','he',
              'jav','jv','jpr','jrb','kas','kaz','kk','ku','kur','ks','lad',
              'ma','mal','may','ms','msa','pa','pal','pan','peo','per','ps',
              'pus','sam','sd','so','som','snd','syc','syr','tmh','tk','tuk'
              ,'ug','uig','ur','urd');

$first_time = true;

$changed_text =false;
ksort($found_modules);

foreach ($locales as $locale) {    
    $locale_dashed = $locale;
    if ( ($dash_pos = strpos($locale_dashed,'-')) === false ) {
        if ( ($pos = strpos($locale_dashed,'_')) !== false) {
            $two_letter = substr($locale,0,$pos);
            $locale_dashed[$pos] = '-';
        } else {
            $two_letter = $locale;
        }
    } else {
        $two_letter = substr($locale,0,$dash_pos);
    }
    $rtl = in_array( $two_letter, $RTLS);
    I2CE::raiseError( "Translating on Locale $locale");

    $translations_array = array();

    if (!$booleans['only_archive']) {
        foreach ($found_modules as $module=>$top_module) {
            if ((!array_key_exists($module,$templates) || !is_array($templates[$module]))
                && (!array_key_exists($module,$configs) || !$configs[$module])) {
                I2CE::raiseError("No translatable templates or configs for $module");
                continue;
            }
            $launchpad_name = launchpad($module);
            if (!$booleans['read_po_files']) {
                $mo_file =  $locale . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $launchpad_name . '.mo';        
                if (!in_array($mo_file,$tar_files)) {
                    continue;
                }            
                $contents = false;
                I2CE::raiseError("Extracting $mo_file");
                $contents=$tar->extractInString($mo_file);
                if (!$contents) {
                    I2CE::raiseError("Bad extraction for $mo_file");
                    continue;
                }
                $mo_resource = fopen("php://temp", 'r+');
                fputs($mo_resource,$contents);
                rewind($mo_resource);
                $translations_array[$module] = loadMO($mo_resource);
            } else {
                $po_file = $templates_dir . launchpad($module) . DIRECTORY_SEPARATOR . $locale . '.po';
                if (!is_readable($po_file)) {
                    I2CE::raiseError($po_file  . " is not readable for $module");
                    continue;
                }                
                $t_translations = loadPOT($po_file);
                foreach ($t_translations as $msg_id=>$data) {
                    if (!array_key_exists('msgstr',$data)) {
                        unset($t_translations[$msg_id]);
                        continue;
                    }
                    $msg_str = $data['msgstr'];
                    if (!$msg_id || !$msg_str || $msg_id == $msg_str) {
                        unset($t_translations[$msg_id]);
                        continue;
                    }
                    $t_translations[$msg_id] = $msg_str;
                }
                $translations_array[$module] = $t_translations;
            }
            if (!is_array($translations_array[$module]) || count($translations_array[$module]) == 0) {
                I2CE::raiseError("no translations for $module");
                unset($translations_array[$module]);
                continue;
            }            
        }
        $t_translations_array = array();
        foreach ($translations_array as  $mod=>$trans) {
            $t_trans = $trans;
            foreach ($trans as    $msg_id=>$tran) {
                if ($msg_id == '') {
                    continue;
                }
                $t_msg_id =  solidifySource($msg_id);
                if (array_key_exists($t_msg_id,$trans)) {
                    continue;
                }
                $t_trans[$t_msg_id] = solidifySource($tran);
            }            
            $t_translations_array[$mod] = $t_trans;
        }
        $translations_array = $t_translations_array;

        foreach ($found_modules as $module=>$top_module) {
            if (!array_key_exists($module,$translations_array) || !is_array($translations_array[$module])) {
                continue;
            }
            if (!array_key_exists($module,$configs) ) {
                continue;
            }
            $translations = $translations_array[$module];
            $file = null;
            if ( ! $storage->setIfIsSet($file,"/config/data/$module/file")) {
                I2CE::raiseError( "No config file for $module -- Skipping");
                continue;
            }    
            $file = basename($file);
            $source_file = $configs[$module] . DIRECTORY_SEPARATOR . I2CE_Locales::DEFAULT_LOCALE . DIRECTORY_SEPARATOR . $file;
            if (!is_readable($source_file)) {
                I2CE::raiseError( "Config file $source_file is not readable. -- Skipping");
                continue;
            }    
            $dom = new DOMDocument();
            if (!$dom->load($source_file)) {
                I2CE::raiseError( "Could not load $source_file -- Skipping");
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
                I2CE::raiseError( "No translatable strings found in $source_file -- Skipping");
                continue;
            }
            $has_trans = false;
            for ($i=0; $i < $results->length; $i++) {
                $node = $results->item($i);
                $text = trim($node->textContent);
                $key = false;
                if ($node->tagName == 'value' && ($node->parentNode->getAttribute('type') == 'delimited')) {                  
                    list($key,$text) = explode(':',$text);                
                }
                $trans = translate($text);
                if ($trans === false) { 
                    //no translation
                    //we have to keep the metadata displyaname as it is a required tag.  
                    //otherwise, we can't translate it, so we remove it from the tree
                    if ( ! ($node->parentNode->tagName == 'metadata' && $node->tagName == 'displayName')) {
                        $node->parentNode->removeChild($node);
                    }
                    continue;
                } 
                //we have a translation
                $has_trans = true;
                while ($node->hasChildNodes()) {
                    $node->removeChild($node->firstChild);
                }
                if ($key !== false) {
                    $trans = $key . ':' . $trans;
                }
                $node->appendChild($dom->createTextNode($trans));
            }        
            if (!$has_trans) {
                I2CE::raiseError( "No configuration strings in $module were translated.  Skipping");
                continue;
            }
            foreach ( $xpath->query('//version') as $node) {
		if ($node instanceof DOMElement) {
		    $node->appendChild($dom->createTextNode('.1')); //hack to trigger an update for 4.1.10 release due translations not being loaded on updates
		}
	    }
            $results = $xpath->query('//*[@locale]');
            for ($i=0; $i < $results->length; $i++) {
                $results->item($i)->setAttribute('locale',$locale);
            }
            $out = $dom->saveXML();
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
            if ($booleans['create_archive']) {
                $cwd = realpath(getcwd());
                $c_path = realpath($configs[$module]);
                if ( strpos($c_path,$cwd) !== 0) {
                    I2CE::raiseError("Cannot determine module sub-directory structure for $module", E_USER_ERROR);
                }
                $target_dir  = $archive_dir . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . substr($c_path, strlen($cwd)) . DIRECTORY_SEPARATOR . $locale. DIRECTORY_SEPARATOR;
            } else {
                $target_dir = $configs[$module] . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR;
            }
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0775,true)) {
                    I2CE::raiseError("Could not created $target_dir", E_USER_ERROR);
                }
            }
            if (!is_writable($target_dir)) {
                I2CE::raiseError( "WARNING: Cannot write to directory $target_dir", E_USER_ERROR);
                continue;
            }

            $target_file = $target_dir .  $file;
            if (!file_put_contents($target_file,$out)) {
                I2CE::raiseError( "Could not save $file localized to $locale for $module\n:\t$target_file", E_USER_ERROR);
            }
        }



        foreach ($found_modules as $module=>$top_module) {
            if (!array_key_exists($module,$translations_array) ||! is_array($translations_array[$module])) {
                continue;
            }
            if (!array_key_exists($module,$templates) || !is_array($templates[$module])) {
                continue;
            }
            $translations = $translations_array[$module];
            foreach ($templates[$module] as $basePath=>$files) {  //basePath ends in en_US
                $topDir = dirname($basePath); //topdir is the templates (or whatever) dir
                $translated_files = array();
                foreach($files as $file) {
                    $changed_text =false; 
                    $trans = translateTemplate($file);
                    if (false === $trans) {
                        I2CE::raiseError( "WARNING: Could not translate $file");
                    }
                    if (!trim($trans)) { //empty contents
                        continue;
                    }
                    if ($booleans['only_changed'] && !$changed_text) {
                        continue;
                    }
                    $translated_files[basename($file)] = $trans;
                }
                if (count($translated_files) == 0) {
                    continue;
                }
                I2CE::raiseError( "In $module, we have translations for the following template files:\n\t" . implode(",", array_keys($translated_files)) );
                if ($booleans['create_archive']) {
                    $cwd = realpath(getcwd());
                    $t_path = realpath($topDir);
                    if ( strpos($t_path,$cwd) !== 0) {
                        I2CE::raiseError("Cannot determine module sub-directory structure for $module", E_USER_ERROR);
                    }
                    $target_dir  = $archive_dir . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . substr($t_path, strlen($cwd)) . 
                        DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR;
                } else {
                    $target_dir = $topDir . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR;
                }
                if (!is_dir($target_dir)) {
                    if (!mkdir($target_dir,0775,true)) {
                        I2CE::raiseError( "WARNING: Could not make $target_dir", E_USER_ERROR);
                    }
                }            
                if (!is_writable($target_dir)) {
                    I2CE::raiseError( "WARNING: Cannot write to directory $target_dir", E_USER_ERROR);
                }
                foreach ($translated_files as $file=>$contents) {
                    $target_file = $target_dir . $file;
                    if (is_file($target_file) && is_readable($target_file)) {
                        $existing = file_get_contents($target_file);
                        if (false === $existing) {
                            I2CE::raiseError( "WARNING: Translation to $locale for $file in $module exists but is not readable. -- Skipping");
                            continue;
                        }
                        if ($contents == $existing) {
                            continue;
                        } else {
                            $msg ="There is an existing translation to $locale for $file in the module $module.  Overwrite? ";
                            if (function_exists('xdiff_string_diff')) {
                                $diff = xdiff_string_diff( $existing, $contents,1);
                            } else {
                                if ($first_time) {
                                    $msg .= "\nFor more information, run (on ubunutu):
\tsudo apt-get install re2c  php5-dev build-essential wget
\tmkdir -p /tmp/src
\tcd /tmp/src
\twget http://www.xmailserver.org/libxdiff-0.22.tar.gz
\ttar -xzf libxdiff-0.22.tar.gz 
\tcd libxdiff-0.22
\t./configure
\tmake
\tsudo make install
\tsudo pecl install xdiff
\techo \"extension=xdiff.so\" | sudo tee /etc/php5/conf.d/xdiff.ini \n";
                                }
                                $first_time  = false;
                                $diff = false;
                            }
                            if (prompt($msg, $booleans['overwrite-all'], $diff)) {
                                unlink($target_file);
                            } else {
                                continue;
                            }
                        }
                    }
                    if (false === file_put_contents($target_file,$contents)) {
                        I2CE::raiseError( "WARNING: Could not create file $target_file", E_USER_ERROR);
                    } else {
                        I2CE::raiseError( "Translate $target_file");
                    }
                }
            }        
        }
    }
    if (!$booleans['create_archive']) {
        continue;
    }
    $source_dir  = realpath($archive_dir . DIRECTORY_SEPARATOR . 'files' .  DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR) ;
    if (!is_dir($source_dir)) {
        I2CE::raiseError($archive_dir . DIRECTORY_SEPARATOR . 'files' .  DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . " is not readable");
        continue;
    }
    //here we do the tarball packaging stuff.
    $target_dir  = $archive_dir . DIRECTORY_SEPARATOR . 'tarballs' .   DIRECTORY_SEPARATOR ;
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir,0775,true)) {
            I2CE::raiseError("Could not create directgory " . $target_dir, E_USER_WARNING);
        }
    }
    $target_dir = realpath($target_dir);
    if (!is_writable($target_dir)) {
        I2CE::raiseError("Cannot write to directory $target_dir", E_USER_WARNING);
    }
    $target_file = $target_dir . DIRECTORY_SEPARATOR . $BASE_MODULE . '-locale-' . $locale . '.tgz';
    if (file_exists($target_file)) {
        if (!unlink($target_file)) {
            I2CE::raiseError("Could not erase existing " . $target_file, E_USER_WARNING);
        }
    }
    I2CE::raiseError("Creating arvhice by:\n\tcd $source_dir; tar  -caf $target_file ./");
    shell_exec("cd $source_dir; tar  -caf $target_file ./");    


    //here we do the debian packaging stuff.    
    I2CE::raiseError("Creating debian package for $locale");
    $package_name = 'i2ce-' . $BASE_MODULE . '-all-locale-' . strtolower(strtr($locale,'_','-'));
    $package_vers = $BASE_VERSION . '-1';
    $target_dir  = $archive_dir . DIRECTORY_SEPARATOR . 'debian' .  DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR ;
    $parent_dir  = $archive_dir . DIRECTORY_SEPARATOR . 'debian' .  DIRECTORY_SEPARATOR ;
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir,0775,true)) {
            I2CE::raiseError("Could not create directgory $target_dir", E_USER_WARNING);
        }
    }
    if (!is_writable($parent_dir)) {
        I2CE::raiseError("Cannot write to directory $parent_dir", E_USER_WARNING);
    }
    if (!is_writable($target_dir)) {
        I2CE::raiseError("Cannot write to directory $target_dir", E_USER_WARNING);
    }
    $debian_dir  = $target_dir . DIRECTORY_SEPARATOR . 'DEBIAN' ;
    if (!is_dir($debian_dir)) {
        if (!mkdir($debian_dir,0775,true)) {
            I2CE::raiseError("Could not create directgory $debian_dir", E_USER_WARNING);
        }
    }
    if (!is_writable($debian_dir)) {
        I2CE::raiseError("Cannot write to directory $debian_dir", E_USER_WARNING);
    }
    $ret =null;
    $out = null;
    $files_dir = $target_dir . DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'share' . DIRECTORY_SEPARATOR . 'i2ce'  . DIRECTORY_SEPARATOR  . $BASE_MODULE ;
    if (!is_dir($files_dir)) {
        if (!mkdir($files_dir,0775,true)) {
            I2CE::raiseError("Could not create directgory $files_dir", E_USER_WARNING);
        }
    }
    if (!is_writable($files_dir)) {
        I2CE::raiseError("Cannot write to directory $files_dir", E_USER_WARNING);
    }
    exec("cp -R $source_dir/* $files_dir/",$out,$ret);
    if ($ret != 0 ) {
        I2CE::raiseError("Could not copy translated files to debian archive ($ret):\n" . implode("\n",$out), E_USER_WARNING);
    }
    $doc_dir = "$target_dir/usr/share/doc/$package_name/";
    if (!is_dir($doc_dir)) {
        if (!mkdir($doc_dir,0775,true)) {
            I2CE::raiseError("Could not create directgory $doc_dir", E_USER_WARNING);
        }
    }
    if (!is_writable($doc_dir)) {
        I2CE::raiseError("Cannot write to directory $doc_dir", E_USER_WARNING);
    }    
    exec ("cp COPYING $target_dir/usr/share/doc/$package_name/copyright", $out,$ret);
    if ($ret != 0 ) {
        I2CE::raiseError("Could not copy copyright file:\n" . implode("\n",$out), E_USER_WARNING);
    }
    $files = array(
        $debian_dir . '/control'=>
        "Source: $package_name\n"
        ."Version: $BASE_VERSION-1\n"
        ."Section: misc\n"
        ."Priority: optional\n"
        ."Maintainer: Carl Leitner <litlfred@ibiblio.org>\n"
//        ."Standards-Version: 3.8.1\n"
        ."Homepage: https://launchpad.net/$BASE_MODULE\n"
//        ."Build-Depends: debhelper (>= 7)\n"
//        ."Vcs-Browser: http://bazaar.launchpad.net/%7Eintrahealth%2Binformatics/$BASE_MODULE\n"
//        ."Vcs-Bzr: http://bazaar.launchpad.net/%7Eintrahealth%2Binformatics/$BASE_MODULE\n"
        ."Package: $package_name\n"
        ."Section: misc\n"
        ."Priority: optional\n"
        ."Architecture: all\n"
        ."Depends:  i2ce-$BASE_MODULE-all  (>= $BASE_VERSION_SHORT), i2ce-$BASE_MODULE-all (<< $BASE_VERSION_SHORT_NEXT)\n"
        ."Description:Provides translations to the locale $locale for all modules within $BASE_MODULE\n"
        ." Translations provided by the people for the people via launchpad\n",

        
        $doc_dir . '/changelog'=> 
"$package_name ($BASE_VERSION-1)

  * Generated by translate_templates.php 

 -- Carl Leitner <litlfred@ibiblio.org> " . date('l jS \of F Y h:i:s A') ,
        
        
//         'compat'=>
//         "7\n",
        
        $doc_dir . '/copyright'=>
        "This package was debianized by Carl Leitner <litlfred@ibiblio.org> on " . date('l jS \of F Y h:i:s A') ."\n\n" 
        ."It contains translated files of  http://bazaar.launchpad.net/%7Eintrahealth%2Binformatics/$BASE_MODULE/  based on the launchpad hosted translations\n"
        ."Copyright: 2009 by IntraHealth International, Inc.\n"
        ."Upstream Author: Luke Duncan<lduncan@intrahealth.org> / Carl Leitner <litlfred@ibiblio.org>\n\n"
        ."License: GPLv3 (see /usr/share/common-licenses/GPL-3)\n"
        );
    $files[$doc_dir . '/changelog.Debian']= $files[$doc_dir . '/changelog'];
    $file_list = explode("\n",trim(shell_exec("cd $source_dir; find ./")));
    foreach ($file_list as $i=>&$file) {
        if (strlen($file) < 2 || $file[0] != '.' || $file[1] != '/') {
            unset($file_list[$i]);
            continue;
        }
        $file = substr($file,2);
        $file = "usr/share/i2ce/$BASE_MODULE/$file usr/share/i2ce/$BASE_MODULE/$file";
    }
    $file_list[] = "usr/share/doc/$package_name/copyright usr/share/doc/$package_name/copyright";
    $file_list[] = "usr/share/doc/$package_name/changelog.gz usr/share/doc/$package_name/changelog.gz";    
    $file_list[] = "usr/share/doc/$package_name/changelog.Debian.gz usr/share/doc/$package_name/changelog.Debian.gz";
    //$files[$target_dir . '/' . $package_name . '.install'] = implode("\n",$file_list) . "\n";
    foreach ($files as $target_file=>$content) {
        if (file_exists($target_file)) {
            if (!unlink($target_file)) {
                I2CE::raiseError("Could not remove existing $target_file", E_USER_WARNING);
            }
        }
        if (!file_put_contents($target_file,$content)) {
            I2CE::raiseError("Could not write   $target_file:\n$content", E_USER_WARNING);
        }
    }
    I2CE::raiseError($doc_dir);
    exec ("cd $doc_dir; gzip  -f  --best changelog", $out,$ret);
    if ($ret != 0 ) {
        I2CE::raiseError("Could not gzip chnagelog:\n" . implode("\n",$out), E_USER_WARNING);
    }
    exec ("cd $doc_dir; gzip  -f --best changelog.Debian", $out,$ret);
    if ($ret != 0 ) {
        I2CE::raiseError("Could not gzip chnagelog.Debian:\n" . implode("\n",$out), E_USER_WARNING);
    }
    $deb_file = $package_name .  '_' . $BASE_VERSION . '-1_all.deb';
    I2CE::raiseError(shell_exec("fakeroot dpkg-deb --build $target_dir $parent_dir/$deb_file"));    
    I2CE::raiseError(shell_exec("lintian $parent_dir/$deb_file"));    
}







