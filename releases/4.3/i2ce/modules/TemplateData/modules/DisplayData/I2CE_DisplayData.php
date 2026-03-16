<?php
/**
 * @copyright © 2009 Intrahealth International, Inc.
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
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */


  /**
   * I2CE_DisplayData
   * @todo Better documentation
   * @package I2CE
   */
class I2CE_DisplayData extends I2CE_Module {

    public static function getHooks() {
        return array(
            'pre_page_prepare_display_I2CE_Template'=> 'processDisplay'
            );        
    }

    public static function getMethods() {
        return array(
            'I2CE_Template->setDisplayData' => 'setDisplayData',
            'I2CE_Template->setDisplayDataImmediate' => 'setDisplayDataImmediate',
            'I2CE_Template->selectOptionsImmediate' => 'selectOptionsImmediate',
            'I2CE_Page->selectOptionsImmediate' => 'selectOptionsImmediate',
            'I2CE_Page->setDisplayData' => 'setDisplayData',
            'I2CE_Page->setDisplayDataImmediate' => 'setDisplayDataImmediate'
            );
    }
    


    /**
     * Add a display element to the page.
     * 
     *  Mutiple entries with the same field name
     * will be appended to an array so that multiple matches can be handled in order.
     * @param string $field The name attribute to match.
     * @param string $value The value to replace the element with.
     * @param DOMNOde $node.  Defaults to null meaning the whole page.  Data is relative to this node
     */
    public function setDisplayData($template,$field,$value, $node = null) {
        if ($template instanceof I2CE_Page) {
            $template = $template->getTemplate();
        }
        $template->setData($value, $node,'DISPLAY',$field,false);
    }


    /**
     * Process  processes display data  immediately.
     * 
     *  Mutiple entries with the same field name
     * will be appended to an array so that multiple matches can be handled in order.
     * @param string $name The name attribute to match.
     * @param string $value The value to replace the element with.
     * @param DOMNOde $node.  Defaults to null meaning the whole page.  Data is relative to this node
     */
    public function setDisplayDataImmediate($template,$name,$value,$node = null) {
        if ($template instanceof I2CE_Page) {
            $template = $template->getTemplate();
        }
        if ($node instanceof DOMNode) {            
            $results = $template->query( "./descendant-or-self::node()[@name='$name']" ,$node);
        } else {
            $results = $template->query( "//*[@name='$name']");
        }
        for( $i = 0; $i < $results->length; $i++ ) {
            $this->processDisplayValue($template,$results->item($i),$value);
        }
    }
    

    /**
     * Selects options
     * 
     * @param string $name The name we are looking for
     * @param mixed $selected String or array of string, The selected values.
     * @param DOMNOde $node.  Defaults to null meaning the whole page.  Data is relative to this node
     */    
    public function selectOptionsImmediate($template,$name,$selected, $node = null) {
        if ($template instanceof I2CE_Page) {
            $template = $template->getTemplate();
        }
        if (is_scalar($selected)) {
            $selected = array($selected);
        }
        if (!is_array($selected)) {
            return;
        }
        if ($node instanceof DOMNode) {
            $results = $template->query( "./descendant-or-self::*/select[@name='$name']" ,$node);
        } else {
            $results = $template->query( "//select[@name='$name']");
        }
        for( $i = 0; $i < $results->length; $i++ ) {
            self::selectOptions($template,$results->item($i),$selected);
        }
    }






    public static  function selectOptions($template,$node,$selected) {
        $options = $template->query("./option",$node);
        for ($i=0; $i < $options->length;  $i++) {
            $option = $options->item($i);
            if (in_array($option->getAttribute('value'), $selected)) {
                $option->setAttribute('selected','selected');
            }
        }
    }


