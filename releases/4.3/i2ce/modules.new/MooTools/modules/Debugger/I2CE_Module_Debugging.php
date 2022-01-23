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
 ** The module that Debuggin infor
 * @package I2CE
 * @access public
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @since Demo-v1.a
 * @version Demo-v2.a
 */




class I2CE_Module_Debugging extends I2CE_Module {

    /**
     * The constructor
     */
    public function __construct() {
        parent::__construct();
        $this->errors =   array();
    }

        
    /**
     * An array of  javavscript error messages 
     */
    protected $errors;



    public static function getHooks() {
        return array(  
            'process_error'=> 'processErrors',
            'template_post_display'=> 'writeOutJS'
            );
                
    }


    /**
     * Make a call to process any  delayed errors.
     * make sure that the error div, if it exists, is the last thing on the body
     * @param I2CE_Template $template
     */
    public function writeOutJS($args) {
        $num_errors = count($this->errors);
        if ($num_errors == 0) {
            // no delayed errors.
            return;
        }
        $template = $args['template'];
        $template->addHeaderLink('mootools-core.js');
        $template->addHeaderLink('mootree.js');
        $template->addHeaderLink('mootree.css');
        $js = "
var ErrorTree = {
    start: function(){  
        ErrorTree.tree = new MooTreeControl({ 
            div: 'errorTree', 
            mode: 'files', 
            grid: true,
            theme: 'file?name=mootree.gif',
            loader:  {icon:'file?name=mootree_loader.gif', text:'Loading...', color:'#a0a0a0'}
        },{ 
            text: 'Error Messages',
            open: false
        });
        ErrorTree.tree.disable;
";
        for ($i=0; $i < $num_errors; $i++) {
            $js .= $this->errors[$i];
        } 
        $js .= "
         ErrorTree.tree.enable();
         }
};
window.addEvent('load',ErrorTree.start);

";
        $template->addHeaderText($js,'script',true);
        $errorDiv = $template->createElement('div',array('id'=>'errorTree'),'');
        $errorDiv->setAttribute('style',"margin-left:5%");
        $siteContent = $template->getElementById('siteFooter');
        if (!$siteContent instanceof DOMNode) {
            $siteContent = $template->getElementById('siteContent');
            if (!$siteContent instanceof DOMNode) {
                $siteContent =  $template->getElementByTagName( "body", 0 );
            }
        }
        $siteContent->appendChild($errorDiv);          
    }




    /**
     * returns false as we do not want to do a redirect.
     */
    public function processErrors($args) { 
        $this->errors[] = $this->createError($args['message']);
    }
        
    /**
     * Creates an error message in the given document
     * @param DOMDocument $doc
     * @param string $msg  The error message
     * @returns DOMNode
     */
    protected  function createError($msg) {
        $num = count($this->errors)+1;
        if ($num == 25) {
            $msg = 'Exceeded Maximum Number of Displayable Errors';
        }
        if ($num > 25) {
            return;
        }
        $js = '';
        //handle the message
        if (! is_string($msg)) {
            if ($msg === null) {
                $msg = 'null'; 
            } else      if (is_bool($msg)) {
                $msg = $msg ? 'true' : 'false';
            } else {
                $msg =  print_r($msg,true);
            }
        }
        $msg = str_replace('\'','\\\'',$msg);
        $msg = str_replace("\n",'',$msg);
        $js  = "\tvar errorNode{$num} = ErrorTree.tree.insert({text:'$msg', id:'{$num}'});\n";
        if ($num > 8) {  
            return $js;
        }

        $debug = debug_backtrace();
        $num_debug = count($debug); 
        $debug_start = 0; 
        while (($debug[$debug_start]['class'] != 'I2CE') && ($debug_start < $num_debug-1)) { 
            $debug_start++; 
        } 
        $debug_start++;                 
        for ($depth = $debug_start; $depth < $num_debug; $depth++) {
            $n = $depth - $debug_start + 1;
            $details = $debug[$depth]['class']     . $debug[$depth]['type'] . $debug[$depth]['function'] . 
                "()  Found at " . $debug[$depth]['file'] . " Line " 
                . $debug[$depth]['line'] ;                      
            if (isset($debug[$depth]['args'][0])) {
                $js .= "\t\tvar errorNode{$num}_D_{$n} = errorNode{$num}.insert({text:'$details',id:'{$num}_D_$n'});\n";
                foreach ($debug[$depth]['args'] as $arg_num=>$arg) {  
                    $js .= $this->createArgument($arg,"errorNode{$num}_D_{$n}",$num . '_D_' . $n  );
                }
            } else { 
                $js .= "\t\terrorNode{$num}.insert({text:'$details',id:'{$num}_D_$n'});\n";
            } 
        } 
        return $js;
    }

        
        

    /**
     *  Displays an object nicely
     */
    protected function createArgument($arg,$addNode,$id) {              
        $arg_type = gettype($arg);
        if ($arg === null) {
            $arg_type = '';
            $arg_val = 'null';
        } else if (is_string($arg)) {
            $arg_val = "'$arg'";
        } else  if (is_bool($arg)) {
            $arg_val = $arg ? 'true' : 'false';
        } else if (is_array($arg)) {
            $arg_type = 'Array';
            $js= "\t\t\t{$addNode}_A = $addNode.insert({text:'Array',id:'$id'});\n";
            foreach($arg as $index=>$val) {
                $js .= $this->createArgument($val,$addNode .'_A',$id . '_' . $index);
            }
            return $js;
        } else if (is_object($arg)) {
            $arg_type =  get_class($arg);
            $js= "\t\t\t{$addNode}_O = $addNode.insert({text:\"{$arg_type} Object\",id:\"{$id}_O\"});\n";
            return $js;
            //$arg =  print_r($arg,true);
        } else {
            //$arg =  print_r($arg,true);
            $arg_val = '';
        }
        $arg_val = str_replace('"','\"\'',$arg_val);
        $arg_type = str_replace('"','\"',$arg_type);
        return  "\t\t\t $addNode.insert({text:\"{$arg_type} {$arg_val}\",id:\"$id\"});\n";

    }

} 

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
