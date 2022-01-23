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
 * The module that adds in an image data type
 * @package I2CE
 * @access public
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @since Demo-v1.a
 * @version Demo-v2.a
 */



  /**
   * I2CE_Module_Tags
   * @todo better documentation
   * @package I2CE
   */
class I2CE_Module_Tags extends I2CE_Module {


    public static function getHooks() {
        return array(
            'pre_page_prepare_display_I2CE_Template'=> 'processTags',
            'pre_page_prepare_display'=> 'processModules'
            );        
    }


    public function processTags($page) { 
        if (!$page instanceof I2CE_Page) {
            I2CE::raiseError("Did not get expected page");
            return;
        }
        if (!$page->getTemplate() instanceof I2CE_Template)  {
            I2CE::raiseError("Incorrect template type");
            return;
        }
        self::setPage($page);
        $this->processScripts();
        $this->processDisplayValues();
        $this->processPrintFs();
    }
        

    public static function setPage($page) {
        self::$page = $page;
    }


    public function processModules($page) {
        if (!$page instanceof I2CE_Page) {
            I2CE::raiseError("Did not get expected page");
            return;
        }
        if (!$page->getTemplate() instanceof I2CE_TemplateMeister)  {
            I2CE::raiseError("Incorrect template type");
            return;
        }
        self::setPage($page);
        $this->_processModules();
    }



    /**
     *Process any module directives on the page.
     * removes any nodes that refer to non-enabled/non-existent modules.
     * @param DOMNode a node we wish to process modules under the given node.
     */
    protected function _processModules($node = null) {
        if ($node instanceof DOMNode) {
            $qry = 'child::*//*[@type=\'module\']';
        } else {
            $qry = '//*[@type=\'module\']';
            $node = null;
        }
        $template = self::$page->getTemplate();
        $results =$template->query($qry,$node);
        $mod_factory =  I2CE_ModuleFactory::instance();
        for( $i = 0; $i < $results->length; $i++ ) {
            $node = $results->item($i);
            $node->removeAttribute('type'); //clean myself up.
            $module = null;
            $name = null;
            $is_enabled = null;
            if ($node->hasAttribute('name')) {
                $name = $node->getAttribute('name');
                $is_enabled = $mod_factory->isEnabled($name);
                if ($is_enabled) {
                    $module = $mod_factory->getClass($name);
                }
            }
            if ( $node->hasAttribute("ifenabled") ) { 
                if ($name === null) {
                    $template->removeNode($node);
                    continue;
                }
                $ifenabled = strtoupper($node->getAttribute( "ifenabled" ));
                if ( $ifenabled[0] == "!" ) {
                    $not = true;
                    $ifenabled = substr($ifenabled, 1);
                } else {
                    $not = false;
                }
                if (in_array($ifenabled,array('T','TRUE','1'))) {
                    $ifenabled = true;
                } else{
                    $ifenabled = false;
                }                
                $ifenabled = !($not xor $ifenabled);
                if (  !($is_enabled  xor  $ifenabled))  {
                    $template->removeNode($node);
                    continue;
                }
            } 
            if ($node->hasAttribute('if')) {
                if (($name !== null ) && !$module instanceof I2CE_Module) {
                    $template->removeNode($node);
                    continue;
                }
                $if = $node->getAttribute( "if" ); 
                $node->removeAttribute( "if" );
                if ( $if[0] == "!" ) {
                    $not = true;
                    $if = substr($if, 1);
                } else {
                    $not = false;
                }
                try {
                    $orig_call = $if;
                    $value = self::callModuleFunction($module,false,$if);
                } catch (Exception $e) {
                    $template->removeNode($node);
                    I2CE::raiseError("Could not call $orig_call:\n" . $e->getMessage());
                    continue;
                }
                if ( !( $value  xor $not)) {               
                    $template->removeNode( $node );
                    continue;
                }
            } 
            if ($node->hasAttribute('call')) {
                if (($name !== null ) && !$module instanceof I2CE_Module) {
                    if ( $is_enabled ) {
                        I2CE::raiseError("Bad0  on $name");
                    }
                    $template->removeNode($node);
                    continue;
                }
                $call = $node->getAttribute('call');
                $orig_call = $call;
                $node->removeAttribute('call');
                try {
                    self::$node = $node;
                    self::callModuleFunction($module,false,$call, true);                     
                } catch (Exception $e) {
                    $template->removeNode($node);
                    I2CE::raiseError("Could not call $orig_call:\n" . $e->getMessage());
                    continue;
                }
                $this->_processModules($node);
            }
            //flatten out this node            
            if ($node->parentNode instanceof DOMNode) {
                while ($node->hasChildNodes()) {
                    $node->parentNode->insertBefore($node->firstChild,$node);
                }
                $template->removeNode($node);
            }
        }
    }