    /*
     * @param boolean $name_set Defaults to true.  
     */
    public static function processDisplayValue($template,$node,$value,$name_set=true) {
        if (!$node instanceof DOMElement) {
            return;
        }        
        $ifset = null;
        if ($node->hasAttribute('ifset')) {
            $ifset = $node->getAttribute('ifset');
            $node->removeAttribute('ifset');
        }
        if ($name_set &&  is_string($ifset) && strlen($ifset) > 0) {
            if ( $ifset[0] == "!" ) {
                $not = true;
                $ifset = substr($ifset, 1);
            } else {
                $not = false;
            }
            if (in_array(strtoupper($ifset),array('T','TRUE','1'))) {
                $ifset = true;
            } else{
                $ifset = false;
            }
            $ifset = ($not xor $ifset);
            if ( ( $value  xor $ifset)) {                               
                $template->removeNode( $node );
            }
            return;
        }
        switch ($node->tagName) {
        case 'textarea':
            if (!$name_set) { 
                //only change the value of an text area node if its name has been explicitly set
                return;
            } 
            $node->appendChild($template->createTextNode($value));
            break;
        case 'input':
            if (!$name_set) { 
                //only change the value of an input node if its name has been explicitly set
                return;
            }            
            if (is_array($value)) {
                foreach ($value as $a=>$v) {
                    $node->setAttribute($a,$v);
                }
            } else if (is_scalar($value)) {
                if (strtolower($node->getAttribute('type')) == 'checkbox') {
                    if ($value) {
                        $node->setAttribute('checked','checked');
                    } else {
                        $node->removeAttribute('checked');
                    }
                } else {
                    $node->setAttribute('value', $value );
                }
            }
            break;
        case 'select':
            if (!is_array($value)) {
                return;
            }
            if ($node->hasAttribute('display') && $node->getAttribute('display') == 'checkbox') {
                $name = $node->getAttribute('name');
                if (!$name) {
                    return;
                }
                if (substr($name,-2)  != '[]') {
                    $name.= '[]';
                }
                //we will need to replace this node with a div ($selecNode) for the checkbox
                if (!$node->parentNode instanceof DOMNode) {
                    return;
                }
                $selectNode = $template->createElement('div', array('class'=>'checkboxlist'));
                if ($node->hasAttribute('id')) {
                    $selectNode->setAttribute('id',$node->getAttribute('id'));
                }
                $node->parentNode->replaceChild(  $selectNode, $node );
                foreach ($value as $i=>$val) {
                    $attrib = array(
                        'type'=>'checkbox',
                        'name'=>$name,
                        );
                    if (is_scalar($val)) {
                        $attrib['value']=$i;
                        $text = $val;
                    } else if (is_array($val) && array_key_exists('value',$val)) {
                        $attrib["value"]=$val['value'];
                        if (array_key_exists('text', $val) && is_scalar($val['text'] )) {
                            $text = $val['text'];
                        }
                        if (array_key_exists('selected', $val) && $val['selected']) {
                            $attrib['checked'] = 'checked';
                        }
                    } else if (is_array($val) && array_key_exists('text',$val)) {
                        $attrib["value"]=$i;
                        if (array_key_exists('text', $val) && is_scalar($val['text'] )) {
                            $text = $val['text'];
                        }
                        if (array_key_exists('selected', $val) && $val['selected']) {
                            $attrib['checked'] = 'checked';
                        }                            
                    } else {                    
                        continue; 
                    }
                    
                    $checkContainer = $template->createElement('div',array('class'=>'checkboxlist'));
                    $selectNode->appendChild($checkContainer);
                    $checkContainer->appendChild($template->createElement('input', $attrib));
                    $checkContainer->appendChild($template->createElement('span', array('class'=>'checkboxlistdisplay'),$text));
                }
            } else {
                foreach ($value as $i=>$val) {
                    if (is_scalar($val)) {
                        $attrib = array("value"=>$i);
                        $text = $val;
                    } else if (is_array($val) && array_key_exists('value',$val)) {
                        $attrib = array("value"=>$val['value']);
                        if (array_key_exists('text', $val) && is_scalar($val['text'] )) {
                            $text = $val['text'];
                        }
                        if (array_key_exists('selected', $val) && $val['selected']) {
                            $attrib['selected'] = 'selected';
                        }
                    } else if (is_array($val) && array_key_exists('text',$val)) {
                        $attrib = array("value"=>$i);
                        if (array_key_exists('text', $val) && is_scalar($val['text'] )) {
                            $text = $val['text'];
                        }
                        if (array_key_exists('selected', $val) && $val['selected']) {
                            $attrib['selected'] = 'selected';
                        }                            
                    } else {                    
                        continue; 
                    }
                    $node->appendChild($template->createElement('option',$attrib,$text));
                }
            }
            break;
        case 'a':
            $href = $node->getAttribute( "href" );
            if (is_array($value)) {
                if (array_key_exists('href',$value)) {
                    $href = $value['href'];
                    unset($value['href']);
                }
                $t_values = array();
                foreach ($value as $name=>$val) {                    
                    $t_values[]= $name . '=' . urlencode($val);
                }
                $value = implode('&',$t_values);
            }
            if ( $href == "" ) {
                $href = $value;
            } else {
                if ( strpos( $href, '?' ) === false ) {
                    $href .= "?" . $value;
                } else {
                    $href .= "&" . $value;
                }
            }
            if ( $href == "" ) {
                if ($name_set) {
                    $template->removeNode( $node );
                }
            } else {
                $node->setAttribute( "href", $href );
            }
            break;
        case 'img':
            if (is_scalar($value)) {
                if (empty($value)) {
                    return;
                }
                $node->setAttribute( "src", $value );
            } else if (is_array($value)) {
                foreach ($value as $k=>$v) {
                    if (!is_scalar($v) || empty($value)) {
                        continue;
                    }
                    $node->setAttribute($k,$v);
                }
            }
            break;
        case 'pre':
        case 'div':
        case 'span':
        case 'li':
        case 'tr':
            $newtext = $template->createTextNode( $value );
            $node->appendChild( $newtext );
            break;
        case 'object':
            if (empty($value)) {
                return;
            }
            $node->setAttribute( "data", $value );
            $param = $template->createElement( "param", array( "name" => "movie", "value" => $value ) );
            $template->appendNode( $param, $node );
            break;
        case 'form':
            if (empty($value)) {
                return;
            }
            if (is_string($value)) {
                $node->setAttribute( "action", $value );
            } else if (is_array($value)) {
                foreach ($value as $k=>$v) {
                    $node->setAttribute($k,$v);
                }
            }
            break;
        case 'meta':
            if (empty($value)) {
                return;
            }
            $node->setAttribute( "content", $value );
            break;
        }       
    }

        

    /**
     * Process all display elements
     * 
     * This will go through the entire {@link displayData} array to replace any
     * elements on the page that match the given name.  Multiple matches will cycle 
     * through the values in the displayData array.  If the element is an anchor tag then 
     * the value will be appended as name/value pairs to the href attribute.
     */
    public function processDisplay($page) {
        if (!$page instanceof I2CE_Page) {
            I2CE::raiseError("Did not receive page when expected");
            return false;
        }
        $template = $page->getTemplate();
        if (!$template instanceof I2CE_Template) { 
            return ;
        }
        $results = $template->query( "//*[@name]" );
        $names = $template->getDataNames('DISPLAY');
        for( $i = 0; $i < $results->length; $i++ ) {
            $node = $results->item($i);
            $name = $node->getAttribute('name');
            if (!array_key_exists($name,$names)) {
                continue;
            }
            $value = $template->getData('DISPLAY',$name,$node,false,false);
            $this->processDisplayValue($template,$node,$value,$names[$name]);
        }
    }    
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
