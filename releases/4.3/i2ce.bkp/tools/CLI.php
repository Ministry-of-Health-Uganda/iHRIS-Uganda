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

error_reporting(E_ALL);
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib');

$blue = "\033[34m";
$black = "\033[0m";
$red = "\033[31m";
$green = "\033[32m";


if (!isset($booleans) || !is_array($booleans)) {
    $booleans[] = array();
}
if (!isset($usage) || !is_array($usage)) {
    $usage = array();
}


@require_once ("Console/Getopt.php");  
if (!class_exists('Console_Getopt')) {
    usage('Please install the PEAR Console_Getopt package');
}


function usage($msg = '',$die = true) {
    global $usage;
     if ($msg) {
        echo $msg . "\n";
    }
    $debug = debug_backtrace();
    $file = basename($debug[count($debug)-1]['file']);
    echo "Usage: " . $file . "\n";
    echo implode("",$usage);
    if ($die) {
        die();
    }
}



$cg = new Console_Getopt();  
$t_args = $cg->readPHPArgv(); 
$modules =false;
array_shift($t_args);
$args = array();


$arg_files  = array();
while (count($t_args) > 0) {
    $arg = array_shift($t_args);
    list($key,$val) = array_pad(explode('=',$arg),2,'');
    if (strlen($key)  == 0) {
        usage( "Bad argument $key");
    }
    if (substr($key,0,2) == '--') {
        if (strlen($key) < 3) {
            usage( "Bad argument $key");
        }
        $key = substr($key,2);        
        if ($key  == 'help') {
            usage(); 
        }
    } else if ($key[0] == '-') {
        if (strlen($key) < 2) {
            usage( "Bad argument $key");
        }
        $key = substr($key,1);        
        if ($key  == 'h') {
            usage(); 
        }
    } else {
        $arg_files[] = $arg;
        continue;
    }
    if (strlen($val) == 0) {
        usage("Bad value [$val] for argument $key");
    }
    switch($key) {
    case 'search_dirs':
        $search_dirs = preg_split('/,/',$val,-1,PREG_SPLIT_NO_EMPTY);
        if (count($search_dirs) == 0) {
            usage("No valid search directories specified");
        }
        break;
    case 'modules':
        $modules = preg_split('/,/',$val,-1,PREG_SPLIT_NO_EMPTY);
        if (count($modules) == 0) {
            usage("No valid modules specified");
        }
        break;
    default:
        if (array_key_exists($key,$booleans)) {
            if ($val == '0' || (is_string($val) && strlen($val) > 0 && strtoupper(substr($val,0,1)) == 'F')) {
                $booleans[$key] = false;
            } else  if ($val == '1' || (is_string($val) && strlen($val) > 0 && strtoupper(substr($val,0,1)) == 'T')) {
                $booleans[$key] = true;
            }
        } else {
            $args[$key]=$val;
        }
        break;
    }
}




function prompt ($message,&$universal, $show=null) {
    global $red,$black;
    if ($universal === true) {
        return true;
    }
    if ($universal === false) {
        return false;
    }
    $message = trim($message);
    if ($show) {
        echo "$message\n({$red}Y{$black}es/{$red}N{$black}o/{$red}A{$black}lways/ne{$red}V{$black}er/{$red}S{$black}how): ";
    } else {
        echo "$message\n({$red}Y{$black}es/{$red}N{$black}o/{$red}A{$black}lways/ne{$red}V{$black}er): ";
    }
    $success =false;
    while (true) {
        $c = strtolower(fread(STDIN,1));
        if ($c=== false) {
            echo ("Bad input");
            die();
        }
        switch ($c) {
        case 'y':
            return true;
        case 'n':
            return false;
        case 'a':
            $universal = true;
            return true;
        case 'v':
            $universal = false;
            return false;
        case 's':
            if ($show) {
                echo "\n" . $show . "\n";
                echo "$message\n({$red}Y{$black}es/{$red}N{$black}o/{$red}A{$black}lways/ne{$red}V{$black}er): ";
                $show =false;
            }
            break;            
        default:
            break;
        }
    }
    echo "\n";
}


function simple_prompt ($message, $show = null) {
    global $red,$black;
    if ($show != null) {
        $show_message = "/{$red}S{$black}how";
    } else {
        $show_message = '';
    }
    
    $message = trim($message)  . "\n({$red}Y{$black}es/{$red}N{$black}o$show_message): ";
    echo $message;
    $success =false;
    while (true) {
        $c = strtolower(fread(STDIN,1));
        if ($c=== false) {
            echo ("Bad input");
            die();
        }
        switch ($c) {
        case 's':
            if ($show != null) {
                echo $show . "\n" . $message;
            }
            break;
        case 'y':
            return true;
        case 'n':
            return false;
        default:
            break;
        }
    }
    echo "\n";
}





