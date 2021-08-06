<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
 *  I2CE_BackgroundProcess
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */




class I2CE_BackgroundProcess extends I2CE_Module {
    
    


    public static function getMethods() {
        return array(
            'I2CE_Page->launchBackgroundProcess'=>'launchBackgroundProcess',
            'I2CE_Module->launchBackgroundProcess'=>'launchBackgroundProcess',
            'I2CE_Template->launchBackgroundProcess'=>'launchBackgroundProcess',
            'I2CE_Page->launchBackgroundPHPScript'=>'launchBackgroundPHPScript',
            'I2CE_Module->launchBackgroundPHPScript'=>'launchBackgroundPHPScript',
            'I2CE_Template->launchBackgroundPHPScript'=>'launchBackgroundPHPScript',
            'I2CE_Page->launchBackgroundPage'=>'launchBackgroundPage',
            'I2CE_Module->launchBackgroundPage'=>'launchBackgroundPage',
            'I2CE_Template->launchBackgroundPage'=>'launchBackgroundPage'
            ); 
    }  
    /**
     * @param mixed $obj.  The calling object
     * @param string $cmd The command
     * @param mixed $cl_args.  String or array of string, the command line arguments.  Defualts to empty arra.
     * @param string $wd.  Workking directory.  Default to null
     */
    public static function launchBackgroundProcess($obj,$cmd, $cl_args=array(), $wd = null) {
        $number = rand(10000,99999);
        if ((! is_string($cmd)) ||  strlen($cmd) == 0) {
            I2CE::raiseError("No background process found to launch");
            return false;
        }
        if (is_array($cl_args)) {
            $cl_args = implode(' ', $cl_args);
        }
        if (!is_scalar($cl_args)) {
            I2CE::raiseError("Invalid arguments");
        }
        $logDir = self::getLogDir();
        if ($logDir) {
            $logDir = escapeshellarg($logDir);
        }
        if (!I2CE_FileSearch::isUnixy()) {
            //if windows
            $path = '';
            if ($wd !== null) {
                $path = "/D$wd";
            }
            $cmd = 'start "I2CE::BackgroundProcess" ' . $path . ' /B ' . $cmd . ' ' . $cl_args;
            if ($logDir) {
                $cmd .=  "  2> $logDir" .DIRECTORY_SEPARATOR . "process.$number.log";
            }
            $_SESSION['BackgroundProcess'][$number]['cmd_line'] = $cmd;
        } else {
            //assume unix for now
            $cmd =   trim($cmd . ' ' .  $cl_args);
            if ($logDir) {
                $cmd .= "  > $logDir" .DIRECTORY_SEPARATOR . "process.$number.log 2>&1 &";
            }
            $_SESSION['BackgroundProcess'][$number]['cmd_line'] = $cmd;
            $cmd = "sh -c "   . escapeshellarg($cmd . "echo $!") ;
            if ($wd !== null) {  
                $cmd = 'cd ' . $wd . '; ' . $cmd;  
            } 
        } 
        $_SESSION['BackgroundProcess'][$number]['working_dir'] = getcwd();
        $_SESSION['BackgroundProcess'][$number]['time'] = time();
        if ($logDir) {
            I2CE::raiseError("Starting background process: ($cmd) \nWorking directory is (" . getcwd() 
                             . ")\nLog file is stored at $logDir" . DIRECTORY_SEPARATOR . "process.$number.log");
        } else {
            I2CE::raiseError("Starting background process: ($cmd) \nWorking directory is (" . getcwd() . ")");
        }               
        //now execute the command
        if (!I2CE_FileSearch::isUnixy()) {
            pclose(popen($cmd,'r'));
        } else {
            //we can get the process id for unix
            exec($cmd,$output);
            if (count($output) > 0) {
                $_SESSION['BackgroundProcess'][$number]['process'] = $output[0];
            }
        }
    }

    /**
     * Returns the log directory for background process.  It will attempt to create it if it is not there.
     * @returnn mixed string the logDirectory or false on failure/inaccessable
     */
    public static function getLogDir() {
        $logDir = false;
        I2CE::getConfig()->setIfIsSet($logDir,"modules/BackgroundProcess/log_dir");        
        $logDir = I2CE_FileSearch::realPath($logDir,false);
        if (!$logDir) {
            return false;
        }
        clearstatcache();
        if (!file_exists($logDir)) {
            mkdir($logDir);
        }
        clearstatcache();
        if (!(is_writeable($logDir) && is_dir($logDir))) {
            I2CE::raiseError("Log directory ($logDir) is not usable.");
            return false;
        }
        //hopedfully we are good to go at this point
        return $logDir;
    }


    /**
     * @param mixed $obj.  The calling object
     * @param string $script The php script
     * @param mixed $cl_args.  String or array of string, the command line arguments.  Defualts to empty arra.
     * @param string $wd.  Workking directory.  Default to null
     */
    public static function launchBackgroundPHPScript($obj,$script,$cl_args=array(),$wd = null) {
        if (strlen($script) == 0) {
            I2CE::raiseError("No background script provided");
            return false;
        }
        $script = escapeshellarg($script);
        $php = 'php';
        if (!I2CE_FileSearch::isUnixy()) {
            I2CE::getConfig()->setIfIsSet($php,"/modules/BackgroundProcess/php_executable/windows");
        } else {
            I2CE::getConfig()->setIfIsSet($php,"/modules/BackgroundProcess/php_executable/unix");
        }
        $php = I2CE_FileSearch::realPath($php);
        if (!is_executable($php) &&  !array_key_exists('HTTP_HOST',$_SERVER) && array_key_exists('_',$_SERVER)) {
            //this is CLI -- try using the current running interpreter.
            $php = $_SERVER['_'];  //this is the php executable from the CLI
        }       
        $php = escapeshellarg($php);
        self::launchBackgroundProcess($obj,"$php $script", $cl_args, $wd);
    }



    /**
     * @param mixed $obj Calling object
     * @param string $page the name of the page to launch
     * @param mixed $cl_args.  String or array of string, the command line arguments.  Defualts to empty arra.
     */
    public static function launchBackgroundPage($obj,$page,$cl_args=array()) {
        $script = $_SERVER['SCRIPT_FILENAME'];
        if ( ($protocol = I2CE::getRuntimeVariable('I2CE_DB_PROTOCOL',''))) {  
            $cl_args[] = '--I2CE_DB_PROTOCOL=' . $protocol;
        }
        $cl_args[] = "--page=$page";
        $cl_args[] = "--nocheck=1"; //dont' check that all things are a gogo.. this update won't run
        self::launchBackgroundPHPScript($obj,$script,$cl_args);
    }

}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