    protected function processDisplayValues() {
        $template = self::$page->getTemplate();
        $results = $template->query( "//*[@display]" );
        for( $i = 0; $i < $results->length; $i++ ) {
            $node = $results->item($i);
            if ($node->hasAttribute('display')) {
                $display = $node->getAttribute('display');
                $orig_display = $display;
                try {
                    $value = self::callModuleFunction(false,false,$display,false); //not a template function. just
                } catch (Exception $e) {
                    //I2CE::raiseError("Could not display $orig_display:\n" . $e->getMessage());
                    // I2CE_DisplayData::processDisplayValue($template,$node,$value[0],true);                    
                    continue;
                }
                //we use the output of callModuleFunction to process the display
                //$node->removeAttribute('display');
                if (is_array($value)  && count($value) == 1) {
                    I2CE_DisplayData::processDisplayValue($template,$node,$value[0],true);
                }
            }
        }
    }

    protected function processPrintFs() {
        $template = self::$page->getTemplate();
        $results = $template->query( '//*[@printf ]');
        for( $i = 0; $i < $results->length; $i++ ) {
            $node = $results->item($i);
            self::$node = $node;
            if  ($node->hasAttribute('printf_form')) { // plural information.
                $print = $node->getAttribute('printf_form'); //the variable/function etc we are using for the 'ngettext' count
                if ($node->hasAttribute('lang')) {
                    $lang = $node->getAttribute('lang');
                } else {
                    $lang = false;
                }
                if ($lang) {
                    $print = "'$lang',"  . $print; 
                } else {
                    $print = "'" . I2CE_Locales::DEFAULT_LOCALE ."',"  . $print; 
                }
                $orig_print = $print;
                try {
                    self::$node = $node;
                    $n = self::callModuleFunction('Tags','getPluralForm',$print, false); //not a template function
                } catch (Exception $e) {
                    I2CE::raiseError("Could not call getPluralForms($orig_print):\n" . $e->getMessage());
                    $this->cleanUpPrintF($template,$node);
                    continue;
                }
                if ($n === false) {
                    I2CE::raiseError("Did not get plural form for $orig_print");
                    $this->cleanUpPrintF($template,$node);
                    continue;
                }
                if ($node->hasAttribute('printf_plural')) {
                    if ($n == 0) {
                        $attr = 'printf';
                    } else if ($n == 1) {
                        $attr = 'printf_plural';
                    } else {
                        I2CE::raiseError("Got plural form $n when expecting either 0 or 1");
                        $this->cleanUpPrintF($template,$node);
                        continue;
                    }
                } else {
                    $attr = 'printf_' . $n;
                }
                $print = $node->getAttribute($attr) . $this->extractPrintFArgs($node->getAttribute('printf'));
            } else { //assume it has no plural form
                $print = $node->getAttribute('printf');
            }
            $orig_print = $print;
            $this->cleanUpPrintF($template,$node);
            try {
                $string = self::callModuleFunction('Tags','printf',$print,false); //not a template function
            } catch (Exception $e) {
                I2CE::raiseError("Could not call Tags->printf($orig_print):\n" . $e->getMessage());
                continue;
            }
            $node->appendChild( $template->createTextNode($string));
        }
    }

    protected $pluralForms;

    protected function extractPrintFArgs($text) {
        $text = trim($text);
        if (strlen($text) == 0) {
            return '';
        }
        $in_escape = false;
        $begin_quote = false;
        for ($i=0; $i < strlen($text); $i++) {
            $c = $text[$i];
            if ($in_escape) {
                $in_escape = false;
            } else {
                switch ($c) {
                case '"':
                case "'":
                    if (!$begin_quote) {
                        $begin_quote = $c;
                    } else  if ($begin_quote == $c) {
                        return substr($text,$i+1); //return the remainder of the string
                    } else {
                        //no nothing
                    }
                    break;
                case '\\':
                    $in_escape = true;
                    break;
                default:
                    //do nothing;
                }
            }
        }
        return '';        
    }

    protected function cleanUpPrintF($template,$node) {
        $prints = $template->query('./@*',$node);
        for ($j = 0; $j < $prints->length; $j++) {
            $attr = $prints->item($j);
            if (!$attr instanceof DOMAttr) {
                continue;
            }
            if (substr($attr->name,0,6) == 'printf') {
                $node->removeAttributeNode($attr);
            }
        }
    }


                    
    public function getPluralForm($module,$args) {
        if (count($args) != 2 ) {
            return false;
        }
        if (!$this->pluralForms instanceof I2CE_PluralForms) {
            $this->pluralForms = new I2CE_PluralForms();
        }
        return $this->pluralForms->getPluralForm($args[0],$args[1]);        
    }
        
        

