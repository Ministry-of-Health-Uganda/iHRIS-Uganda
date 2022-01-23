<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
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
* @subpackage core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2
* @since v3.2
* @filesource 
*/ 
/** 
* Class I2CE_MootoolsCore
* 
* @access public
*/


class I2CE_MootoolsCore extends I2CE_Module{


    public static function getHooks() {
        return array(
            'pre_page_prepare_display_I2CE_Template'=> 'processScripts'
            );
    }

    public static function getMethods() {
        return array(
            'I2CE_Page->getClassValue'=>'getClassValue',
            'I2CE_Template->getClassValue'=>'getClassValue',
            'I2CE_Page->loadClassValues'=>'loadClassValues',
            'I2CE_Template->loadClassValues'=>'loadClassValues',
            'I2CE_Page->setClassValue'=>'setClassValue',
            'I2CE_Template->setClassValue'=>'setClassValue',
            'I2CE_Page->setClassValues'=>'setClassValues',
            'I2CE_Template->setClassValues'=>'setClassValues',
            'I2CE_Page->useDropDown'=>'useDropDown',
            'I2CE_Template->useDropDown'=>'useDropDown',
            );
    }

    protected $uses_dropdown = false;


    public function useDropDown($obj) {
        if ($obj instanceof I2CE_Page) {
            $obj = $obj->getTemplate();
        }
        if (!$obj instanceof I2CE_Template) {
            return;
        }
        $this->uses_dropdown = true;
        $obj->addHeaderLink("I2CE_DropdownMenu.js");
        $obj->addHeaderLink("I2CE_DropdownMenu.css");

    }


    public function processScripts($page) { 
        if (!$page instanceof I2CE_Page) {
            I2CE::raiseError("Did not get expected page");
            return;
        }
        if (!($template = $page->getTemplate()) instanceof I2CE_Template)  {
            I2CE::raiseError("Incorrect template type");
            return;            
        }
        if ( $this->uses_dropdown || (($nodes = $template->query('//script[@src="file/I2CE_DropdownMenu.js"][1]')) instanceof DOMNodeList && $nodes->length > 0)) {
            $js = "window.addEvent('domready',  function() { $$('.dropdown').each(function(e) {  new I2CE_DropdownMenu(e); })});";
            $template->addHeaderText($js,'script','dropdownmenu');
        }
    }


    public static function encode($type,$data, $single_quote = true) {
        switch ($type) {
        case '=':
            $ret =  self::encode_equals($data);
            break;
        case '%':
            $ret =  json_encode($data);
            break;
        default:
            $ret =  $data . '';
            break;
        }
        if ($single_quote && preg_match('/[\' \t]/',$ret) !== false) {
            $ret =  '\'' . addcslashes($ret,'\\\'') . '\'';
        }
        return $ret;
    }


    public static function decode($type,$data, $desingle_quote = false) {
        if (!is_string($data)) {
            return null;
        }
        if ($desingle_quote && ( ($len = strlen($data)) >= 2)) {
            if ($data[0] == '\'' && $data[$len -1 ] == '\'') {
                //probably should replace this
                $data = stripcslashes(substr($data,1,$len - 2));
            }
        }
        switch ($type) {
        case '=':
            return self::decode_equals($data);
        case '%':
            return self::decode_json($data);
        default:
            return null;
        }
    }


    protected static function _encode_equals($data) {
        if (is_bool($data)) {
            if ($data) {
                return 'true';
            } else {
                return 'false';
            }            
        } else if (is_numeric($data)) {
            return $data . '';
        } else if (is_string($data)) {
            return $data;
        } else {
            return '';
        }

    }

    public static function encode_equals($data) {
        if (is_array($data)) {
            $enc = array();
            foreach ($data as $t) {
                $enc[] =  self::_encode_equals($t);
            }
            return '[' . implode(',',$enc) . ']';
        } else {
            return  self::_encode_equals($data);
        }                
    }
    

    public static function decode_json($data) {
        if (!is_string($data) || (($len = strlen($data)) == 0)) {
            return null;
        }
        return json_decode($data);
    }


