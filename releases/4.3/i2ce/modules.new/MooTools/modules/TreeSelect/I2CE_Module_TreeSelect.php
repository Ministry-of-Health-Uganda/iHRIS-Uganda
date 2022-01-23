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
* @package i2ce
* @subpackage core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2.0
* @since v3.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Module_TreeSelect
* 
* @access public
*/


class I2CE_Module_TreeSelect extends I2CE_Module{

    
    public static function getMethods() {
        return array(
            'I2CE_Page->addAutoCompleteInputTreeById'=>'addAutoCompleteInputTreeById',
            'I2CE_Template->addAutoCompleteInputTreeById'=>'addAutoCompleteInputTreeById',
            'I2CE_Page->addAutoCompleteInputTree'=>'addAutoCompleteInputTree',
            'I2CE_Template->addAutoCompleteInputTree'=>'addAutoCompleteInputTree'
            );
    }

    public static function getHooks() {
        return array(
            'post_page_prepare_display_I2CE_Template'=> 'writeOutJS'
            );
    }

    protected $trees;

    public function  __construct() {
        $this->trees = array();
    }

    /**
     * Adds a auto complete input tree
     * @param string $tree_id The id of the tree
     * @param array $tree_options.  Defaults to empty array.  Passed to the javascript formwork constructor.
     * @param array $autocomplete_options.  Defaults to empty array.  Passed to the javascript formwork constructor.
     * @param mixed $delay_index.  if false (default) no delay load of data.   otherwise it should be an integer index to data to be loaded on delay
     */
    public function addAutoCompleteInputTreeById($obj,$tree_id,$tree_options=array(),$autocomplete_options = array(),$delay_index =false) {
        if (!array_key_exists('auto_input',$this->trees) || !is_array($this->trees['auto_input'])) {
            $this->trees['auto_input'] = array();
        }
        if (is_int($delay_index)) {
            if (!is_array($tree_options)) {
                $tree_options = array();
            }
            $tree_options['delay_index'] = $delay_index;
        }
        if (is_array($tree_options) ) {
            $tree_options = json_encode($tree_options);
        }
        if (!is_string($tree_options)) {
            $tree_options = '{}';
        }
        if (is_array($autocomplete_options)) {
            $autocomplete_options = json_encode($autocomplete_options);
        }
        if (!is_string($autocomplete_options)) {
            $autocomplete_options = '{}';
        }
        $this->trees['auto_input'][] = "new I2CE_TreeSelectAutoCompleter(new I2CE_TreeInputSelect('{$tree_id}',{$tree_options}),{$autocomplete_options});\n";
    }



    public function addAutoCompleteInputTree($template,$node,$hidden_name,$hidden_id,$selected=null,$data, 
                                             $tree_options=array(),$autocomplete_options=array(), $delayed = false) {
        if ($template instanceof I2CE_Page) {
            $template = $template->getTemplate();
        }
        if (!$template instanceof I2CE_Template) {
            return;
        }
        if (!is_array($data)) {
            return;
        }
        if (!$node instanceof DOMElement) {
            return;
        }
        $delayed = $delayed && I2CE_Stub::hasAjax();
        $hidden_id = str_replace( "+", "%2B", $hidden_id );
        $hidden_id = str_replace( "[", "%5B", $hidden_id );
        $hidden_id = str_replace( "]", "%5D", $hidden_id );
        $display_id = $hidden_id . '_inputtree_display';
        $tree_id = $hidden_id . '_inputtree_tree';
        $toggle_class = $tree_id . '_toggle';
        $hiddenNode = $template->createElement(
            'input', 
            array(
                'id'=>$hidden_id,
                'name'=>$hidden_name,
                'type'=>'hidden'
                ));
        if (is_array($selected) && array_key_exists('value', $selected)) {
            $hiddenNode->setAttribute('value',$selected['value']);
        }

        $node->appendChild($hiddenNode);
        $displayNode = $template->createElement(
            'input', 
            array(
                'id'=>$display_id,
                'type'=>'text'
                ));
        if (is_array($selected) && array_key_exists('display', $selected)) {
            $displayNode->setAttribute('value',$selected['display']);
        }
        $node->appendChild($displayNode);
        $select_value = "Select Value";
        I2CE::getConfig()->setIfIsSet($select_value,"/modules/TreeSelect/text/select_value");

        $toggleNode = $template->createElement(
            'span', 
            array(
                'class'=>"tree_main_toggle $toggle_class"
                ),
            $select_value);        
        $node->appendChild($toggleNode);        
        $treeNode = $template->createElement(
            'span', 
            array(
                'id'=>$tree_id,
                'class'=>'tree_closed treeselect'
                ));
        $template->setClassValues(
            $treeNode,
            array(
                'inputSelectHidden'=>$hidden_id,
                'inputSelectVisible'=>$display_id
                ));
        $node->appendChild($treeNode);        
        if ($delayed) {
            //remove children in node and store their html in a session for deffered retrieval
            if (!array_key_exists('tree_data',$_SESSION) || !is_array($_SESSION['tree_data'])) {
                $_SESSION['tree_data'] = array();                
            }
            $delayed = mt_rand();
            $_SESSION['tree_data'][$delayed] = $data;
        } else {
            self::createTreeData($template,$treeNode, $data);
        }
        $this->addAutoCompleteInputTreeById($template,$tree_id, $tree_options,$autocomplete_options,$delayed);
    }