    public function printf () {
        $args = func_get_args();
        if (count($args) < 1) {
            return ;
        }
        if (!is_string($args[0])) {
            return;
        }
        return call_user_func_array('sprintf',$args);
    }



    const GROUND = 0;
    const GROUND_END = 1;
    const FUNC = 10; //either a task or a function
    const VARIABLE = 15; 
    const VARIABLE_METHOD = 15; 
    const VARIABLE_TYPED = 16; 
    const VARIABLE_TYPED_END = 17; 
    const FUNC_ARGS = 20; //either a task or a function
    const FUNC_ARG_QUOTED = 21; //either a task or a function
    const FUNC_ARG_QUOTED_ESCAPED = 22; //either a task or a function
    const FUNC_ARG_MODULE_FUNCTION = 24; //either a task or a function
    const FUNC_ARG_UNQUOTED = 25; //either a task or a function
    const FUNC_ARG_VARIABLE_TYPED = 40; //either a task or a function
    const FUNC_ARG_VARIABLE_TYPED_END = 41; //either a task or a function
    const FUNC_ARG_VARIABLE = 42; //either a task or a function
    const FUNC_ARG_VARIABLE_METH_PROP = 43; //either a method or property
    const FUNC_ARG_SPECIAL_VARIABLE = 50;


    protected static function addArgument(&$args,$arg,&$append) {
        $which = count($args) - 1;
        if ($which >= 0) {
            $orig_arg = $args[$which];
        } else {
            $which = 0;
            $orig_arg = null;
        }
        switch ($append) {
        case 's': //string
            if (is_scalar($orig_arg) || $orig_arg === null) {
                $orig_arg = '' . $orig_arg;
            } else  if (is_object($orig_arg)) {
                throw new Exception("Implicitly treating " . get_class($orig_arg) . " wanting to treat as string");
                $orig_arg = '';
            } else {
                throw new Exception("unexpected type " . get_type($orig_arg) . ".  wanting to treat as string");
                $orig_arg = '';
            }
            $args[$which] = $orig_arg . $arg;
            break;
        case '|':
            $args[$which]  = ( ((bool)$orig_arg) || ((bool)$arg));
            break;
        case '&':
            $args[$which]  = ( ((bool)$orig_arg) && ((bool)$arg));
            break;
        case 'xor':
            $args[$which]  = ( ((bool)$orig_arg) xor ((bool)$arg));
            break;
        case '+':
            if (!is_numeric($orig_arg) || !is_numeric($arg)) {
                throw new Exception("unexpected type " . get_type($orig_arg) . ".  wanting to treat as numeric");
            }
            $args[$which]  = $orig_arg + $arg;
            break;
        case '-':
            if (!is_numeric($orig_arg) || !is_numeric($arg)) {
                throw new Exception("unexpected type " . get_type($orig_arg) . ".  wanting to treat as numeric");
            }
            $args[$which]  = $orig_arg - $arg;
            break;
        case '/':
            if (!is_numeric($orig_arg) || !is_numeric($arg)) {
                throw new Exception("unexpected type " . get_type($orig_arg) . ".  wanting to treat as numeric");
            }
            $args[$which]  = $orig_arg / $arg;
            break;
        case '%':
            if (!is_numeric($orig_arg) || !is_numeric($arg)) {
                throw new Exception("unexpected type " . get_type($orig_arg) . ".  wanting to treat as numeric");
            }
            $args[$which]  = $orig_arg % $arg;
            break;
        case '*':
            if (!is_numeric($orig_arg) || !is_numeric($arg)) {
                throw new Exception("unexpected type " . get_type($orig_arg) . ".  wanting to treat as numeric");
            }
            $args[$which]  = $orig_arg * $arg;
            break;
        default:
            $args[] = $arg;
        }     
        $append = false;
    }
    

    protected static $node;
    protected static $page;


    /**
     * Process an argument string to return an array of argments
     * @param I2CE_Page $page
     * @param DOMNode $node
     * @param string $arg
     * @param boolean $templatefunction.  Defaults to false.  If true we preend the $node, and $template to the argument list for the funciton.
     * @returns array
     */
    public static function getArguments($page,$node,$arg, $templatefunction = false) {
        self::$page = $page;
        self::$node = $node;
        return self::callModuleFunction(false,false,$arg,$templatefunction);
    }

