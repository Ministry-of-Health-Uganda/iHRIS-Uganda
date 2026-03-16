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
 * @package I2CE
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @since v1.0.0
 * @version v4.0.0
 */


/**
 * This class mainly handles throwing errors from withing I2CE
 * @package I2CE
 */
class I2CE_Error   {    

    /**
     * An array of the notice/warning messages received for this session
     * @var protected static array $stored_messages
     */
    protected static $stored_messages;

    public static function resetStoredMessages() {
        self::$stored_messages = array();  
    }

    public static $errorType = array (
        E_ERROR            => 'ERROR',
        E_WARNING        => 'WARNING',
        E_PARSE          => 'PARSING ERROR',
        E_NOTICE         => 'NOTICE',
        E_DEPRECATED     => 'DEPRECATED',
        E_CORE_ERROR     => 'CORE ERROR', 
        E_CORE_WARNING   => 'CORE WARNING',
        E_COMPILE_ERROR  => 'COMPILE ERROR',
        E_COMPILE_WARNING => 'COMPILE WARNING',
        E_USER_ERROR     => 'USER ERROR',
        E_USER_WARNING   => 'USER WARNING',
        E_USER_NOTICE    => 'User Notice',
        E_STRICT         => 'STRICT NOTICE',
        E_RECOVERABLE_ERROR  => 'RECOVERABLE ERROR'
        );

    public static $noticeErrors = array(E_NOTICE, E_USER_NOTICE, E_STRICT, E_DEPRECATED);
    public static $warningErrors = array(E_WARNING, E_USER_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_RECOVERABLE_ERROR, E_PARSE );


    public static $ignoreErrorsFromFilesMatching = array('MDB2', 'PEAR.php', '/usr/share');
    public static $ignoreErrors =
        array(
            'Non-static method Mail::factory() should not be called statically',
            'Non-static method MDB2::singleton() should not be called statically',
            'Non-static method PEAR::isError() should not be called statically',
            'shm_get_var()',
//            'ldap_read()',
            'shm_remove_var()'
            );






    /**
     * Count of the number of erros and warings received when the site is not initialized
     * @param static protected boolean $site_warnings
     */
    static protected $site_warnings = 0;

    /**
     * See if there were any warning messages set before the site was initialized
     * @returns boolean
     */
    public static function hasWarnings() {
        return self::$site_warnings > 0;
    }

    static protected $error_num = 0;

    /**
     * Optional error handler callback.
     */
    static private $errorHandler = array();

    /**
     * Push an error handler onto the stack.
     * @param $callback
     */
    static public function pushErrorHandler($callback) {
        array_unshift(self::$errorHandler, $callback);
    }

    /**
     * Pop an error handler off of the stack.
     * @returns $callback the callback popped off the stack
     */
    static public function popErrorHandler() {
        return array_shift(self::$errorHandler);
    }

