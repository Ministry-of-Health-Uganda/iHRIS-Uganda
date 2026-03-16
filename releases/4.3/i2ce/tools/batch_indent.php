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
 * batch indent
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software Foundation; either 
 * version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
 * received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
 */



$el_file = dirname(__FILE__) . '/batch_indent.el';

function indent($file) {
    global $el_file;
    $blue = "\033[34m";
    $black = "\033[0m";
    $file = realpath($file); //just so it looks pretty
    echo $blue . "Indenting on $file:\n" . $black;
    system("emacs -batch $file -l $el_file -f emacs-format-function"); 
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
        if (preg_match('/\.php$/',$file)) {
            indent($file);            
        }

    }
}




require_once ("Console/Getopt.php");  
$cg = new Console_Getopt();  
$dirs = $cg->readPHPArgv(); 

array_shift($dirs );
if (count($dirs) == 0 ) {
    echo "Usage: dir_1 dir_2 dir_3 ... dir_n";
    die();
}

foreach ($dirs as $dir) {
    run_files("./$dir");
}



?> # Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
