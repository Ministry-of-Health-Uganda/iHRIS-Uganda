#!/usr/bin/php
<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * This File is part of I2CE
 * 
 * I2CE is free software; you can redistribute it and/or modify
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
 * Change the template function calls that have HTML in them to be the same but without the HTML.
 * Thus we can use the updated and cleaned templatemaster and template classes.
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>, Mark Hershberger <mah@everybody.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 */

require_once ("Console/Getopt.php");  




$changes = array(
    '/loadHTMLFile/'=>'loadFile',
    '/addHTMLFile/'=>'addFile',
    '/appendHTMLFileById/'=>'appendFileById',
    '/appendHTMLFileByName/'=>'appendFileByName',    
    '/appendHTMLFileByNode/'=>'appendFileByNode',
    '/importHTMLText/'=>'importText', 
    '/addHTMLText/'=>'addText', 
    '/appendHTMLTextById/'=>'appendTextById',
    '/appendHTMLTextByName/'=>'appendTextByName'
    );




$just_show = false;

function fix_up_file($file) {
    global $just_show;
    global $changes;
    $contents = file($file); 
    $matches = array();
    foreach($contents as $number=>$line) {
        foreach ($changes as $in=>$out) {
            if (preg_match($in, $line)) {
                $matches[$number] = $line;                                
                $line = preg_replace($in,$out,$line);
                $contents[$number] = $line;
            }
        }
    }
    if (count($matches) == 0) {
        return;
    }
    if ($just_show) {
        echo "\nThe following changes would be made to $file:\n";
    } else {
        echo "\nThe following changes are being made to $file:\n";
    }
    foreach ($matches as $number=>$line) {
        echo "Line $number:\n\t" . rtrim($matches[$number]) . "\t=>\n\t{$contents[$number]}";        
    }
    if (!$just_show) {
        $fh = fopen($file,"w");
        foreach ($contents as $line) {
            fwrite($fh,$line);
        }
        fclose($fh);                
    } 

}


function run_files($file) {
    if (is_link($file)) {
        return;
    }
    if (is_dir ($file . '/.')) {
        $dh = opendir($file);
        $files = array();
        while (false !== ($f = readdir($dh))) {
            if ($f[0] !='.') {
                $files[] = $file . '/' . $f;
            }
        }
        closedir($dh);
        foreach ($files as $f) {
            run_files($f);
        }
    } else {
        if (basename($file) == __FILE__) {
            echo "Skipping " . __FILE__ . "\n";
            continue;
        }
        if (preg_match('/\.php$/',$file)) {
            fix_up_file($file);
        }

    }
}


function usage($verbose = false) {
    echo "Usage: [-l] [-h] [-m] [--mode='mode'] dir_1 dir_2 dir_3 ... dir_n\n";

    if($verbose) {
        echo<<<EOF
 -h        Display this help message.
 -n        Just print what would be changed
EOF;
    }
    die();
}


function main() {
    global $just_show;
    $shortopts = "hn"; 
    $cg = new Console_Getopt(); 
    $dirs = $cg->readPHPArgv(); 
    array_shift($dirs);            /* Trash $0, the name of the script */
    $ret = $cg->getopt2($dirs, $shortopts, $longopts);

    if(PEAR::isError($ret)) {
        echo $ret->getMessage(), "\n\n";
        usage();
    }
    list($opts, $dirs) = $ret;

    foreach($opts as $opt) {
        list($o, $val) = $opt;
        switch($o) {
        case 'h':
            usage(true);
            break;

        case 'n':
            $just_show = true;
            break;

        default:
            echo "Unknown option: $o\n\n";
            usage(true);

        }
    }
            
    if (count($dirs) == 0 ) {
        usage();
    }

    foreach ($dirs as $dir) {
        run_files($dir);
    }
}

main();