    static protected $started_errors = false;
    static public $errorImage = 
'
<img style="position:fixed;left:2em;top:1em;width:150;height:150;z-index:-1"  alt=""
src="data:image/png;base64,
iVBORw0KGgoAAAANSUhEUgAAAJYAAACWCAYAAAA8AXHiAAAABGdBTUEAALGO
fPtRkwAACk9pQ0NQUGhvdG9zaG9wIElDQyBwcm9maWxlAAB42p1TZ1RT6RY9
9970QkuIgJRLb1IVCCBSQouAFJEmKiEJEEqIIaHZFVHBEUVFBBvIoIgDjo6A
jBVRLAyKCtgH5CGijoOjiIrK++F7o2vWvPfmzf611z7nrPOds88HwAgMlkgz
UTWADKlCHhHgg8fExuHkLkCBCiRwABAIs2Qhc/0jAQD4fjw8KyLAB74AAXjT
CwgAwE2bwDAch/8P6kKZXAGAhAHAdJE4SwiAFABAeo5CpgBARgGAnZgmUwCg
BABgy2Ni4wBQLQBgJ3/m0wCAnfiZewEAW5QhFQGgkQAgE2WIRABoOwCsz1aK
RQBYMAAUZkvEOQDYLQAwSVdmSACwtwDAzhALsgAIDAAwUYiFKQAEewBgyCMj
eACEmQAURvJXPPErrhDnKgAAeJmyPLkkOUWBWwgtcQdXVy4eKM5JFysUNmEC
YZpALsJ5mRkygTQP4PPMAACgkRUR4IPz/XjODq7OzjaOtg5fLeq/Bv8iYmLj
/uXPq3BAAADhdH7R/iwvsxqAOwaAbf6iJe4EaF4LoHX3i2ayD0C1AKDp2lfz
cPh+PDxFoZC52dnl5OTYSsRCW2HKV33+Z8JfwFf9bPl+PPz39eC+4iSBMl2B
RwT44MLM9EylHM+SCYRi3OaPR/y3C//8HdMixEliuVgqFONREnGORJqM8zKl
IolCkinFJdL/ZOLfLPsDPt81ALBqPgF7kS2oXWMD9ksnEFh0wOL3AADyu2/B
1CgIA4Bog+HPd//vP/1HoCUAgGZJknEAAF5EJC5UyrM/xwgAAESggSqwQRv0
wRgswAYcwQXcwQv8YDaEQiTEwkIQQgpkgBxyYCmsgkIohs2wHSpgL9RAHTTA
UWiGk3AOLsJVuA49cA/6YQiewSi8gQkEQcgIE2Eh2ogBYopYI44IF5mF+CHB
SAQSiyQgyYgUUSJLkTVIMVKKVCBVSB3yPXICOYdcRrqRO8gAMoL8hrxHMZSB
slE91Ay1Q7moNxqERqIL0GR0MZqPFqCb0HK0Gj2MNqHn0KtoD9qPPkPHMMDo
GAczxGwwLsbDQrE4LAmTY8uxIqwMq8YasFasA7uJ9WPPsXcEEoFFwAk2BHdC
IGEeQUhYTFhO2EioIBwkNBHaCTcJA4RRwicik6hLtCa6EfnEGGIyMYdYSCwj
1hKPEy8Qe4hDxDckEolDMie5kAJJsaRU0hLSRtJuUiPpLKmbNEgaI5PJ2mRr
sgc5lCwgK8iF5J3kw+Qz5BvkIfJbCp1iQHGk+FPiKFLKakoZ5RDlNOUGZZgy
QVWjmlLdqKFUETWPWkKtobZSr1GHqBM0dZo5zYMWSUulraKV0xpoF2j3aa/o
dLoR3ZUeTpfQV9LL6Ufol+gD9HcMDYYVg8eIZygZmxgHGGcZdxivmEymGdOL
GcdUMDcx65jnmQ+Zb1VYKrYqfBWRygqVSpUmlRsqL1Spqqaq3qoLVfNVy1SP
qV5Tfa5GVTNT46kJ1JarVaqdUOtTG1NnqTuoh6pnqG9UP6R+Wf2JBlnDTMNP
Q6RRoLFf47zGIAtjGbN4LCFrDauGdYE1xCaxzdl8diq7mP0du4s9qqmhOUMz
SjNXs1LzlGY/B+OYcficdE4J5yinl/N+it4U7yniKRumNEy5MWVca6qWl5ZY
q0irUatH6702ru2nnaa9RbtZ+4EOQcdKJ1wnR2ePzgWd51PZU92nCqcWTT06
9a4uqmulG6G7RHe/bqfumJ6+XoCeTG+n3nm95/ocfS/9VP1t+qf1RwxYBrMM
JAbbDM4YPMU1cW88HS/H2/FRQ13DQEOlYZVhl+GEkbnRPKPVRo1GD4xpxlzj
JONtxm3GoyYGJiEmS03qTe6aUk25pimmO0w7TMfNzM2izdaZNZs9Mdcy55vn
m9eb37dgWnhaLLaotrhlSbLkWqZZ7ra8boVaOVmlWFVaXbNGrZ2tJda7rbun
Eae5TpNOq57WZ8Ow8bbJtqm3GbDl2AbbrrZttn1hZ2IXZ7fFrsPuk72Tfbp9
jf09Bw2H2Q6rHVodfnO0chQ6Vjrems6c7j99xfSW6S9nWM8Qz9gz47YTyynE
aZ1Tm9NHZxdnuXOD84iLiUuCyy6XPi6bG8bdyL3kSnT1cV3hetL1nZuzm8Lt
qNuv7jbuae6H3J/MNJ8pnlkzc9DDyEPgUeXRPwuflTBr36x+T0NPgWe15yMv
Yy+RV63XsLeld6r3Ye8XPvY+cp/jPuM8N94y3llfzDfAt8i3y0/Db55fhd9D
fyP/ZP96/9EAp4AlAWcDiYFBgVsC+/h6fCG/jj8622X2stntQYyguUEVQY+C
rYLlwa0haMjskK0h9+eYzpHOaQ6FUH7o1tAHYeZhi8N+DCeFh4VXhj+OcIhY
GtExlzV30dxDc99E+kSWRN6bZzFPOa8tSjUqPqouajzaN7o0uj/GLmZZzNVY
nVhJbEscOS4qrjZubL7f/O3zh+Kd4gvjexeYL8hdcHmhzsL0hacWqS4SLDqW
QEyITjiU8EEQKqgWjCXyE3cljgp5wh3CZyIv0TbRiNhDXCoeTvJIKk16kuyR
vDV5JMUzpSzluYQnqZC8TA1M3Zs6nhaadiBtMj06vTGDkpGQcUKqIU2Ttmfq
Z+ZmdsusZYWy/sVui7cvHpUHyWuzkKwFWS0KtkKm6FRaKNcqB7JnZVdmv82J
yjmWq54rze3Ms8rbkDec75//7RLCEuGStqWGS1ctHVjmvaxqObI8cXnbCuMV
BSuGVgasPLiKtipt1U+r7VeXrn69JnpNa4FewcqCwbUBa+sLVQrlhX3r3Nft
XU9YL1nftWH6hp0bPhWJiq4U2xeXFX/YKNx45RuHb8q/mdyUtKmrxLlkz2bS
Zunm3i2eWw6Wqpfmlw5uDdnatA3fVrTt9fZF2y+XzSjbu4O2Q7mjvzy4vGWn
yc7NOz9UpFT0VPpUNu7S3bVh1/hu0e4be7z2NOzV21u89/0+yb7bVQFVTdVm
1WX7Sfuz9z+uiarp+Jb7bV2tTm1x7ccD0gP9ByMOtte51NUd0j1UUo/WK+tH
Dscfvv6d73ctDTYNVY2cxuIjcER55On3Cd/3Hg062naMe6zhB9Mfdh1nHS9q
QprymkabU5r7W2Jbuk/MPtHW6t56/EfbHw+cNDxZeUrzVMlp2umC05Nn8s+M
nZWdfX4u+dxg26K2e+djzt9qD2/vuhB04dJF/4vnO7w7zlzyuHTystvlE1e4
V5qvOl9t6nTqPP6T00/Hu5y7mq65XGu57nq9tXtm9+kbnjfO3fS9efEW/9bV
njk93b3zem/3xff13xbdfnIn/c7Lu9l3J+6tvE+8X/RA7UHZQ92H1T9b/tzY
79x/asB3oPPR3Ef3BoWDz/6R9Y8PQwWPmY/Lhg2G6544Pjk54j9y/en8p0PP
ZM8mnhf+ov7LrhcWL3741evXztGY0aGX8peTv218pf3qwOsZr9vGwsYevsl4
MzFe9Fb77cF33Hcd76PfD0/kfCB/KP9o+bH1U9Cn+5MZk5P/BAOY8/xjMy3b
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" alt=""/>
';
    


