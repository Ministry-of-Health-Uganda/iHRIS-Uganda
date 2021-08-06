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
 * Mode line insertion for php files
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 */

require_once ("Console/Getopt.php");  

function fix_up_file($file, $license, $mode_line, $maxlen, $just_show) {
    $blue = "\033[36m";
    $red = "\033[31m";
    $yellow = "\033[33m";
    $black = shell_exec("tput sgr0");
    $year = strftime("%Y");
    $longlines = 0;
    $mode_line_index = 0;
    $shebang = "#!/usr/bin/php";
    $localvar_start = null;
    $localvar_end = null;
    $file_modified = false;

    echo "Checking $file...\n";
    $short_path = $file;
    $file = realpath($file);

    $lines = file($file);       /* Slurp in the whole file. */
    if($lines === false) {
        echo "{$red}Problem opening $short_path for reading.{$black}\n\n";
        return;
    }

    /* Skip the first line if it has a shebang, mode lines can go on
       the line after that. */
    $first_line = rtrim($lines[$mode_line_index]);
    if (substr($first_line, 0, strlen($shebang)) === $shebang) {
        $mode_line_index++;
        $first_line = rtrim($lines[$mode_line_index]);
    }

    /* Check for the line */
    if (preg_match('{^(.*)(?://|#)\s*-\*-\s+mode:\s+php;\s(.*)\s-\*-(.*)}',
                   $first_line, $matches)) {

        /* Remove it if it is there. */
        if (isset($matches[2]) && $matches[2] !== "") {
            $first_line = $matches[1];
        }
    }
    $first_line = rtrim($first_line);

    if ("$first_line\n" !== $lines[$mode_line_index]) {
        echo "\t{$blue}Updating modeline: $lines[$mode_line_index]{$black}";
        $lines[$mode_line_index] = "$first_line\n";
        $file_modified = true;
    }

    /* Scan through the rest of the file looking */
    $in_php = false;
    foreach ($lines as $i => $l) {

        /* Update copyright year if necessary */
        if ($license &&
            preg_match("{(.*(?:©|&copy;)?\s*Copyright\s*(?:©|&copy;)?\s)".
                       "([0-9, ]+)(\s.*)}",
                          $l, $matches)) {
            if (strpos($matches[2], $year) === false) { /* could return 0 */
                echo "\t{$blue}Updating copyright: $matches[2]{$black}\n";
                $l = "$matches[1]$matches[2], $year$matches[3]\n";
                $file_modified = true;
            }
        }

        /* Count extra long lines */
        if(strlen($l) > $maxlen) {
            $longlines++;
        }

        /* Find the LocalVar section. */
        if (preg_match('{^# Local Variables:}', $l)) {
            $localvar_start = $i;
        }
        if (preg_match('{^# End:}', $l) && $localvar_start !== null) {
            $localvar_len = $i - $localvar_start + 1;
        }

        $lines[$i] = $l;
    }

    /* Place the LocalVar section at the end if we didn't find one */
    if(null === $localvar_start) {
        $localvar_len = 0;
        $localvar_start = count($lines);
        $file_modified = true;
    }

    /* Splice in the LocalVar section. */
    $lvars = array_filter(preg_split('{\s*;\s*}', $mode_line));
    $lvar_lines[] = "# Local Variables:\n";
    $lvar_lines[] = "# mode: php\n";
    foreach($lvars as $l) {
        $lvar_lines[] = "# $l\n";
    }
    $lvar_lines[] = "# End:\n";

    /* Remove the final ?> if needed */
    if (substr($lines[$localvar_start-1], 0) === "?>\n" ||
        substr($lines[$localvar_start-1], 0) === "?>") {
        $lines[$localvar_start-1] = "\n";
        $file_modified = true;
    }

    /* Update the changed Local Variables section if needed. */
    if(count($lvar_lines) != $localvar_len) {
        echo "\t{$blue}Updating Local Vars{$black}\n";
        $file_modified = true;
    } else {
        foreach($lvar_lines as $i => $l) {
            if($l !== $lines[$localvar_start+$i]) {
                echo "\t{$blue}Updating Local Vars{$black}\n";
                $file_modified = true;
            }
        }
    }
    array_splice($lines, $localvar_start, $localvar_len, $lvar_lines);

    if($longlines > 0) {
        echo "\t${yellow}Number of long lines: $longlines{$black}\n";
    }

    if(!$just_show && $file_modified) {
        $fh = @fopen($file,"w");
        if($fh === false) {
            echo "{$red}Problem opening $short_path for writing{$black}\n\n";
            return;
        }

        foreach($lines as $l) {
            fwrite($fh,$l);
        }
        fclose($fh);
    }
    echo "\n";
}

function run_files($file, $license, $mode_line, $maxlen, $just_show) {
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
            run_files($f, $license, $mode_line, $maxlen, $just_show);
        }
    } else {
        if (preg_match('/\.php$/',$file)) {
            fix_up_file($file, $license, $mode_line, $maxlen, $just_show);
        }

    }
}

function usage($verbose = false) {
    echo "Usage: [-l] [-h] [-m] [--mode='mode'] dir_1 dir_2 dir_3 ... dir_n\n";

    if($verbose) {
        echo<<<EOF
 -h        Display this help message.
 -l        Update the license as well as the mode line.
 -m        Print the default mode line and exit.
 -n        Just print what would be changed
 --mode="line"
           Override the default mode line
 --maxlen=#
           Maximum line length to warn on.

EOF;
    }
    die();
}


function main() {
    $shortopts = "lhmn"; 
    $longopts  = array("mode=","maxlen="); 
    $maxlen = 80;
    $mode_line = 'c-default-style: "bsd"; indent-tabs-mode: nil; '.
        'c-basic-offset: 4;';
    $cg = new Console_Getopt(); 
    $dirs = $cg->readPHPArgv(); 
    array_shift($dirs);            /* Trash $0, the name of the script */
    $ret = $cg->getopt2($dirs, $shortopts, $longopts);
    $license = false;
    $just_show = false;

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

        case 'm':
            echo "Mode Line: $mode_line\n";
            die();
            break;

        case '--mode':
            $mode_line = $val;
            break;

        case '--maxlen':
            $maxlen = $val;
            break;

        case 'l':
            $license = true;
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
        run_files($dir, $license, $mode_line, $maxlen, $just_show);
    }
}

main();


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
