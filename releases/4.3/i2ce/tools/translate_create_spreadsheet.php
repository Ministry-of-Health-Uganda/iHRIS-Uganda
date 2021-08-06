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
$archive ="." . DIRECTORY_SEPARATOR . "translations" .  DIRECTORY_SEPARATOR . 'launchpad-export.tar.gz';
$archive_dir ="." . DIRECTORY_SEPARATOR . "translations" .  DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR;
$locales = false;
$use_modules = false;
$booleans['use_archives'] = null;
$usage[] = 
    "[--use_modules=XXX,YYY,ZZZ] If set, a comma separated list of module we want to use.  If not set, use all available modules\n";
$usage[] = 
    "[--template_dir=\$template_dir]: The directory to store .pot template files in\n"
    ."\tIf not set, we use $template_dir\n" ;
$usage[] = 
    "[--read_po_files=\$read_po_files]: Tries to read .po files for the given locale rather than an export\n\tDefaults to true\n"
    ."[--use_archive=]: Set to true to use an translation of archives.\nSet to false to use the .po files found under $template_dir\n\tDefaults to false\n"
    . "[--archive=\$archive]: The archive consisting of all translationd\n\tDefaults to $archive\n"
    . "[--locales=\$locale1,\$locale2..\$localeN]: The locales we wish to translate for\n"
    . "\tIf not specified, it uses  every valid subdirectory of in the translations archive file\n";
$booleans['read_po_files'] = true;
$set_configs  = false;
require_once('translate_base.php');


include_once('PHPExcel/PHPExcel.php');
include_once 'PHPExcel/Writer/Excel2007.php';
if (! class_exists('PHPExcel',false)) {
    I2CE::raiseError("Please install PHPExcel (http://phpexcel.codeplex.com/):\n\tpear channel-discover pear.pearplex.net\n\tpear install pearplex/PHPExcel\n");
}

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
        break;
    case 'use_modules':
        $use_modules = preg_split('/,/',$val,-1,PREG_SPLIT_NO_EMPTY);
    }   
}
if (!$booleans['read_po_files']) {
    $archive = realpath($archive);
    if (!is_readable($archive)) {
        usage("The file $archive is not readable");
    }


    I2CE::raiseError("Using archive: " . realpath($archive)); 
//$tar =  new Archive_Tar($archive, $compression);
    $tar =  new Archive_Tar($archive);
//$tar->setErrorHandling(PEAR_ERROR_PRINT);
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
}

if ($locales == false) {
    $locales = array();
    if (!$booleans['read_po_files']) {
        foreach ($tar_dirs as $dir) {
            if (preg_match('/^([a-zA-Z_\-]+)\/LC_MESSAGES\/$/', $dir,$matches)) {
                $locales[] = $matches[1];
            }
        }
    } else {
        $files= glob($template_dir . DIRECTORY_SEPARATOR  . '*' . DIRECTORY_SEPARATOR  . '*.po');
        foreach ($files as $file) {
            $locale = basename($file, '.po');
            $locales[$locale] = true;
        }
        $locales = array_keys($locales);
    }
} else {
    foreach ($locales as $i=>$locale) {
        if ( !$booleans['read_po_files'] && !in_array($locale .'/' ,$tar_dirs)) {
            I2CE::raiseError( "WARNING: the locale $locale does not exist in " . basename($archive));
            //unset($locales[$i]);
        }
    }
}


$found_modules = getAvailableModules();
if (count($locales)==0) {
    usage("No valid locales specified");
}
if (is_array($use_modules)) {
    echo "Is array\n";
    foreach ($found_modules as $found_module=>$top_level_module) {
        echo "\tLooking at $found_module\n";
        if (in_array($found_module,$use_modules)) {
            continue;
        }
        echo "Rmoved $found_module\n";
        unset($found_modules[$found_module]);
    }
}
if (count($found_modules) == 0) {
    usage("No modules found");
}
I2CE::raiseError( "Creating translation spreadsheet for locales:\n\t" . implode(',',$locales) );