    static protected $errorStart = 
        '<html><head>
<script type="text/javascript">
var moved = false;
var current_msg = 0;
var messages = new Array();  messages[0] = "<b style=\'display:block\'>User Notice</b>Beginning site update/installation";
var traces = new Array();  traces[0] = ""; 
var trace  = false;
var message = false;
var slider = false;
var trace_shown = false;
function addMessage(msg,trc) {
  messages[messages.length] = msg;
  traces[traces.length] = trc;
  if (!slider) {
    slider = document.getElementById("slider");
  }
  if (!slider) {return; }
  slider.innerHTML = "" +  (current_msg+1) + " of " + messages.length;
  if (moved) {return;}
  current_msg = messages.length-1;
  showMessage();
}
function firstMessage() {
  current_msg = 0;
  moved = true;
  blankTrace();
  showMessage();
}
function lastMessage() {
  current_msg = messages.length -1;
  moved = true;
  blankTrace();
  showMessage();
}
function prevMessage() {
  if (current_msg == 0) {
    return;
  }
  current_msg--;
  moved = true;
  blankTrace();
  showMessage();
}
function nextMessage() {
  if (messages.length -1  == current_msg) {
    return;
  }
  moved = true;
  current_msg++;
  blankTrace();
  showMessage();
}
function playMessages() {
  lastMessage();
  moved = false;
}
function showMessage() {
  if (!message) {
    message = document.getElementById("message");
  }
  if (!slider) {
    slider = document.getElementById("slider");
  }
  if (!message) {return;}
  trace_shown = false;
  message.innerHTML = messages[current_msg];
  if (!slider) {return; }
  slider.innerHTML = "" +  (current_msg+1) + " of " + messages.length;
}
function blankTrace() {
  trace_shown = false;
  if (!trace) {
    trace = document.getElementById("message_trace");
  }
  if (!trace) {return;}
  trace.innerHTML = "";
  trace.style.display="none";
}
function showTrace() {
  moved = true;
  if (trace_shown) {  
      blankTrace();
      return;
  }
  trace_shown = true;
  if (!trace) {
    trace = document.getElementById("message_trace");
  }
  if (!trace) {return;}
  trace.style.display ="block";
  trace.innerHTML = traces[current_msg];
}
</script>
</head><body><center>
<div id="header">
  <h2 style="color:#993300">iHRIS Site Update</h2>
</div>
<div id="message"
     style="text-align:left;width:75%;height:30%;
            font-family:monospace;
            font-height:70%;
            overflow:auto;margin-top:0;
            background-color:#ffffcc;border:dashed;border-width:3px;border-color:#ffcc99;opacity:0.8;">
</div>
<div style="width:75%;height:1.5em;padding-top:0.2em;padding-bottom:0.2em;">
  <span style="float:right;color:#ff9966;text-decoration:none;width:auto;padding-right:1em;" 
    onmouseover="this.style.textDecoration=\'underline\'; this.style.cursor=\'pointer\';" 
    onmouseout="this.style.textDecoration=\'none\'; this.style.cursor=\'default\';"
    id="trace" onClick="showTrace()"; >Trace
  </span>
  <div id="slider" style="padding-right:2em;float:right;font-weight:bold;color:#ffcc99;text-align:right;" >1 of 1</div>             

</div>
<div style="width:75%">
  <span 
     style="float:left;color:#ff9966;text-decoration:none;width:4em;" 
    onmouseover="this.style.textDecoration=\'underline\'; this.style.cursor=\'pointer\';" 
    onmouseout="this.style.textDecoration=\'none\'; this.style.cursor=\'default\';"
    id="left" onClick="firstMessage()"; >First
  </span>
  <span 
     style="float:left;color:#ff9966;text-decoration:none;width:4em;" 
    onmouseover="this.style.textDecoration=\'underline\'; this.style.cursor=\'pointer\';" 
    onmouseout="this.style.textDecoration=\'none\'; this.style.cursor=\'default\';"
    id="left" onClick="prevMessage()"; >Previous
  </span>
  <span style="float:right;color:#ff9966;text-decoration:none;width:4em" 
    onmouseover="this.style.textDecoration=\'underline\'; this.style.cursor=\'pointer\';" 
    onmouseout="this.style.textDecoration=\'none\'; this.style.cursor=\'default\';"
    id="right" onClick="lastMessage()";>Last
  </span>
  <span style="float:right;color:#ff9966;text-decoration:none;width:4em;" 
    onmouseover="this.style.textDecoration=\'underline\'; this.style.cursor=\'pointer\';" 
    onmouseout="this.style.textDecoration=\'none\'; this.style.cursor=\'default\';"
    id="right" onClick="nextMessage()";>Next
  </span>
  <center ><span 
     style="color:#ff9966;text-decoration:none;width:100%;" 
    onmouseover="this.style.textDecoration=\'underline\'; this.style.cursor=\'pointer\';" 
    onmouseout="this.style.textDecoration=\'none\'; this.style.cursor=\'default\';"
    id="play" onClick="playMessages()"; >Play
  </span></center>
</div>
<br/>
<div  id="message_trace"
      style="display:none;text-align:left;width:75%;height:30%;
            font-family:monospace; font-height:70%;
            overflow:auto;margin-top:1%;
            background-color:#ffffcc;border:dashed;border-width:3px;border-color:#ffcc99;opacity:0.8;">
</div>
';