    /***
     * @param mixed $module.  Either a string ( the module name), null or false or an object.  If false we do not try to call a module function, rather
     * we assume that just want an array or arguments evaluated from $argument.   If null, we try to get the module name from the argument string.
     * If it is an object, and $func is a string, then we parse the arguement string for arguements to call on the function $func.  If it is an
     * an object and $func is false, then we parse the argument string for the function to call on the object as well as it's arguments.
     * @param mixed $func.   Either a string, the function to call, or false meaning that we should pull the function from $argument
     * @param string &$argument.  The argument string 
     * @param boolean $templatefunction.  Defaults to false.  If true we preend the $node, and $template to the argument list for the funciton.
     * @param string $default_category.  The defaults category for template data.  Defaults tos DISPLAY
     * @returns mixed.
     */
    public static  function callModuleFunction($module, $func , &$argument, $templatefunction = false, $default_category = 'DISPLAY') {
        if (is_object($module)) {
            if ($func === false) {
                $state = self::GROUND;
                $func = '';
            } else {
                $state = self::FUNC_ARGS;
            }
        } else  if ($module === false) {
            $state = self::FUNC_ARGS;
            $func = '';
        } else  if ($func === false) {
            $func = '';
            $state = self::GROUND;
        } else {
            $state = self::FUNC_ARGS;
        }
        $end_state = $state;
        $sub_module = '';
        $orig_argument = $argument;
        $booleans = array();
        $i = 0;
        $arg = '';
        $args_stack = array();
        $args = array();
        $var = '';
        $append = false;
        $quote_begin = null;
        while (strlen($argument) > 0 && $i < strlen($argument) ) {
            $c = $argument[$i];
            $i++;
            switch ($state) {
            case self::FUNC_ARG_QUOTED_ESCAPED:
                $arg .= $c;
                $state  = self::FUNC_ARG_QUOTED;
                break;
            case self::FUNC_ARG_QUOTED:
                if  (($c == $quote_begin)) {
                    $state = self::FUNC_ARGS;
                    self::addArgument($args,$arg,$append);
                    $arg = '';
                } else if ($c == '\\' ) {
                    $state=self::FUNC_ARG_QUOTED_ESCAPED;
                } else {
                    $arg .= $c;
                }
                break;
            case self::FUNC_ARG_SPECIAL_VARIABLE:
                if ($c == '>') {
                    $arg = null;
                    if (strtoupper($arg) == 'USER') {
                        if (self::$page instanceof I2CE_Page) {
                            $arg = self::$page->getUser();
                        } else {
                            $arg = new I2CE_User();
                        }
                    }  else if (strtoupper($arg)== 'TEMPLATE') {
                        if (self::$page instanceof I2CE_Page && self::$page->getTemplate() instanceof I2CE_Template) {
                            $arg = self::$page->getTemplate();
                        }
                    }  else if (strtoupper($arg) == 'NODE') {
                        $arg = self::$node;
                    } else if (strtoupper(substr($arg,0,3)) == 'GET') {
                        if (self::$page instanceof I2CE_Page) {
                            $arg = self::$page->get(explode(':',substr($arg,3)));
                        } 
                    } else if (strtoupper(substr($arg,0,4)) == 'POST') {
                        if (self::$page instanceof  I2CE_Page) {
                            $arg = self::$page()->post(explode(':',substr($arg,4)));
                        } 
                    } else if (strtoupper(substr($arg,0,7)) == 'REQUEST') {                        
                        if (self::$page instanceof  I2CE_Page) {
                            $arg = self::$page()->request(explode(':',substr($arg,7)));
                        } 
                    } else {
                        throw new Exception("Unrecognized special varaible ending at $i,  <$arg> in $orig_argument");
                    }
                    self::addArgument($args,$arg,$append);
                    $state = self::FUNC_ARGS;
                    $arg = '';
                } else {
                    $arg .= $c;
                }
                break;
            case self::FUNC_ARG_VARIABLE_TYPED: 
                if ($c == '}') {
                    $state = self::FUNC_ARG_VARIABLE_TYPED_END;
                    $arg = '';
                } else {
                    $type .= $c;
                }
                break;
            case self::FUNC_ARG_VARIABLE_TYPED_END:
                if ($c == '$') {
                    //we are good
                    $arg = '';
                    $state = self::FUNC_ARG_VARIABLE;
                } else {
                    throw new Exception("Unexpected characted ($c/$state) at $i in $orig_argument");
                }
                break;
            case self::FUNC_ARG_VARIABLE_METH_PROP:
                if ($c == ')') {
                    //it was a variable property and $arg is the property
                    $i--;
                    if (!is_object($var)) {
                        $arg = null;
                    } else {
                        @$arg = $var->$arg;
                    }
                } else  if  (ctype_space($c) || $c==',') {
                    //it was a variable property
                    if (!is_object($var)) {
                        $arg = null;
                    } else {
                        @$arg = $var->$arg;
                    }
                } else  if  ($i == strlen($argument)) {
                    $arg .= $c;
                    //it was a variable property
                    if (!is_object($var)) {
                        $arg = null;
                    } else {
                        @$arg = $var->$arg;
                    }                    
                } else if ($c == '(') {
                    //we are in a method for the object.
                    if (strlen($arg) == 0) {
                        throw new Exception("Unexpected character ($c/$state) at $i in $orig_argument");
                    }
                    //we are hopefully dealing  with a e function call for the object $var
                    $argument = substr($argument, $i-1); //get the substring starting after the ( which is at $i-1 ($i >= 1))
                    if (!is_object($var)) {
                        throw new Exception("Expecting object to call $arg(), but not found at $i in $orig_argument");
                    } else {
                        $arg = self::callModuleFunction($var,$arg, $argument);
                    }
                    $i = 0;
                } else if ($c == '>') {
                    //we are doing (hopefully) something like $user->$surname->doSoemthing() andwe are about to start the $surname part
                    if (strlen($arg) ==  0 || $arg[strlen($arg) -1] != '-') {
                        throw new Exception("Unexpected ($c/$state) at $i in $orig_argument");
                    }
                    $arg = substr($arg,0,-1);                        
                    if (is_object($var)) { 
                        @$var = $var->$arg;
                    }
                    if (!is_object($var)) {
                        $var = null;
                    }
                    $arg = '';
                    continue;
                } else {
                    $arg .= $c;
                    continue;
                }
                $state = self::FUNC_ARGS;   
                self::addArgument($args,$arg,$append);                          
                break;
            case self::FUNC_ARG_VARIABLE:
                if ($c == ')') {
                    $state = self::FUNC_ARGS;                    
                    $i--;
                } else  if  (ctype_space($c) || $c==',' ) {
                    $state = self::FUNC_ARGS;             
                } else if  ($i == strlen($argument)  ) {
                    $arg .= $c;
                    $state = self::FUNC_ARGS;             
                } else  if  ( $c == '>') {
                    $state = self::FUNC_ARG_VARIABLE_METH_PROP;                    
                }else {
                    $arg .= $c;
                    continue;
                }
                $var = null;
                if (strlen($arg) == 0) {
                    I2CE::raiseError("Invalid arguement $arg at $i in $orig_arguement");
                }else  if (!I2CE_ModuleFactory::instance()->isEnabled('DisplayData')) {
                    I2CE::raiseError("Template Display Data not enabled for $orig_argument when getting $arg");
                } else {
                    if (self::$page instanceof I2CE_Page && self::$page->getTemplate() instanceof I2CE_TemplateMeister) {
                        $var = self::$page->getData($type,$arg,self::$node);
                    } else {
                        I2CE::raiseError("No template when expecting for $orig_argument when getting $arg");
                    }
                }                
                if ($state !== self::FUNC_ARG_VARIABLE_METH_PROP) {
                    self::addArgument($args,$var,$append);
                }
                $arg = '';
                break;
            case self::FUNC_ARG_MODULE_FUNCTION:
                if ($c == '(') {
                    if (strlen($arg) == 0) {
                        throw new Exception("Unexpected character ($c/$state) at $i in $orig_argument");
                    }
                    //we are hopefully dealing  with a module function call
                    $argument = substr($argument, $i-1); //get the substring starting after the ( which is at $i-1 ($i >= 1))
                    $arg = self::callModuleFunction($sub_module,$arg, $argument);
                    $i = 0;
                    self::addArgument($args,$arg,$append);
                    $state = self::FUNC_ARGS; //step down a state
                } else if ($c == ')') {
                    throw new Exception ("Unexpected character ($c/$state) at $i in $orig_argument");
                } else {
                    $arg .= $c;
                    //hopefuly it's a valid characted
                }
                break;
            case self::FUNC_ARG_UNQUOTED: 
                if ($c == '>') {
                    if ($arg[strlen($arg)-1] == '-') {
                        $state = self::FUNC_ARG_MODULE_FUNCTION;
                        $sub_module = substr($arg,0,-1);
                        $arg = '';
                    } else {
                        throw new Exception("Unepected $c at $i in $orig_argument");
                    }
                    break;
                } else if ($c == ')') {
                    $i--; //backtrack one 
                } else  if  (ctype_space($c) || $c==',') {
                    //
                } else if  ($i == strlen($argument) ) {
                    $arg .= $c;
                    //
                }else {
                    $arg .= $c;
                    break;
                }
                //we had either a ) a space or a , if we got here                
                $lower_arg = strtolower($arg);
                if ($lower_arg == 'true') {
                    self::addArgument($args,true,$append);
                } else if ($lower_arg == 'false') {
                    self::addArgument($args,false,$append);
                } elseif ($lower_arg == 'or' || $arg == '||') {
                    if ($append) {
                        throw new Exception("Unexpected ($arg/$state) at $i in $orig_argument");
                    } 
                    $append = '|';
                } elseif ($lower_arg == 'and' || $arg == '&&') {
                    if ($append) {
                        throw new Exception("Unexpected ($arg/$state) at $i in $orig_argument");
                    } 
                    $append = '&';
                } elseif ($lower_arg == 'xor') {
                    if ($append) {
                        throw new Exception("Unexpected ($arg/$state) at $i in $orig_argument");
                    } 
                    $append = 'xor';
                } else if (is_numeric($arg)) {
                    self::addArgument($args,$arg,$append);
                } else if (strlen($arg) == 0) { 
                    if ((!$append) && count($args) == 0) {
                        //do nothing
                    } else {
                        throw new Exception("Expecting argument at $i in $orig_argument: $state");
                    }
                } else if ($c == '(') {
                    //we are hopefully dealing  with a module function call
                    $argument = substr($argument, $i-1); //get the substring starting after the ( which is at $i-1 ($i >= 1))
                    $arg = self::callModuleFunction($module,$arg, $argument);
                    $i = 0;
                    self::addArgument($args,$arg,$append);
                } else {
                    throw new Exception ("Unexpected character ($c/$state) with arg=$arg at $i in $orig_argument");
                }
                $state = self::FUNC_ARGS;                    
                $arg = '';
                break;                
            case self::FUNC_ARGS:
                if ( $c == "." ) {
                    if (count($args) == 0) {
                        throw new Exception("Unexepected ($c/$state) at $i in $orig_argument");
                    }
                    //hope that the last guy is a string.
                    $append = 's';
                } else if ( $c == "'" ) {
                    $state = self::FUNC_ARG_QUOTED;
                    $quote_begin = $c;
                    $arg = '';
                } else if ($c == '"') {
                    $state = self::FUNC_ARG_QUOTED;
                    $quote_begin = $c;
                    $arg = '';
                } else  if ( ctype_space($c)) {
                    //do nothing
                } else  if ($c == ','){
                    if ($append) {
                        throw new Exception("Unexpected ($c/$state) at $i in $orig_argument");
                    } 
                    //$args[] = null;
                    $arg = '';
                    $append = false;
                } else if ($c == '<') {
                    $arg = '';
                    if ($append) {
                        throw new Exception("Unexpected ($c/$state) at $i in $orig_argument");
                    }
                    $state = self::FUNC_ARG_SPECIAL_VARIABLE;
                } else if ($c == '$') {
                    $arg = '';
                    $type = $default_category;
                    $state = self::FUNC_ARG_VARIABLE;
                } else if ($c == '{') {
                    $type = '';
                    $arg = '';
                    $state = self::FUNC_ARG_VARIABLE_TYPED;
                } else if ($c == ')') {
                    if (count($args_stack) == 0) {
                        if ($append) {
                            throw new Exception("Unexpected ($c/$state) at $i in $orig_argument while $append");
                        } 
                        $state = self::GROUND;
                        break 2; //break out of the while loop
                    } else {
                        if (count($args) > 1) {
                            throw new Exception("Unexpected number of sub-expressions at $i in $orig_argument while returning from nested sub-expression");
                        }
                        $args = array_pop($args_stack);
                        if (count($args) == 1) {
                            if (!$append) {
                                throw new Exception("Unexpected ($c/$state) at $i in $orig_argument while returning from nested sub-expression");
                            }
                            $arg = $args[0];
                            self::addArgument($args,$arg,$append);
                        }  else {
                            //count($args) == 0 we had the situation of ((something)) e.g. a double paren.  and we can ignore thigns as long as there was no operator
                            if ($append) {
                                throw new Exception("Unexpected ($c/$state) at $i in $orig_argument while returning from nested sub-expression");
                            }
                        }
                    }
                } else if ($c == '(') { 
                    array_push($args_stack,$args);
                    $args = array();
                    break;
                } else {  //presumably some printable character...  
                    $arg = $c;
                    $state = self::FUNC_ARG_UNQUOTED;
                }                
                break;
            case self::FUNC:
                if (ctype_alnum($c) ) {
                    $func .= $c;
                } else if ($c == '_') {
                    $func .= $c;
                } else if ($c == '.') {
                    $func .= $c;
                } else if ($c == '-') {
                    $func .= $c;
                } else if ($c == '+') {
                    $func .= $c;
                } else if ($c == '>') {
                    if (strlen($func) > 0 && $func[strlen($func) -1] == '-') {
                        $module = substr($func,0,-1);
                        $func = '';
                    } else {
                        throw new Exception("Unexpected ($c/$state) at $i in $orig_argument");
                    }
                } else if ($c == '(') {
                    $state = self::FUNC_ARGS;
                } else {
                    throw new Exception("Invalid expression at ($i) $orig_permssion");
                    //do  nothing
                }               
                break;
            case self::VARIABLE_TYPED:
                if ($c == '}') {
                    $state = self::VARIABLE_TYPED_END;
                } else {
                    $type .= $c;
                }
                break;
            case self::VARIABLE_TYPED_END:
                if ($c == '$') {
                    //we are good
                    $func = '';
                    $state = self::VARIABLE;
                } else {
                    throw new Exception("Unexpected characted ($c/$state) at $i in $orig_argument");
                }
                break;
            case self::VARIABLE_METHOD:
                if ($c == '(') {
                    $state = self::GROUND;
                    //we are in a method for the object.
                    if (strlen($func) == 0) {
                        throw new Exception("Unexpected character ($c/$state) at $i in $orig_argument");
                    }
                    //we are hopefully dealing  with a e function call for the object $var
                    $argument = substr($argument, $i-1); //get the substring starting after the ( which is at $i-1 ($i >= 1))
                    if (!is_object($method)) { //shouldn't be here as we checked in case self::VARIABLE, but we are being paranoid
                        throw new Exception("Expecting object to call $func(), but not found at $i in $orig_argument");
                    }
                    //get the arguements
                    $args = self::callModuleFunction(false,false,$func, $argument);
                    $i = 0;
                } else if ($c == '>') {
                    //we are doing (hopefully) something like $user->$surname->doSoemthing() andwe are about to start the $surname part
                    if (strlen($func) ==  0 || $func[strlen($func) -1] != '-') {
                        throw new Exception("Unexpected ($c/$state) at $i in $orig_argument");
                    }
                    $func = substr($func,0,-1);                        
                    if (!is_object($method)) { //shouldn't be here as we checked in case self::VARIABLE, but we are being paranoid
                        throw new Exception("Expecting object to access properoty $func, but not found at $i in $orig_argument");
                    }
                    @$method = $method->$func;
                    if (!is_object($method)) {
                        throw new Exception("Expecting object to at property $func, but not found at $i in $orig_argument");
                    }
                    $func = '';
                    continue;
                } else {
                    $func .= $c;
                    continue;
                }
                break;
            case self::VARIABLE:
                if (ctype_alnum($c) ) {
                    $func .= $c;
                    break;
                } else if ($c == '_') {
                    $func .= $c;
                    break;
                } else if ($c == '.') {
                    $func .= $c;
                    break;
                } else if ($c == '-') {
                    $func .= $c;
                    break;
                } else if ($c == '+') {
                    $func .= $c;
                    break;
                } else if ($c == '>') {
                    if (strlen($func) ==  0 || $func[strlen($func) -1] != '-') {
                        throw new Exception("Unexpected ($c/$state) at $i in $orig_argument");
                    }
                    $func = substr($func,0,-1);                        
                    $state = self::VARAIBLE_METHOD;
                    //we should be able to get a varaible here. let's try.
                    if (!I2CE_ModuleFactory::instance()->isEnabled('DisplayData')) {
                        throw new Exception("Template Display Data not enabled for $orig_argument when getting $func of type $type");                    
                    }
                    if ( (!self::$page instanceof I2CE_Page) || (!self::$page->getTemplate() instanceof I2CE_TemplateMeister)) {
                        throw new Excpetion("No template when expecting for $orig_argument when getting $func of type $type");
                    }
                    I2CE::raiseError("Getting $type $arg");
                    $module  = self::$page->getData($type,$func,self::$node);
                    if (!is_object($module)) {
                        throw new Exception("Object excpected at ($c/$state) at $i but not received");
                    }
                    //if we made it here, we have an object to operate on.
                    $func = '';                
                    break;
                } else {
                    throw new Exception("Invalid expression at ($i) $orig_permssion");
                    //do  nothing
                }               
                break;
            case self::GROUND:
                if (ctype_alnum($c) ) {
                    $func .= $c;
                    $state = self::FUNC;  
                } else if ($c == '{') {
                    $func = '';
                    $type = '';
                    $state = self::VARIABLE_TYPED;
                } else if ($c == '$') {
                    $func = '';
                    $type = $default_category;
                    $state = self::VARIABLE;
                }else if (ctype_space($c)) { 
                    //ignore it                    
                } else  {
                    throw new Exception("Unexpected character  ($c/$state) at $i in $orig_argument");
                }
                break;
            }
        }
        if ($state !==  $end_state) {
            throw new Exception("Unfinised expression in $orig_argument (STATE=$state) (END_STATE = $end_state)");
        }
        if ($templatefunction) {
            array_unshift($args,self::$page->getTemplate());
            array_unshift($args,self::$node);
        }
        if ($module === false) {
            return $args;
        } else  if (is_object($module)) {            
            $object = $module;
        } else {
            $mod_factory = I2CE_ModuleFactory::instance();
            if (!$mod_factory->isEnabled($module)) {
                return null;
            }
            $object = $mod_factory->getClass($module);        
            if (!$object instanceof I2CE_Module) {
                $name = '';
                throw new Exception("module $module does not have a module class ");
            }
        }
        if (strlen($func) == 0) {
            throw new Exception("No calling function for $orig_argument");
        }
        if (!is_callable(array($object,$func))) {
            throw new Exception("Method $func not callable for " . get_type($object));
        }
        //delete what we used up.
        $argument = substr($argument, $i+1);
        return call_user_func_array(array($object,$func),$args);
    }