    public static function createTreeData($template,$treeNode,$data) {
        $has_selectable = false;
        foreach ($data as $d) {
            if (!is_array($d)) {
                continue;
            }
            if (!array_key_exists('display',$d)) {
                continue;
            }            
            if (array_key_exists('children',$d) && is_array($d['children'])) {
                $childNode = $template->createElement('span',array('class'=>'tree_children tree_closed'));
                $has_selectable_children = self::createTreeData($template,$childNode,$d['children']);
                //we have children, so this is an expander
                $expanderNode = $template->createElement('span',array('class'=>'tree_expandable'));
                $treeNode->appendChild($expanderNode);
                if (array_key_exists('value',$d)) {
                    //it is selectable
                    $has_selectable = true;
                    $tree_toggle = $template->createElement('span',array('class'=>'tree_toggle'));
                    $tree_toggle->appendChild( $template->doc->createEntityReference( "nbsp" ) );
                    $expanderNode->appendChild( $tree_toggle ); //the toggler
                    $selectorNode = 
                        $template->createElement(
                            'span',
                            array('class'=>'treeselect_selectable'),
                            $d['display']);
                    if (array_key_exists('title',$d)) {
                        $selectorNode->setAttribute('title',$d['title']);
                    }
                    $template->setClassValue($selectorNode, 'treeselect_value',$d['value']);
                    $expanderNode->appendChild( $selectorNode);
                } else {
                    //it is not selectable
                    $class = 'tree_toggle';
                    if ($has_selectable_children){
                        $has_selectable = true;
                    } else {
                        $class .= ' treeselect_notselectable';
                        continue;
                    }
                    $togglerNode = $template->createElement('span',array('class'=>$class),$d['display']);
                    if (array_key_exists('title',$d)) {
                        $togglerNode->setAttribute('title',$d['title']);
                    }
                    $expanderNode->appendChild( $togglerNode);
                }
                $expanderNode->appendChild($childNode);
            } else {
                //we have no children, so this is not an expander
                $childNode = $template->createElement('span', array(),$d['display']);                
                if (array_key_exists('value',$d)) {
                    $has_selectable = true;
                    $childNode->setAttribute('class','treeselect_selectable');
                    $template->setClassValue($childNode,'treeselect_value', $d['value']);
                } else {
                    $childNode->setAttribute('class','treeselect_notselectable');
                    continue;
                }
                if (array_key_exists('title',$d)) {
                    $childNode->setAttribute('title',$d['title']);
                }
                $treeNode->appendChild($childNode);
            }
        }
        return $has_selectable;
    }





    public function writeOutJS($page) {
        if (count($this->trees) == 0) {
            return;
        }
        if (!$page instanceof I2CE_Page) {
            return;
        }
        $template= $page->getTemplate();
        if (!$template instanceof I2CE_Template) {
            return;
        }
        $template->addHeaderLink("mootools-core.js");
        $template->addHeaderLink("mootools-more.js");
        $template->addHeaderLink("getElementsByClassName-1.0.1.js");
        $template->addHeaderLink("I2CE_ClassValues.js");       
        $template->addHeaderLink("I2CE_Window.js");       
        $template->addHeaderLink("I2CE_ToggableWindow.js");       
        $template->addHeaderLink("I2CE_TreeSelect.js");       
        $template->addHeaderLink("Tree.css");       
        if (array_key_exists('auto_input',$this->trees) && is_array($this->trees['auto_input']) && count($this->trees['auto_input'])> 0) {
            $template->addHeaderLink('Observer.js');
            $template->addHeaderLink('Autocompleter.js');
            $template->addHeaderLink('Autocompleter.css');
            $template->addHeaderLink('I2CE_TreeSelectAutoCompleter.js');
        }
        $js = '';
        foreach ($this->trees as $type=>$trees) {
            foreach ($trees as $tree_js) {
                $js .= $tree_js;
            }            
        }        
        $js = "window.addEvent('domready',function() {\n" . $js . "\n});\n";        
        $template->addHeaderText($js,'script','treeselect');
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