    static protected $badness = 
        '<html><body><center>
<div id="header">
  <h2 style="color:#993300">iHRIS Fatal Error</h2>
</div>
<div id="message"
     style="text-align:left;width:75%;height:15%;
            overflow:auto;margin-top:0;padding-bottom:2em;
            background-color:#ffffcc;border:dashed;border-width:3px;border-color:#ffcc99;opacity:0.8;">
    <b style="display:block">Something Unexpected Happened.</b>
    Don\'t Panic.
    <br/>
    Please contact your system administrator if this happens again or use the form below to submit an error report.
</div>
<br/>
<div  id="message_trace"
      style="text-align:left;width:75%;height:60%;
            overflow:auto;margin-top:1%;
            background-color:#ffffcc;border:dashed;border-width:3px;border-color:#ffcc99;opacity:0.8;">
      <form method="post" action="mailto:{{EMAIL}}?Subject=iHRIS Fatal Error Report" enctype="text/plain">
        <b>Email Address:</b>
        Where can we contact your if we need more information
        <br/>
        <input style="width:80%;margin-left:5%" name="email"/>
        <br/>
        <b>Problem:</b>
         What were you trying to do when this problem occured
        <br/>
        <textarea name="description" style="width:80%;height:50%;margin-left:5%"></textarea>
        <input type="hidden" name="error_message" value="{{ERRORMESSAGE}}"/>
        <input type="hidden" name="error_trace" value="{{ERRORTRACE}}"/>
        <br/>
        <input style="float:right;margin-left:2em;margin-right:1em;" type="submit" value="Send"/>