    /**
     *Process any script directives on the page.
     *
     *@param I2CE_Template $template
     *
     *Basically we move any <script> tags from the body (which were inserted by templates)
     *to the header.   There is some additional functionality:
     *if the body attribute is set, then that script is the javascript for the event with that value.
     * e.g. <script body='onscoll'>alert('annoying hello')</script>  
     * will result in an alert box being displayed anytime the page is scrolled.  Specifically
     * we set the body node to have attribute 'event' with value the content of the script.
     * <p/>
     * If the src attribute is set with a relative file path, then it will serve up the file
     * with the file dump utility. Nothing is done with the value of a  body attribute if the
     * src attribute is present.
     */
    protected function processScripts() {
        //we move and <script = src nodes to the header
        $qry = '//head//script';  //find all script elements in the document that are in the body.
        $template = self::$page->getTemplate();
        $results = $template->query($qry); 
        $head_ids = array();
        for( $indx = 0; $indx < $results->length; $indx++ ) { 
            $script = $results->item($indx);
            if (!$script->hasAttribute('id')) {
                continue;
            }
            $id = $script->getAttribute('id');
            if (!$id) {
                continue;
            }
            $head_ids[$id] = $script;
        }
        $head_node = $template->doc->getElementsByTagName( "head" )->item( 0 ); 
        $body_node = $template->doc->getElementsByTagName( "body" )->item( 0 ); 
        $qry = '//body//script';  //find all script elements in the document that are in the body.
        $results = $template->query($qry); 
        for( $indx = 0; $indx < $results->length; $indx++ ) { 
            $node = $results->item($indx); 
            if ($node->hasAttribute('src')) {
                $script_src = $node->getAttribute('src');
                $attrs = array();
                for ($i = 0; $i < $node->attributes->length; $i++) {
                    $attr = $node->attributes->item(0);
                    if ($attr->name == 'src') {
                        continue;
                    }
                    $attrs[$attr->name] = $attr->value;
                }
                $use_file_dump =  !I2CE_FileSearch::isAbsolut($script_src);
                $node->parentNode->removeChild($node);
                $template->addHeaderLink($script_src,$attr,$use_file_dump);
            } else {
                //we have some script defined
                if ($node->hasAttribute('body')) {
                    $body_event = $node->getAttribute('body');
                    $body_attr_node = $body_node->getAttributeNode($body_event);
                    if (!$body_attr_node) {
                        $body_attr_node = new DOMAttr($body_event);
                        $body_node->setAttributeNode($body_attr_node);
                    }
                    $body_attr_node->value .= $node->textContent;
                } else {
                    if ($node->hasAttribute('id')) {
                        $id = $node->getAttribute('id');
                        if (array_key_exists($id,$head_ids)) {
                            $append_node = $head_ids[$id];
                            foreach ($node->childNodes as $child) {
                                $append_node->appendChild($child);
                            }
                        } else {
                            $head_ids[$id] = $node;
                            $head_node->appendChild($node);
                        }
                    } else {
                        $head_node->appendChild($node);
                    }

                }
            }
        }

    }
        







}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
