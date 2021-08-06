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
 * Apache Tail
 *
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2008, 2008 IntraHealth International, Inc. 
 * @version 1.0
 */
require_once ("Console/Getopt.php");  
require_once ("CLI.php");  
$cg = new Console_Getopt();  
$args = $cg->readPHPArgv(); 

array_shift($args );
if (count($args) > 2 ) {
    echo "Usage: apache_tail.php (log_file)\n\t If log file is not given, we assume /var/log/apache2/error.log\n";
    echo "Usage: apache_tail.php (number)\n\t  we assume /var/log/apache2/error.log. Number is multiple in 1000 bytes to backup the file\n";
    echo "Usage: apache_tail.php log_file (number)\n\t Where number is the multiple in 1000 bytes to reverse in the file.\n";
    die();
}
$file = '/var/log/apache2/error.log';
$back = 5000;
foreach ($args as $arg) {
    if (ctype_digit($arg)) {
        $back = 1000 * ((int) ($arg));
    } else {
        $file = $arg;
    }
}


$blue = "\033[34m";
$black = "\033[0m";
$red = "\033[31m";
$green = "\033[32m";


function highlightText($text) {
    global $highlight;
    $text = processText($text);
    return str_ireplace($highlight, "\033[1m"  .  $highlight . "\033[0m",$text);
}
function processText($text) {
    global $blue;
    global $black;
    global $red;
    global $green;
    $color = $blue;
    if (preg_match('/^\s*(.*)\s*(\\n)?,?\s*Error Type=([0-9]*),?\s*(.*)\s*$/',$text,$matches)) {
        $t_text =$matches[1] . ' ' . $matches[4];
        $type = (int)$matches[3];
        switch($type) {
        case E_USER_WARNING:
            $color = $green;
            break;
        case E_USER_ERROR;
            $color = $red;
            break;
        default;         
        break;
        }
    } else {
        $t_text = trim($text);
    }
    $t_text = str_replace('\t',"\t",$t_text);
    if (preg_match('/^(.*?)\s*(I2CE:.*php\:\d+\\)):\s*(.*)\s*(\\\\n)?\s,?\s*referer\s*(.*)\s*$/',$t_text,$matches)) {
        $matches[2] = str_replace('\n',"\n\t      ",$matches[2]);
        $matches[3] = str_replace('\n',"\n\t",$matches[3]);
        $matches[3] = preg_replace('/\s*$/m','',$matches[3]);
        return  $matches[1] . "\n\tRefered by " . $matches[5] . "\n\t" . $matches[2] . "\n\t" . $color. $matches[3] .  "\n" . $black;
    } else if (preg_match('/^(.*?)\s*(I2CE:.*?__autoload):\s*(.*)\s*,?\s*referer:?\s*(.*)\s*$/',$t_text,$matches)) {
        $matches[2] = str_replace('\n',"\n\t      ",$matches[2]);
        $matches[3] = str_replace('\n',"\n\t",$matches[3]);
        $matches[3] = preg_replace('/\s*$/m','',$matches[3]);
        return  $matches[1] . "\n\tRefered by " . $matches[4] .  "\n\t" . $matches[2] . "()\n\t" . $color. $matches[3]. "\n" . $black;
    } else if (preg_match('/^(.*?)\s*(I2CE:.*?__call):\s*(.*)\s*,?\s*referer:?\s*(.*)\s*$/',$t_text,$matches)) {
        $matches[2] = str_replace('\n',"\n\t      ",$matches[2]);
        $matches[3] = str_replace('\n',"\n\t",$matches[3]);
        $matches[3] = preg_replace('/\s*$/m','',$matches[3]);
        return  $matches[1] . "\n\tRefered by " . $matches[4] .  "\n\t" . $matches[2] . "()\n\t" . $color. $matches[3]. "\n" . $black;
    } else if (preg_match('/^(.*?)\s*(I2CE:.*?__call):\s*(.*)\s*$/',$t_text,$matches)) {
        $matches[2] = str_replace('\n',"\n\t      ",$matches[2]);
        $matches[3] = str_replace('\n',"\n\t",$matches[3]);
        $matches[3] = preg_replace('/\s*$/m','',$matches[3]);
        return  $matches[1] . "\n\t" .  $matches[2] . "()\n\t" . $color. $matches[3]. "\n" . $black;
    } else if (preg_match('/^(.*?)\s*(I2CE:.*php\:\d+\\)):\s*(.*)\s*(\\\\n)?\s*$/',$t_text,$matches)) {
        $matches[2] = str_replace('\n',"\n\t      ",$matches[2]);
        $matches[3] = str_replace('\n',"\n\t",$matches[3]);
        $matches[3] = preg_replace('/\s*$/m','',$matches[3]);
        return $matches[1]  ."\n\t" .  $matches[2] . "\n\t" . $color . $matches[3] . "\n". $black;
    } else if (preg_match('/^(.*\[client\s\d+\.\d+\.\d+\.\d+\])\s*\\(:\\):\s*(.*)\s*(\\\\n)?\s*,?\s*referer:?\s*(.*)\s*$/',$t_text,$matches)) {
        $matches[2] = str_replace('\n',"\n\t      ",$matches[2]);
        $matches[2] = preg_replace('/\s*$/m','',$matches[2]);
        return $matches[1] . "\n\tRefered by " . $matches[4] . "\n\t" . $color. $matches[2] . "\n" . $black;
    } else if (preg_match('/^(.*?)\s*(I2CE:.*)\s*\\(:\\):\s*(.*)\s*([Cc]alled.*php)\s*(\\\\n)?\s*(.*)\s*,?\s*referer:?\s*(.*)\s*$/',$t_text,$matches)) {
        $matches[2] = str_replace('\n',"\n\t      ",$matches[2]);
        $matches[6] = str_replace('\n',"\n\t",$matches[6]);
        $matches[6] = preg_replace('/\s*$/m','',$matches[6]);
        return $matches[1]  . "\n\tRefered by " . $matches[7] ."\n\t" . $matches[4] .
            "\n\t" . $matches[2] .  $color."\n\t" . $matches[3] . "\n\t" . $matches[6] . "\n" . $black; 
    } else if (preg_match('/^(.*?)\s*(I2CE:.*)\s*\\(:\\):\s*(.*)\s*([Cc]alled.*php)\s*(\\\\n)?\s*(.*)\s*$/',$t_text,$matches)) {
        $matches[2] = str_replace('\n',"\n\t      ",$matches[2]);
        $matches[6] = str_replace('\n',"\n\t",$matches[6]);
        $matches[6] = preg_replace('/\s*$/m','',$matches[6]);
        return $matches[1]  . "\n\t" . $matches[4] . 
            "\n\t" . $matches[2] .$color. "\n\t" . $matches[3] . "\n\t" . $matches[6] . "\n" . $black; 
    } else if (preg_match('/^(.*?)\s*(I2CE:\s*.*?):\s*(.*)\s*,?\s*referer:?\s*(.*)\s*$/',$t_text,$matches)) {
        $matches[2] = str_replace('\n',"\n\t      ",$matches[2]);
        $matches[3] = str_replace('\n',"\n\t",$matches[3]);
        $matches[3] = preg_replace('/\s*$/m','',$matches[3]);
        return $matches[1]  . "\n\tRefered by " . $matches[4] ."\n\t" . $matches[2] .
            "\n\t$color" . $matches[3] .   "$black\n";
    } else if (preg_match('/^(.*?)\s*(I2CE: :Fatal Error:)\s*(.*)\s*$/',$t_text,$matches)) {
        $matches[3] = str_replace('\n',"\n\t      ",$matches[3]);
        $matches[3] = rtrim(str_replace('\n',"\n\t",$matches[3]));
        return $matches[1] . "\n" . $red . "************FATAL ERROR************:\n" . $matches[3] . "\n************FATAL ERROR************" . $black . "\n";
    } else if (preg_match('/^(.*?)\s*(I2CE:)\s*(.*)\s*(\\\\n)?\s,?\s*referer\s*(.*)\s*$/',$t_text,$matches)) {
        $matches[2] = str_replace('\n',"\n\t      ",$matches[2]);
        $matches[3] = str_replace('\n',"\n\t",$matches[3]);
        $matches[3] = preg_replace('/\s*$/m','',$matches[3]);
        return  $matches[1] . "\n\tRefered by " . $matches[5] . "\n\t" . $matches[2] . " " . $color. $matches[3] .  "\n" . $black;
    } else if (preg_match('/^(.*?)\s*(I2CE:)\s*(.*)\s*(\\\\n)?\s*$/',$t_text,$matches)) {
        $matches[2] = str_replace('\n',"\n\t      ",$matches[2]);
        $matches[3] = str_replace('\n',"\n\t",$matches[3]);
        $matches[3] = preg_replace('/\s*$/m','',$matches[3]);
        return $matches[1]  ."\n\t" .  $matches[2] . " " . $color . $matches[3] . "\n". $black;
    } else if (preg_match('/Segmentation fault/',$t_text) || preg_match('/glibc detected/',$t_text)) {
        return $red . "\n" . $t_text . $black . "\n";
    } else { 
        return $text; 
    } 
} 