        <b style="cursor:pointer" onClick="var details = document.getElementById(\'details\'); if (details) {details.style.display=\'block\'; }" >Show Details<b/>
        <span id="details" style="display:none;width:80%;margin-left:5%">
        <hr/>
         <pre>
Error Message: 
{{ERRORMESSAGE}}
         </pre>
         <hr/>
         <pre>
Trace: 
{{ERRORTRACE}}
         </pre>
        </span>
    </form>
</div>
';



    /**
     * Raise an error message, but don't display any extra trace messages to
     * keep the log file short when the trace isn't necessary.
     * @param string/mixed $message The error message.
     * @param integer $type The error type.
     * @param string $redirect The page to redirect to for critical errors.
     */
    static public function raiseMessage( $message = null, $type=E_USER_NOTICE,
                                       $redirect="" ) {
        $old_depth = self::setTraceDepth( 0 );
        self::raiseError( $message, $type, $redirect );
        self::setTraceDepth( $old_depth );
    }

    static public function handleError($err_no, $err_string, $err_file = false, $err_line = false , $err_context = false) {        
        foreach (self::$ignoreErrors as $part) {
            if (strpos($err_string, $part) !== false) {
                return;
            }
        }
        $msg = $err_string;
        if ($err_file) {
            foreach (self::$ignoreErrorsFromFilesMatching as $part) {
                if (strpos($err_file,$part) !== false) {
                    return;
                }
            }            
            if($err_line) {
                $msg .= "\nOccured on line $err_line of $err_file";
            } else {
                $msg .= "\nOccured in $err_file";
            }
        }
        self::raiseError($msg, $err_no);
    }