function ask ($message, $length = 2000, $trim = true) {
    if ($trim) {
        $message = trim($message);
    }
    echo "$message: ";
    $success =false;
    return fread(STDIN,$length);
}





function getPassword($which = null, $stars = true) {
    if ($which === null) {
        $which = strtolower(basename($_SERVER['PHP_SELF'],'.php')) . 'z';
    }
    $prompt =  "Give фasswзrd. scяipt-кittч häx yør $which: ";
    $cat = <<<ENDOFCAT
         ,-""""""-.
      /\j__/\  (  \`--.
      \`@_@'/  _)  >--.`.
     _{.:Y:_}_{{_,'    ) )
    {_}`-^{_} ```     (_/
ENDOFCAT;
    echo $cat . "\n" .   $prompt;
                       
    //stolen from http://www.dasprids.de/blog/2008/08/22/getting-a-password-hidden-from-stdin-with-php-cli
    // Get current style
    $oldStyle = shell_exec('stty -g');
    
    if ($stars === false) {
        shell_exec('stty -echo');
        $password = rtrim(fgets(STDIN), "\n");
    } else {
        shell_exec('stty -icanon -echo min 1 time 0');
        
        $password = '';
        while (true) {
            $char = fgetc(STDIN);
            
            if ($char === "\n") {
                break;
            } else if (ord($char) === 127) {
                if (strlen($password) > 0) {
                    fwrite(STDOUT, "\x08 \x08");
                    $password = substr($password, 0, -1);
                }
            } else {
                fwrite(STDOUT, "*");
                $password .= $char;
            }
        }
    }
    
    // Reset old style
    shell_exec('stty ' . $oldStyle);
    echo "\nStølэnz!\n";                       
    // Return the password
    return $password;
}

                  
$su_pass = null;


function getSuPassword($which = null) {
    global $su_pass;
    if ($su_pass ===null) {
        $su_pass = getPassword($which);            
    }
    return $su_pass;
}





function sudo_exec($cmd, &$output = null, &$return_var = null, $su_pass = null) {
    //check if we need have sudo rights already
    if (!$su_pass) {
        $su_pass = getSuPassword();
    }
    $cmd = "/usr/bin/sudo -S " . $cmd;

    if (!is_array($output)) {
        $output = array();
    }
    $pipes = array();
    //See http://us3.php.net/manual/en/function.proc-close.php#56798

    $desc = array( 0 => array('pipe', 'r'),  1 => array('pipe', 'w'),    2 => array('pipe', 'w'));
    if (!is_resource($r = proc_open($cmd,$desc,$pipes))) {
        return false;
    }
    fwrite($pipes[0],$su_pass . "\n");
    fclose($pipes[0]);        
    do {
        //wait until it stops running
        $status = proc_get_status($r);
        if (!is_array($status)) {
            break;
        }
        if ($status['running']) {
            while (!feof($pipes[1])) {
                $output[] = rtrim(stream_get_line($pipes[1],1000000));
            }
        } else {
            break;
        }
    } while (true);

    fclose($pipes[1]);        
    fclose($pipes[2]);

    if (is_resource($r)) {
        proc_close($r);       
    }
    if ($status) {
        $return_val = $status['exitcode'];
    }
    end($output); 
    $last_line = current($output);
    return $last_line;
}

function sudo($cmd) {
    global $su_pass;
    if ($su_pass === null) {
        $su_pass = getPassword();
    }
    $pipes = array();
    if (is_resource($r = proc_open("sudo -S  " . $cmd,array( 0 => array('pipe', 'r'),  1 => array('pipe', 'w'),    2 => array('pipe', 'w')),$pipes))) {
        fwrite($pipes[0],$su_pass . "\n");
        fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]);
        $out2 = stream_get_contents($pipes[2]);
        fclose($pipes[1]);        
        fclose($pipes[2]);
        if (($ret = proc_close($r)) !=  0) {
            I2CE::raiseError("exit $ret for: $cmd:\n$out\n$out2\n");
        }
        return $out;
    } else {
        I2CE::raiseError("Could not sudo $cmd");
    }
}


function chooseMenuValue($msg,$options) {
    return $options[chooseMenuIndex($msg,$options)];
}


function chooseMenuIndex($msg,$options) {
    $menu = '';
    $black = "\033[0m";
    $red = "\033[31m";
    foreach ($options as $i=>$key) {
        $menu .= "\t$red$i$black) " . trim($key) . "\n";
    }
    do {
        $idx = trim(ask($msg . "\n" . $menu . "Choice"));
    } while (!array_key_exists($idx,$options));
    return $idx;
}




function chooseMenuIndices($msg,$options) {
    $indices = array();
    if (array_key_exists('Q',$options) || array_key_exists('q',$options)) {
        I2CE::raiseError("Not allowed to have the key 'q' or 'Q' in your options list");
        return $indices;
    }
    $menu = '';
    $black = "\033[0m";
    $red = "\033[31m";
    do {
        $menu = '';
        foreach ($options as $i=>$key) {
            if (in_array($i,$indices)) {
                $menu .= "\t$red$i$black [X]) " . trim($key) . "\n";
            } else {
                $menu .= "\t$red$i$black [ ]) " . trim($key) . "\n";
            }
        }
        $idx = trim(ask($msg . "\n" . $menu . "Please select an option or enter q to quit"));
        if (array_key_exists($idx,$options)) {
            if ( ($pos = array_search($idx,$indices)) !== false) {
                unset($indices[$pos]);
            } else {
                $indices[] = $idx;
            }
        } 
    } while (strtolower($idx) != 'q');
    return $indices;
}


function chooseDependentTreeMenuIndices($msg,$options, $indices = null) {
    if (!is_array($indices)) {
        $indices = array();
    }
    if (array_key_exists('Q',$options) || array_key_exists('q',$options)) {
        I2CE::raiseError("Not allowed to have the key 'q' or 'Q' in your options list");
        return $indices;
    }
    $menu = '';
    $black = "\033[0m";
    $red = "\033[31m";
    do {
        $menu = '';
        foreach ($options as $i=>$key) {
            $index = str_pad(((string)$i) . ')' ,8,' ');
            $num_tabs = strlen($key) - strlen(ltrim($key,"\t"));                
            $desc_pad = "\t" . str_pad('',strlen($index) + 4," ") . "\t "  . str_pad('',$num_tabs,"\t") . "  ";
            $key = str_replace("\n","\n" . $desc_pad,$key);
            if (in_array($i,$indices)) {
                $menu .= "\t" . $black . "[X] $red$index\t$black " . $key . "\n";
            } else {
                $menu .= "\t" . $black . "[ ] $red$index\t$black " . $key . "\n";
            }
        }
        $idx = trim(ask($msg . "\n" . $menu . "Please select an option or enter q to quit selection process"));
        if (array_key_exists($idx,$options)) {            
            if ( ($pos = array_search($idx,$indices)) !== false) {
                //we are disabling
                $enable = false;
                unset($indices[$pos]);
                //we also need to disable everything above us
            } else {
                //we are enabling.  
                $enable = true;
                $indices[] = $idx;
                $indices = array_unique($indices);
            }
            //now we need to enable/disable everything under what we selected
            $found = false;
            $num_tabs = strlen($options[$idx]) - strlen(ltrim($options[$idx],"\t"));                
            $under = false;
            foreach ($options as $k=>$v) {
                if ($k == $idx) {
                    $found = true;
                    $under = true;
                    continue;
                } 
                $sub_tabs = strlen($options[$k]) - strlen(ltrim($options[$k],"\t"));
                if ( (!$enable) ) {
                    //we are disabling
                    if  ($sub_tabs < $num_tabs) {
                        //we disable everything that is sitting above us
                        if ( !$found && ($p = array_search($k,$indices)) !== false) {                    
                            echo "disabling $k as it is aboce $idx\n";
                            unset($indices[$p]);
                        }
                    }

                }
                if (!$found) {
                    continue;
                }
                $sub_tabs = strlen($options[$k]) - strlen(ltrim($options[$k],"\t"));
                if ( $sub_tabs <= $num_tabs) {
                    $under = false;
                    //we have jumped up the hierarcy.  
                    if ($enable) {
                        //we can stop
                        break;
                    }
                }                
                if ($enable) {
                    $indices[] = $k;
                    $indices = array_unique($indices);
                } else {
                    //we are disabling
                    if ( $under && ($p = array_search($k,$indices)) !== false) {                    
                        echo "disabling $k because we have found it and are under $idx\n";
                        unset($indices[$p]);
                    }
                }
            }
        }
    } while (strtolower($idx) != 'q');
    return $indices;
}