    public static function decode_equals($data) {
        if (!is_string($data) || (($len = strlen($data)) == 0)) {
            return null;
        }
	if ($len >= 2 && $data[0] == '[' && $data[$len-1] == ']') {
            $data = preg_split('/\s*,\s*/',substr($data,1,$len-2),-1,PREG_SPLIT_NO_EMPTY);
            foreach ($data as &$d) {
                $d = self::_decode_equals($d);
            }
	    return $data;
	} else {
	    return self::_decode_equals($data);
	}
    }


    protected static function _decode_equals($data) {
        if (!is_string($data) || (($len = strlen($data)) == 0)) {
            return null;
        }
        if (preg_match('/^\s*true\s*$/i',$data)) {
            return true;
        }else if (preg_match('/^\s*false\s*$/i',$data)) {		
            return false;
        } else if (preg_match('/^\s*[0-9]+\s*$/',$data)) {
            return intval($data);
        } else if (preg_match('/^\s*[0-9]+\.[0-9]*\s*$/',$data)) {
            return floatval($data);
        } else {	    
            return $data;
        }
    }


    



    protected static function loadKeyValPairs($input,&$data, $all = false, $keep_encoding = false) {
        if (!is_array($data)) {
	    return null;
	}
	$i = 0; 
	$state = 0;	    
	$key = '';
	$val = '';
	$c;
	if (!is_string($input) || (($max_index = strlen($input)) == 0)) {
	    return null;
	}
	//STATES:
	//0: ground state
	//20: begin value
	//21: quoted value 
	//22: quoted escaped value
	//29: value
	//100:wait for white space
	$val_type =false;  //type 1 is equals(=), type 2 is json(:)
	do  {
	    $c = $input[$i];
	    switch ($state) {		
	    case 0: //GROUND STATE
		if ($c == ' ' || $c == "\t" || $c == "\n") {
		    //skip whitespace
		    break;		    
		}
		if ( ('a' <= $c && $c <= 'z') || ('A' <= $c && $c <= 'Z') ) {
		    $key = $c;
		    $state = 10; //KEY NAME STATE
		} else {
		    $state = 100; //WAIT FOR WHITE SPACE
		}
		break;
	    case 10: //KEY NAME STATE
		if ($c == ' ' || $c == "\t" || $c == "\n") {
                    if ($all && $key) {
                        if ($keep_encoding) {
                            $data[$key] = array('value'=>'','encoding'=>false);
                        } else {
                            $data[$key] = '';
                        }
                    }
		    $key = '';
		    $val = '';
		    $state = 0;		    
		    break;		    
		}
                if ($c == '=' || $c == '%') {
		    if (strlen($key) > 0) {
                        $val_type = $c;
			$state = 20; //VALUE BEGIN STATE
		    } else {
			$state = 100; //WAIT FOR WHITE SPACE
		    }
		    break;
		}
		$key .= $c;
		break;
	    case 20: //VALUE BEGIN STATE
		if ($c == ' ' || $c == "\t" || $c == "\n") {
                    if ($all || array_key_exists($key,$data)) {
			//val is empty here.  hope your parser handles it apporpriately
                        if ($keep_encoding) {
                            $data[$key] =  array(
                                'value'=>self::decode($val_type,$val),
                                'encoding'=>$val_type);
                        } else {
                            $data[$key] = self::decode($val_type,$val);                        
                        }
		    }
		    $state =0;
		    $key = '';
		    $val = '';
		    break;		    		    
		}
		if ( $c == "'") {
		    $state = 21; // VALUE QUOTED BEGIN
		    break;
		} else {
		    $val = $c;
		    $state = 29; // VALUE
		    break;
		}
	    case 21: //VALUE QUOTED 
		if ($c == '\\') {
		    $state = 22; // VALUE QUOTED ESCAPED
		    break;
		}
		if ($c == '\'') {
		    //ending the quoteed string
                    if ($all || array_key_exists($key,$data)) {
                        if ($keep_encoding) {
                            $data[$key] =  array(
                                'value'=>self::decode($val_type,$val),
                                'encoding'=>$val_type);
                        } else {
                            $data[$key] = self::decode($val_type,$val);                        
                        }
		    }
		    $state =0;
		    $key = '';
		    $val = '';			
		    break;
		}
		$val .= $c;
		break;
	    case 22:  //VALUE QUOTED ESCAPED
		if ( $c == '\'' || $c == '\\') {
		    $val .= $c;
		} else {
		    $val .= '\\' + $c;
		}
		$state = 21;  // VALUE QUOTED 
		break;
	    case 29: //VALUE 
		if ($c == ' ' || $c == "\t" || $c == "\n") {
                    if ($all || array_key_exists($key,$data)) {
                        if ($keep_encoding) {
                            $data[$key] =  array(
                                'value'=>self::decode($val_type,$val),
                                'encoding'=>$val_type);
                        } else {
                            $data[$key] = self::decode($val_type,$val);                        
                        }
		    }
		    $state =0;
		    $key = '';
		    $val = '';
		    break;		    		    
		}  else {
		    $val .= $c;
		    break;
		}
	    case 100:  //WAIT FOR WHITE SPACE STATE
		if ($c == ' ' || $c == "\t" || $c == "\n") {
		    $state = 0;
		    $key = '';
		    $val = '';
		}
		break;
	    default:
		//should not be here.
		break;
	    }
	    $i++;
	} while ($i < $max_index);
	if ($state == 21 || $state == 29) {
            //a null value or n unquoted value was terminated by end of string.  this is valid
            if ($all || array_key_exists($key,$data)) {
                if ($keep_encoding) {
                    $data[$key] =  array(
                        'value'=>self::decode($val_type,$val),
                        'encoding'=>$val_type);
                } else {
                    $data[$key] = self::decode($val_type,$val);                        
                }
            }
	} else if ($state == 10 ) {
            //key name state
            if ($all || array_key_exists($key,$data)) {
                if ($keep_encoding) {
                    $data[$key] =  array(
                        'value'=>'',
                        'encoding'=>false
                        );
                } else {
                    $data[$key] = '';
                }                
            }
        }
	return null;
    }





