<?php
/**
* © Copyright 2011 IntraHealth International, Inc.
* 
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
* @package I2CE
* @subpackage I2CE
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.11.0
* @since v4.0.11.0
* @filesource 
*/ 
/** 
* Class I2CE_CLI
* 
* @access public
*/


require_once("I2CE_Fuzzy.php");

if (!class_exists('I2CE_CLI')) {
    class I2CE_CLI extends I2CE_Fuzzy{
        public static $blue = "\033[34m";
        public static $black = "\033[0m";
        public static  $red = "\033[31m";
        public static $green = "\033[32m";
        

        protected $usage = array();
        public function addUsage($usage) {
            $this->usage[] = $usage;
        }
    
    
    
        public function usage($msg = '',$die = true) {
            if ($msg) {
                echo $msg . "\n";
            }
            $debug = debug_backtrace();
            $file = basename($debug[count($debug)-1]['file']);
            echo "Usage: " . $file . "\n";
            echo implode("",$this->usage);
            if ($die) {
                die();
            }
        }
    
        protected $booleans = array();

        public function addBoolean($bool, $default_val = null) {
            $this->booleans[$bool] = $default_val;
        }

        public function _hasMethod($method,$getFuzzy = false,$returnErrors = false) {
            if (substr($method,0,11) == 'processArg_') {
                $key = substr($method,11);
                if ($key && array_key_exists($key,$this->processors)) {
                    return true;
                }
            }
            return parent::_hasMethod($method,$getFuzzy,$returnErrors);
        }
        
        public function __call($method,$params) {
            if (substr($method,0,11) == 'processArg_') {
                $key = substr($method,11);
                if ($key && array_key_exists($key,$this->processors)) {
                    return call_user_func_array($this->processors[$key],$params);
                }
            }
            return parent::__call($method,$params);
        }

        public function getArgFiles() {
            return $this->arg_fils;
        }
        protected $processors = array();
        public function addProcessor($key,$callback) {
            if (is_callable($callback)) {
                $this->processors[$key] = $callback;
            }
        }
        protected $arg_files = array();
        protected $processed = false;
        public function processArgs() {
            if ($this->processed) {
                return;
            }
            $this->processed = true;
            @require_once ("Console/Getopt.php");  
            if (!class_exists('Console_Getopt')) {
                $this->usage('Please install the PEAR Console_Getopt package');
            }
            $cg = new Console_Getopt();  
            $t_args = $cg->readPHPArgv(); 

            array_shift($t_args);
            $args = array();



            while (count($t_args) > 0) {
                $arg = array_shift($t_args);
                list($key,$val) = array_pad(explode('=',$arg),2,'');
                if (strlen($key)  == 0) {
                    $this->usage( "Bad argument $key");
                }
                if (substr($key,0,2) == '--') {
                    if (strlen($key) < 3) {
                        $this->usage( "Bad argument $key");
                    }
                    $key = substr($key,2);        
                    if ($key  == 'help') {
                        $this->usage(); 
                    }
                } else if ($key[0] == '-') {
                    if (strlen($key) < 2) {
                        $this->usage( "Bad argument $key");
                    }
                    $key = substr($key,1);        
                    if ($key  == 'h') {
                        $this->usage(); 
                    }
                } else {
                    $this->arg_files[] = $arg;
                    continue;
                }
                if (strlen($val) == 0) {
                    $this->usage("Bad value [$val] for argument $key");
                }
                $processor = 'processArg_' . $key;
                if ($this->_hasMethod($processor)) {
                    $this->setValue($key,$this->$processor($val));
                } else  if (array_key_exists($key,$this->booleans)) {
                    if ($val == '0' || (is_string($val) && strlen($val) > 0 && strtoupper(substr($val,0,1)) == 'F')) {
                        $this->booleans[$key] = false;
                    } else  if ($val == '1' || (is_string($val) && strlen($val) > 0 && strtoupper(substr($val,0,1)) == 'T')) {
                        $this->booleans[$key] = true;
                    }
                } else {
                    $this->setValue($key,$val);
                }
            }
        }

        protected $values = array();
        public function setValue($key,$value) {
            $this->values[$key] = $value;
        }
        public function hasValue($key) {
            return array_key_exists($key,$this->values);
        }
        public function getValue($key) {
            if ($this->hasValue($key)) {
                return $this->values[$key];
            } else {
                return null;
            }
        }

        protected function proccessArg_search_dirs($val) {
            $search_dirs = preg_split('/,/',$val,-1,PREG_SPLIT_NO_EMPTY);
            if (count($search_dirs) == 0) {
                $this->usage("No valid search directories specified");
            }
            return $search_dirs;
        }

        protected function proccessArg_modules($val) {
            $modules = preg_split('/,/',$val,-1,PREG_SPLIT_NO_EMPTY);
            if (count($modules) == 0) {
                $this->usage("No valid modules specified");
            }
            return $modules;
        }

        public function prompt ($message,&$universal, $show=null) {
            if ($universal === true) {
                return true;
            }
            if ($universal === false) {
                return false;
            }
            $message = trim($message);
            if ($show) {
                echo "$message\n(" . self::$red . "Y" . self::$black . "es/" . self::$red . "N" . self::$black. "o/" . self::$red . "A" . self::$black . "lways/ne" . self::$red . "V"  . self::$black . "er/" . self::$red . "S" . self::$black . "how): ";
            } else {
                echo "$message\n(" . self::$red . "Y" . self::$black . "es/" . self::$red . "N" . self::$black . "o/" . self::$red . "A" . self::$black . "lways/ne" . self::$red . "V" . self::$black . "er): ";
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
                        echo "$message\n(" . self::$red . "Y" . self::$black . "es/" . self::$red . "N" . self::$black . "o/" . self::$red . "A" . self::$black . "lways/ne" . self::$red . "V" . self::$black . "er): ";
                        $show =false;
                    }
                    break;            
                default:
                    break;
                }
            }
            echo "\n";
        }


        public function simple_prompt ($message, $show = null) {
            if ($show != null) {
                $show_message = "/" . self::$red . "S" . self::$black . "how";
            } else {
                $show_message = '';
            }
    
            $message = trim($message)  . "\n(" . self::$red . "Y" . self::$black . "es/" . self::$red . "N" . self::$black . "o$show_message): ";
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





        public function ask ($message, $length = 2000) {
            $message = trim($message);
            echo "$message: ";
            $success =false;
            return fread(STDIN,$length);
        }


    


        public function getPassword($which = null, $stars = true) {
            if ($which === null) {
                $which = strtolower(basename($_SERVER['PHP_SELF'],'.php')) . 'z';
            }
            $prompt =  "Give фasswзrd. scяipt-кittч häx yør $which: ";
            $cat ='
            ,-""""""-.
         /\j__/\  (  \`--.
         \`@_@\'/  _)  >--.`.
        _{.:Y:_}_{{_,\'    ) )
        {_}`-^{_} ```     (_/
';

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

                  
        protected $su_pass = null;
    

        public function getSuPassword($which = null) {
            if ($this->su_pass ===null) {
                $this->su_pass = $this->getPassword($which);            
            }
            return $this->su_pass;
        }





        public function sudo_exec($cmd, &$output = null, &$return_var = null, $su_pass = null) {
            //check if we need have sudo rights already
            if (!$su_pass) {
                $su_pass = $this->getSuPassword();
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

        public function sudo($cmd) {
            if ($this->su_pass === null) {
                $su_pass = $this->getPassword();
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


        public function chooseMenuValue($msg,$options) {
            return $options[$this->chooseMenuIndex($msg,$options)];
        }


        public function chooseMenuIndex($msg,$options) {
            $menu = '';
            foreach ($options as $i=>$key) {
                $menu .= "\t" . self::$red . "$i" . self::$black . ") " . trim($key) . "\n";
            }
            do {
                $idx = trim($this->ask($msg . "\n" . $menu . "Choice"));
            } while (!array_key_exists($idx,$options));
            return $idx;
        }


        public function chooseMenuValues($msg,$options) {
            $indices =$this->chooseMenuIndices($msg,$options);
            $vals = array();
            foreach ($indices as $index) {
                if (!array_key_exists($index,$options)) {
                    continue;
                }
                $vals[] = $options[$index];
            }
            return $vals;
        }




        public function chooseMenuIndices($msg,$options) {
            $indices = array();
            if (array_key_exists('Q',$options) || array_key_exists('q',$options)) {
                I2CE::raiseError("Not allowed to have the key 'q' or 'Q' in your options list");
                return $indices;
            }
            $menu = '';
            do {
                $menu = '';
                foreach ($options as $i=>$key) {
                    if (in_array($i,$indices)) {
                        $menu .= "\t" . self::$red . "$i" . self::$black . " [X]) " . trim($key) . "\n";
                    } else {
                        $menu .= "\t" . self::$red . "$i" . self::$black . " [ ]) " . trim($key) . "\n";
                    }
                }
                $idx = trim($this->ask($msg . "\n" . $menu . "Please select an option 'X', a range or options 'X-Y',  or quit the selection process with 'q'"));
                if (preg_match("/^([0-9]+)\-([0-9]+)$/",$idx,$matches)) {
                    $idxs = range($matches[1], $matches[2]);
                } else   if (preg_match("/^([0-9]+)$/",$idx,$matches)) {
                    $idxs = array($matches[1]);
                } else { 
                    $idxs = array();
                }
                foreach ($idxs as $idx) {
                    if (array_key_exists($idx,$options)) {
                        if ( ($pos = array_search($idx,$indices)) !== false) {
                            unset($indices[$pos]);
                        } else {
                            $indices[] = $idx;
                        }
                    }
                } 
            } while (strtolower($idx) != 'q');
            return $indices;
        }


        public function chooseDependentTreeMenuIndices($msg,$options, $indices = null) {
            if (!is_array($indices)) {
                $indices = array();
            }
            if (array_key_exists('Q',$options) || array_key_exists('q',$options)) {
                I2CE::raiseError("Not allowed to have the key 'q' or 'Q' in your options list");
                return $indices;
            }
            $menu = '';
            do {
                $menu = '';
                foreach ($options as $i=>$key) {
                    $index = str_pad(((string)$i) . ')' ,8,' ');
                    $num_tabs = strlen($key) - strlen(ltrim($key,"\t"));                
                    $desc_pad = "\t" . str_pad('',strlen($index) + 4," ") . "\t "  . str_pad('',$num_tabs,"\t") . "  ";
                    $key = str_replace("\n","\n" . $desc_pad,$key);
                    if (in_array($i,$indices)) {
                        $menu .= "\t" . self::$black . "[X] " . self::$red . "$index\t" . self::$black . " " . $key . "\n";
                    } else {
                        $menu .= "\t" . self::$black . "[ ] " . self::$red . "$index\t" . self::$black . " " . $key . "\n";
                    }
                }
                $idx = trim($this->ask($msg . "\n" . $menu . "Please select an option or enter q to quit selection process"));
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
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