    /**
     * Raise an error and redirect the user for any critical errors.
     * 
     * The default redirect will go to the home page for the site.
     * @param string/mixed $message The error message.
     * @param integer $type The error type.
     * @param string $redirect The page to redirect to for critical errors.
     * @global array
     */
    static public function raiseError( $message = null, $type=E_USER_NOTICE,
                                       $redirect="" ) {
        if ( $message === null) {
            if(!is_null($e = error_get_last())) {
                if ( substr( $e['message'], 0, 19 ) == "Allowed memory size" ) {
                    // Memory limit has been reached so display 
                    // something if we still can.
                    print_r( $e );
                }
                
                foreach (self::$ignoreErrors as $part) {
                    if (strpos($e['message'], $part) !== false) {
                        return;
                    }
                }
                foreach (self::$ignoreErrorsFromFilesMatching as $part) {
                    if (strpos($e['file'],$part) !== false) {
                        return;
                    }
                }
                $message = "Fatal Error:" . print_r($e,true);
            } else {
                return;
            }
            if (array_key_exists('HTTP_HOST',$_SERVER)) {
                $contents =preg_replace('/{{ERRORMESSAGE}}/', htmlentities($message), self::$badness);
                if (is_array(self::$stored_messages)) {
                    $trace = array();
                    foreach (self::$stored_messages as $i=>$m) {
                        if (!is_array($m) || !array_key_exists('msg',$m) || !$m['msg']) {
                            continue;
                        }
                        $trace[] =  $m['trace'] . "\n" . $m['msg'] . "\n";
                    }                    
                    $trace = htmlentities(print_r($trace,true));
                } else {
                    $trace = '';
                }
                $contents =preg_replace('/{{ERRORTRACE}}/', $trace, $contents);
                if (is_string(I2CE::$email) && strlen(I2CE::$email)> 0) {
                    $email = I2CE::$email;
                } else {
                    $email = 'hris@capacityplus.org';
                }
                $contents =preg_replace('/{{EMAIL}}/', htmlentities($email), $contents);
                $contents =preg_replace('/{{REQUESTURI}}/', htmlentities($_SERVER['REQUEST_URI']), $contents);
                echo $contents;
                echo self::$errorImage;
                flush();
            }
        }
        $num = self::$error_num++;
        $warning_level = 'Warning';
        if (array_key_exists($type, self::$errorType)) {
            $warning_level  =  self::$errorType[$type];
        }
        if (self::$trace_depth == 0) {
            $trace = 'I2CE: ';
        } else {
            $debug = debug_backtrace();        
            $trace = 'I2CE: ' . 
                self::getPrevMethod( $debug, 1, self::$trace_depth ) . ':';
        }

        if (count(self::$errorHandler) > 0) {
            call_user_func(self::$errorHandler[0], $trace, $message, $type);
        } else if (array_key_exists('HTTP_HOST',$_SERVER)) {
            error_log(  $trace . $message ."\nError Type=" . $type , 0 );
            if (in_array($type, self::$noticeErrors)) {
                $msg_level  = '<b style="display:block;">' . $warning_level . '</b>' ;
            } else {
                self::$site_warnings++;
                $msg_level = '<b style="display:block;color:red">' . $warning_level . '</b>' ;
            }
            $js_message = '<script type="text/javascript">addMessage("' 
                . str_replace("\n",'<br/>',addcslashes($msg_level . $message , '"\\')) . '","'
                . str_replace("\n",'<br/>',addcslashes(rtrim($trace,"\n\t :"), '"\\')) . '");</script>' ."\n";

            if (in_array($type, self::$noticeErrors)) {                
                //a notice error
                if (I2CE::siteInitialized()){
                    self::$stored_messages[] = array('msg'=>$message, 'trace'=>$trace,'level'=>$msg_level);                     
                } else {
                    if (!self::$started_errors) {
                        echo self::$errorStart;
                        echo self::$errorImage;
                        self::$started_errors = true;
                    }
                    echo $js_message;
                    flush();
                }
            } else {
                if (!I2Ce::siteInitialized()){
                    //we have a non notice error
                    //show any stored messages or warnings to give the user a clue as to what is going on
                    if (!self::$started_errors) {
                        echo self::$errorStart;
                        echo self::$errorImage;
                        self::$started_errors = true;
                    }
                    if (is_array(self::$stored_messages)) {
                        foreach (self::$stored_messages as $m) {
                            if (!is_array($m) || !array_key_exists('msg',$m) || !$m['msg']) {
                                continue;
                            }
                            $js_m = '<script type="text/javascript">addMessage("' 
                                . str_replace("\n",'<br/>',addcslashes($m['level'] . $m['msg'] , '"\\')) . '","'
                                . str_replace("\n",'<br/>',addcslashes(rtrim($m['trace'],"\n\t :"), '"\\')) . '");</script>' ."\n";                                
                            echo $js_m;
                            flush();
                        }
                    }
                    self::$stored_messages = array();
                    if( !( $redirect === null || (is_string($redirect) && strlen($redirect)==0))){
                        header( "Location: " . $redirect ); 
                        exit();
                    }
                    if (!in_array($type, self::$warningErrors)) {
                        //this is an actual erros so exit
                        exit(); 
                    } 
                }
            }
        } else {
            // we are on the command line
            //$blue = "\033[34m";
            //$green = "\033[32m";
            //$black = "\033[0m";
            //$red = "\033[31m";
            $message = str_replace(array("\n"),array("\n\t"),$message);
            $trace = "\033[34m" . $trace . "\033[0m\n" ;
            if (in_array($type, self::$warningErrors)) {
                fwrite(STDERR, $trace .  "\t\033[31m\t" . $message . "\033[0m\n");  
                fflush(STDERR);
                exit(102);
            } else   if (!in_array($type, self::$noticeErrors)) {                
                fwrite(STDERR, $trace .  "\t\033[32m\t" . $message . "\033[0m\n");  
                fflush(STDERR);
                exit(101);
            }  else{
                fwrite(STDERR, $trace . "\t\033[32m" . $message . "\033[0m\n");  
                fflush(STDERR);
            }
        }        
    }