    public function getClassValue($obj,$node,$key,$default_val = null) {
        if (!is_scalar($key)) {
            return null;
        }
        $vals = array($key=>$default_val);
        $this->loadClassValues($obj,$node,$vals );        
        return $vals[$key];
    }




    public  function loadClassValues($obj,$node, $vals,$all = false, $keep_encoding =false) {        
        if (!is_array($vals)) {
            return array();
        }
        if ($obj instanceof I2CE_Page) {
            $obj = $obj->getTemplate();
        }
        if (!$obj instanceof I2CE_Template) {
            return $vals;
        }
        if (is_string($node)) {
            $node = $obj->getElementById($node);
        } 
        if (!$node instanceof DOMElement) {
            return $vals;
        }
        if (!$node->hasAttribute('class')) {
            return $vals;
        }
        self::loadKeyValPairs($node->getAttribute('class'),$vals, $all,$keep_encoding);
        return $vals;
    }



    public function setClassValue($obj,$node,$key,$val,$encoding = '=') {
        $this->setClassValues($obj,$node,array($key=>$val),$encoding);
    }


    public function setClassValues($obj,$node,$vals,$encoding = '=') {
        if (!is_array($vals)) {
            return;
        }
        if ($obj instanceof I2CE_Page) {
            $obj = $obj->getTemplate();
        }
        if (!$obj instanceof I2CE_Template) {
            return;
        }
        if (is_string($node)) {
            $node = $obj->getElementById($node);
        } 
        if (!$node instanceof DOMElement) {
            return;
        }
        $classValues = array();
        if ($node->hasAttribute('class')) {
            self::loadKeyValPairs($node->getAttribute('class'),$classValues, true,true);
        }
        $classValues = array_diff_key($classValues,$vals);
        foreach ($classValues as $key=>&$data) {
            if ($data['encoding']) {
                $data = $key . $data['encoding'] . self::encode($data['encoding'],$data['value']);
            } else {
                $data = $key . $data['encoding'];
            }

        }        
        foreach ($vals as $key=>$val) {
            if ($val !== null) {
                $classValues[$key]  = $key . $encoding . self::encode($encoding, $val);
            } else {
                $classValues[$key] = $key;
            }
        }        
        $node->setAttribute('class', implode(' ' , $classValues));
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