$basename = launchpad(basename(getcwd()));
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
    I2CE::raiseError( "Translating on Locale $locale");

    $translations_array = array();
    $pot_array = array();
    //echo implode(",", array_keys($found_modules));
    foreach ($found_modules as $module=>$top_module) {
        $launchpad_name = launchpad($module);
        $pot_file = $template_dir . DIRECTORY_SEPARATOR . $launchpad_name . DIRECTORY_SEPARATOR . $launchpad_name . '.pot';
        $existing_template = loadPOT($pot_file);
        if ($existing_template === false) {
            continue;
        }
        $pot_array[$module] = $existing_template;
        
    }
    if (count($pot_array) == 0) {
        //continue;
    }
    foreach ($found_modules as $module=>$top_module) {
        $translations_array[$module] = array();
        if (!array_key_exists($module,$pot_array)) {
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
            $po_file = $template_dir   . DIRECTORY_SEPARATOR . launchpad($module) . DIRECTORY_SEPARATOR . $locale . '.po';
            if (!is_readable($po_file)) {
                //I2CE::raiseError($po_file  . " is not readable for $module");
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
        if (!is_array($translations_array[$module]) ) {
            $translations_array[$module] = array();
        }            
    }
    $file = $template_dir . DIRECTORY_SEPARATOR . $basename . '-translations-' . $locale . '.xls';
    $workbook = new PHPExcel();
    if (!$workbook instanceof PHPExcel) {
	I2CE::raiseError("Could not create workbook");
        continue;
    }
    //$workbook->setVersion(8);
    $sheet_indx = 0;
    foreach ($pot_array as $module =>$pot) {
        if (count($pot) <= 1) { 
            //nothing to translate
            continue;
        }
        $t_module = launchpad($module);
        $chops = array('ihris-','manage-','qualify-','common-','i2ce');
        foreach ($chops as $chop) {
            if (substr($t_module,0,strlen($chop)) == $chop) {
                $t_module = substr($t_module,strlen($chop));
            }
        }
        $ws = substr($t_module,0,31);
        //$worksheet = $workbook->addWorksheet($ws);
	$workbook->createSheet($sheet_indx);
	echo "Sheet $ws: $sheet_indx\n";
	$workbook->setActiveSheetIndex($sheet_indx);
        $worksheet = $workbook->getActiveSheet();
        if (!$worksheet instanceof PHPExcel_Worksheet ) {
	    I2CE::raiseError($worksheet,"Can't add worksheet");
            continue;
        }
	$sheet_indx++;
	$worksheet->setTitle($ws);
        $worksheet->getColumnDimension('A')->setWidth(75);
        $worksheet->getColumnDimension('B')->setWidth(75);
        //        $worksheet->setInputEncoding('UTF-8');
        $translations = $translations_array[$module];
        $row = 0;
        foreach (array_keys($pot) as $msgstr) {
            if (strlen(trim($msgstr)) == 0) {
                continue;
            }	
            $row++;
            $worksheet->SetCellValue("A" . $row,$msgstr);
            if ($trans = translate($msgstr)) {
		$worksheet->SetCellValue("B" . $row,$trans);
                //$worksheet->writeString($row,1,$trans);
	    }
        }

    }
    $writer = new PHPExcel_Writer_Excel2007($workbook);
    $writer->save($file);

}




// function translate($text) {
//     global $changed_text;
//     global $translations;
//     if (!is_string($text)) {
//         return false;
//     }
//     if (!preg_match('/^(\s*)(.*?)(\s*)$/s',$text,$matches)) {
//         die("BAD [$text]\n");
//     }    
//     if (strlen($matches[2]) == 0) {
//         return false;
//     }
//     if (!array_key_exists($matches[2],$translations)) {
//         return false;
//     }
//     $trans = $translations[$matches[2]];
//     if (!is_string($trans)) {
//         return false;
//     }
//     $trans = trim($trans);
//     if (strlen($trans) == 0) {
//         $trans = false;
//     } else  if ($trans === $matches[2]) {
//         $trans = false;
//     } else {
//         $changed_text = true;
//     }
//     if ($trans) {
//         return $matches[1] . $trans . $matches[3];
//     } else {
//         return $trans;
//     }
// }