## The tail function is based on http://draperyfalls83.livejournal.com/2656.html
## but it didn't work so i rewrote most of it
## I added in the processText function

$curPosition = 0;
$curSize = 0;

//set functions
function closeFile(&$handle)
{
        fclose($handle);
}

function openFile(&$file, &$handle,$back = 5000)
{
    $handle = fopen($file, 'r');
    if ($handle === false) {
        die("Unable to open file $file for reading");
    }
    $curSize = filesize($file);
    fseek($handle, $curSize - $back);
    fgets($handle);

}

function resetFile(&$file, &$handle)
{
    closeFile($handle);
    openFile($file, $handle);
}


openFile($file, $handle,$back);

$msg = trim(getNextMessage($handle));
if ($msg) {
    echo processText($msg);
}

    
$highlight = '';
while (true) {
    $resp = strtolower(trim(ask("\n\t{$red}nXXX{$black}search forward for string XXX or {$red}pXXX{$black}search backwards for string XXXX\n\t{$red}f#{$black}forward by optional amount or {$red}b#{$black}backwards by optional amount\n\t{$red}fMMM DD{$black}date forward or {$red}bMMM DD{$black}date backwards\n\t{$red}fHH:MM{$black}time forward {$red}bHH:MM{$black}time backwards\n\t{$red}hXXX{$black}highlight text[$highlight]\n\t{$red}q{$black}quits",2000,false)));
    $msg = false;
    $amount = 1;
    if (!$resp) {
        continue;
    }
    $date = false;
    $code = $resp[0];
    $resp = strtolower(trim(substr($resp,1)));
    $amount = 1;
    $date = false;
    $time = false;
    if (preg_match('/[a-zA-Z]([0-9]+)/',$resp,$matches)) {
        $amount = max(1,$matches[1]);
    } else if  (preg_match('/[a-zA-Z]+\s+[0-9]+/',$resp)) {
        $amount = false;
        $date = $resp;
    } else if  (preg_match('/[0-9]+:[0-9]+/',$resp)) {
        $amount = false;
        $time = $resp;
    }    
    if ($code == 'q') {
        break;
    } else if ($code == 'h') {
        $highlight = $resp;
    } else if ($code == 'p' ) {
        do {
            if ( ! ($msg = getPreviousMessage($handle))) {
                break;
            }
            echo highlightText($msg);
            if (!$resp) {
                $found = true;
            } else {
                $found = strstr(strtolower($msg),$resp) !== false;
            }
        } while (!$found);
    } else if ($code == 'n' && $resp) {
        do {
            if ( ! ($msg = getNextMessage($handle))) {
                break;
            }
            echo highlightText($msg);
            if (!$resp) {
                $found = true;
            } else {
                $found = strstr(strtolower($msg),$resp) !== false;
            }
        } while (!$found);
    } else if ($code == 'f') {
        if ($amount) {
            $msg = getNextMessage($handle,$amount);
            if ($msg) {
                echo highlightText($msg);
            }
        } else if ($date) {
            $found_date =false;
            while ($found_date != $date) {
                if ( ! ($msg = getNextMessage($handle))) {
                    break;
                }
                if (preg_match('/([a-zA-Z]+)\s+([a-zA-Z]+)\s+([0-9]+)/',$msg,$matches)) {
                    $found_date = strtolower($matches[2] . ' ' . $matches[3]);
                    echo highlightText($msg);
                } else {
                    $msg = false;
                    break;
                }
            }
            if ($found_date != $date) {
                echo "{$red}Date $date not found{$black}\n";
            }
        } else {
            //time should be set
            $found_time =false;
            while ($found_time != $time) {
                if ( ! ($msg = getNextMessage($handle))) {
                    break;
                }
                if (preg_match('/([a-zA-Z]+)\s+([a-zA-Z]+)\s+([0-9]+)\s+([0-9]+):([0-9]+)/',$msg,$matches)) {
                    $found_time = strtolower($matches[4] . ' ' . $matches[5]);
                    echo highlightText($msg);
                } else {
                    $msg = false;
                    break;
                }
            }
            if ($found_time != $time) {
                echo "{$red}[$found_time != $time]Time $time not found{$black}\n";
            }
        }
    } else if ($code =='b') {
        if ($amount) {
            $msg = getPreviousMessage($handle,$amount);
            if ($msg) {
                echo highlightText($msg);
            }            
        } else if ($date) {
            $found_date =false;
            while ($found_date != $date) {
                if ( ! ($msg = getPreviousMessage($handle)) ) {
                    break;
                }
                if (preg_match('/([a-zA-Z]+)\s+([a-zA-Z]+)\s+([0-9]+)/',$msg,$matches)) {
                    $found_date = strtolower($matches[2] . ' ' . $matches[3]);
                    echo highlightText($msg);
                } else {
                    $msg = false;
                    break;
                }
            }
            if ($found_date != $date) {
                echo "{$red}Date $date not found{$black}\n";
            }
        } else {
            //time should be set
            $found_time =false;
            while ($found_time != $time) {
                if ( ! ($msg = getPrevious($handle))) {
                    break;
                }
                if (preg_match('/([a-zA-Z]+)\s+([a-zA-Z]+)\s+([0-9]+)\s+([0-9]+):([0-9]+)/',$msg,$matches)) {
                    $found_time = strtolower($matches[4] . ' ' . $matches[5]);
                    echo highlightText($msg);
                } else {
                    $msg = false;
                    break;
                }
            }
            if ($found_time != $time) {
                echo "{$red}[$found_time != $time]Time $time not found{$black}\n";
            }

        }
        
    }
}


function getPreviousMessage($handle,$amount =1) {
    $curPos = ftell($handle);
    if ($curPos == 0)  {
        return false;
    }
    fseek($handle, $curPos -1);
    $ch = false;
    $amount = max (1,$amount);
    for ($i=0; $i < $amount; $i++) {
        $msg = '';        
        $curPos = ftell($handle);
        while($ch != "\n" && $curPos > 0){
            fseek($handle, $curPos -1);
            $curPos = ftell($handle);
            $ch = fgetc($handle);
            
            if($ch != "\n" && $ch != "\r") {
                $msg = $ch . $msg;
            }
        }

    }
    return $msg;
}

function getNextMessage($handle,$amount =1) {
    $msg = false;
    $amount = max (1,$amount);
    for ($i =0; $i < $amount; $i++) {
        if (feof($handle) === true) {
            break;
        }
        $msg = fgets($handle);
    }
    return $msg;
}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