    /**
     *@var protected static integer $trace_depth.  Defaults to 1.     The 
     *number of previous methods to report when an error message is developed.
     *Set to a negative number to report all, or 0 to report none.
     */
    protected static $trace_depth = -1;

    /**
     * Set the trace depth used in reporting error messages
     *
     * @param integer $depth.
     *        Set to a negative number to report all,
     *        0 to report none.
     *
     * @returns integer. The previous value of trace depth.
     */
    public static function setTraceDepth($depth) {
        $old_depth = self::$trace_depth;
        self::$trace_depth = $depth;
        return $old_depth;
    }

    /**
     * Parse the debug backtrace to get the method that raised the error.
     * @param array $debug
     * @return string
     */
    static protected  function getPrevMethod( $debug, $start_depth=1, $trace_depth = 1 ) {
        if (!is_array($debug)) {
            return '';
        }
        if ($trace_depth < 0) {
            $end_depth = count($debug);
        } else {
            $end_depth = min($start_depth + $trace_depth , count($debug));
        }
        $calls = array();
        for ($depth = $start_depth; $depth < $end_depth; $depth++) {
            if (array_key_exists('class',$debug[$depth])) {
                $errstr = $debug[$depth]['class'] . "->" . $debug[$depth]['function'];
            } else {
                $errstr = $debug[$depth]['function'];
            }
            if ( array_key_exists('file', $debug[$depth] ) ) {
                $errstr = "$errstr (".$debug[$depth]['file'];
                if ( array_key_exists('line', $debug[$depth] ) ) {
                    $errstr = "$errstr:".$debug[$depth]['line'];
                }
                $errstr = "$errstr)";
            }
            $calls[] = $errstr;
        }
        if ($start_depth == $end_depth) {
            $depth = $start_depth -1;
            if (array_key_exists($depth, $debug) && array_key_exists('file', $debug[$depth ])) {
                $errstr = "Called from (".$debug[$depth]['file'];
                if ( array_key_exists('line', $debug[$depth] ) ) {
                    $errstr = "$errstr:".$debug[$depth]['line'];
                }
                $errstr = "$errstr)";
                $calls[] = $errstr;
            }
        }
        return implode("\n", $calls);
    }
    
  }
