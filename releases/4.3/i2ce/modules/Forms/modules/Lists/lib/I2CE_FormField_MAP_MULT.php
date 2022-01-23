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
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v3.2.0
 * @version v3.2.0
 */
/**
 * Class for defining all the database fields used by a {@link I2CE_Form} object.
 * @package I2CE
 * @access public
 */
class I2CE_FormField_MAP_MULT extends I2CE_FormField_MAPPED { 



    public function loadFromXML($node) {
        if (!$node instanceof DOMElement
            || ! ($val_nodes = $node->getElementsByTagName('value')) instanceof DOMNodeList
            || ! ($val_nodes->length > 0)
            ) {
            return;
        }
        $value = array();
        foreach ($val_nodes as $val_node) {
            if (! $val_node instanceof DOMElement
                || ! $val_node->hasAttribute('form')
                ) {
                continue;
            }
            $value[] = array($val_node->getAttribute('form'),$val_node->textContent);
        }
        $this->setValue($value);
    }


    /**
     * Appends an XML representation of the field data onto the current node
     * @param DOMNode $field_node the node we are appending the representation onto
     */
    protected function appendXMLRepresentation($field_node) {
        $doc = $field_node->ownerDocument;
        $values = $this->getValue();
        if (is_array($values)) {
            foreach ($values as $value) {
                if (!is_array($value) || count($value) != 2) {
                    continue;
                }
                list($form,$id) = $value;
                $field_node->appendChild($val_node= $doc->createElement('value',$id));
                $val_node->setAttribute('form',$form);
            }
        }        
    }


    /**
     * Hooked method to remap a given id on a given form and field
     * @param I2CE_List $lsit
     * @param string $oldid
     * @param string $newid
     */
    public function remapField($form,$oldid,$newid) {
        $where =
            array('operator'=>'OR',
                  'operand'=>array(
                      array(
                          'operator'=>'FIELD_LIMIT',
                          'style'=>'LIKE',
                          'field'=>$this->getName(),
                          'data'=>array('value'=>"$form|$oldid,%")
                          ),
                      array(
                          'operator'=>'FIELD_LIMIT',
                          'style'=>'LIKE',
                          'field'=>$this->getName(),
                          'data'=>array('value'=>"%,$form|$oldid")
                          ),
                      array(
                          'operator'=>'FIELD_LIMIT',
                          'style'=>'LIKE',
                          'field'=>$this->getName(),
                          'data'=>array('value'=>"%,$form|$oldid,%")
                          )


                      )
                );

        $set_func = function($val) use ($form, $oldid) { 
            $vals = explode(",", $val); 
            foreach ($vals as &$v) {
                if ($v == $form . '|' . $oldid ) {
                    $v = $form . '|' . $newid;
                }
            } 
            unset($v); 
            return implode(",",$vals); 
        };
        return $this->globalFieldUpdate($where,$set_func);
    }


    /**
     * Componentizes the given $db_value based on component
     * @param string $db_value.  The non-componentized value
     * @param array $forms of stirng. The form names which we wish to componentize.
     * @param string $component The component we wish to encode
     * @returns string The componentized db_value
     */
    public function getComponentizedValue($db_value,$forms,$component) {
        $vals = explode(',', $db_value);
        foreach ($vals as &$val) {
            list ($form,$id) = array_pad(explode('|',$val,2),2,'');
            if (in_array($form,$forms)) {
                $val = $form . '|' .  $id  . '@' . $component;
            }
        }
        return implode(',',$vals);
    }


    /**
     * Componentizes the given $db_value based on component
     * @param string $db_ref.  The reference to the data
     * @param array $forms of stirng. The form names which we wish to componentize.
     * @param string $component The component we wish to encode
     * @returns string The componentized db_value
     */
    public function getSQLComponentization($db_ref,$forms, $component) {
        return "formfield_mult_map_componentize(" . $db_ref .",". I2CE::PDO()->quote(implode($forms)) . "," . I2CE::PDO()->quote($component) . ")";
    }

    public function getDefaultDisplayStyle($type) {
        if (count($this->getDisplayedFields($type)) == 1 && $this->checkStyle_ajax_list() ) {
            return 'ajax_list';
        } else {
            return 'checkbox';
        }
    }

    protected function getSingleValueFromDB( $value ) {
        if (strpos($value,'|') !== false) {
            return explode( "|", $value, 2 );
        } else {
            return false;
        }
    }

    /**
     * Return the value of this field from the database format for the given type.
     * @param mixed $value
     */
    public function getFromDB( $value ) {
        $list_value = array();
        $list = array_unique(explode( ",", $value ));

        foreach( $list as $map_value ) {
            $val  = $this->getSingleValueFromDB( $map_value );
            if (is_array($val)) {
                $list_value[] = $val;
            }
        }
        return $list_value;
    }

    /**
     * Sets the value of this field from the posted form.
     * @param array $post The $_POST array holding the values for this form.
     */
    public function setFromPost( $post ) {
        if ( is_array( $post)) {
            $seen = array();
            $vals = array();
            foreach( $post as $value ) {
                $val = $this->getSingleValueFromDB( $value );
                if (!is_array($val)) {
                    continue;
                }
                $dbval = implode('|',$val);
                if (in_array($dbval,$seen)) {
                    continue;
                }
                $seen[] = $dbval;
                $vals[] = $val;
            }
            $this->value = $vals; 
        } else {
            $this->value = $this->getFromDB( $post);
        }
    }

    /**
     * Return the native value for this form field.
     * @return array
     */
    public function getValue() {
        if ( !$this->isSetValue() ) {
            return array();
        }
        return $this->value;
    }

    /**
     * Return the DB value for this form field.
     * @return string
     */
    public function getDBValue() {
        if ( $this->isValid() ) {
            $step1 = array();
            foreach( $this->getValue() as $value ) {
                $step1[] = implode( "|", $value );
            }
            return implode( ",", array_unique($step1) );
        } else {
            return "";
        }
    }

    /**
     * Checks to see if the current value for this is set and valid.
     * @return boolean
     */
    public function isValid() {
        if ( !$this->isSetValue() ) {
            return false;
        }        
        if ($this->canSelectAnyForm()) {
            foreach( $this->value as $value ) {
                if ( count($value) != 2 || !I2CE_Validate::checkString( $value[1] )) { 
                    return false;
                }
            }
            return true;
        } else {
            $forms = $this->getSelectableForms();
            foreach( $this->value as $value ) {
                if ( count($value) != 2 || !I2CE_Validate::checkString( $value[1] ) 
                     || !in_array( $value[0], $forms ) ) {
                    return false;
                }
            }
            return true;
        }
    }


    /**
     * Returns the value of this field as a human readable format.
     * @param I2CE_Entry $entry If a I2CE_Entry object has been passed to this method then it will return the value for that entry assuming it's an 
     * entry for this field.
     * @return mixed
     */
    public function getDisplayValue( $entry=false,$style = 'default' ) {
        if ( $entry instanceof I2CE_Entry ) {
            $value = $entry->getValue();
        } else {
            $value = $this->getValue();
        }
        $map_list = array();
        foreach( $value as $map_value ) {
            $map_list[] = I2CE_List::lookup( $map_value[1], $map_value[0] );
        }
        return implode( ", ", $map_list );
    }

    /**
     * Return the display value of this form field as a DOM Node.
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @return DOMNode
     */
    public function getDisplayNode( $node,$template ) {

        $add_node = $template->createElement('span',array('class'=>'mult'));
        if ( ($href = $this->getHref()) ) {
            $link_node = $template->createElement( "a", array( "href" => $href ) );
            $add_node->append_child($link_node);
        } else {
            $link_node = $add_node;
        }

        $value = $this->getValue();
        $map_text = array();
        foreach( $value as $i => $map_value ) {
            $map_text[$i] = I2CE_List::lookup( $map_value[1], $map_value[0] );
        }
        $first = true;
        foreach ($map_text as $i =>$text) {
            if ($first) {
                $first = false;
            } else {
                $link_node->appendChild($template->createElement("span",array('class'=>'mult_sep'),','));
            }
            if ($node->hasAttribute('auto_link') && $node->getAttribute('auto_link')) {
                $attrs = array(
                    'class'=>'mult_val',
                    'href' => 'auto_list/view?form_name=' . $value[$i][0] . '&id=' . $value[$i][0] . '|' . $value[$i][1]
                    );
                $link_node->appendChild($template->createElement("a",$attrs,$text));
            } else {
                $link_node->appendChild($template->createElement("span",array('class'=>'mult_val'),$text));
            }


        }
        return $add_node;
    }



    /**
     * Creates a drop down list of options.
     * @param I2CE_Template $template
     * @param DOMNode $node -- the node we wish to add the drop down list under
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.  Defaults to 0
     * @returns mixed DOMNode or an array of DOMNodes to add.
     */
    protected function create_DOMEditable_checkbox($node, $template, $form_node, $show_hidden =  0) {
        $add_limits = $this->getAdditionalLimits($template,$node,$form_node->getAttribute('limit'));
        $display_style = 'default';
        if ( $form_node->hasAttribute( 'display_style' ) ) {
            $display_style = $form_node->getAttribute('display_style');
        }
        $list = $this->getMapOptions($display_style,$show_hidden,true,$add_limits);
        $selected =$this->getValue();
        foreach (  $selected as &$val) {
            $val = implode('|',$val);
        }        
        $selectNode = $template->createElement('span', array('class'=>'checkboxlist'));
        foreach ($list as $d) {
            $attrs = array('value'=>$d['value']);
            $attrs = array(
                'type'=>'checkbox',
                'name'=>$this->getHTMLName() . '[]',
                'value'=>$d['value'],
                );
            if ( in_array($d['value'],$selected)) {
                $attrs['checked'] = 'checked';
            }
            $checkContainer = $template->createElement('span',array('class'=>'checkboxlist'));
            $selectNode->appendChild($checkContainer);
            $checkContainer->appendChild($template->createElement('input', $attrs));
            $checkContainer->appendChild($template->createElement('span', array('class'=>'checkboxlistdisplay'),$d['display']));
        }
        $node->appendChild($selectNode);

        
    }



    /**
     * Creates a drop down list of options.
     * @param I2CE_Template $template
     * @param DOMNode $node -- the node we wish to add the drop down list under
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.  Defaults to 0
     * @returns mixed DOMNode or an array of DOMNodes to add.
     */
    protected function create_DOMEditable_checkbox_tree($node, $template, $form_node, $show_hidden =  0) {
        $add_limits = $this->getAdditionalLimits($template,$node,$form_node->getAttribute('limit'));
        $display_style = 'default';
        if ( $form_node->hasAttribute( 'display_style' ) ) {
            $display_style = $form_node->getAttribute('display_style');
        }
        $data = $this->getMapOptions($display_style,$show_hidden,false,$add_limits);

        $template->addHeaderLink("mootools-more.js");
        $template->addHeaderLink("getElementsByClassName-1.0.1.js");
        $template->addHeaderLink("I2CE_ClassValues.js");       
        $template->addHeaderLink("I2CE_Window.js");       
        $template->addHeaderLink("I2CE_ToggableWindow.js");       
        $template->addHeaderLink("I2CE_TreeSelect.js");       
        $template->addHeaderLink("Tree.css");       
        $ele_id = 'tree:' . $this->getHTMLName();
        $ele_id = str_replace( "+", "%2B", $ele_id );
        $ele_id = str_replace( "[", "%5B", $ele_id );
        $ele_id = str_replace( "]", "%5D", $ele_id );
        $ele_id .= '_inputtree_tree';

        $js  = "new I2CE_Tree('{$ele_id}',{});\n";
        $js = "window.addEvent('domready',function() {\n" . $js . "\n});\n";        
        $template->addHeaderText($js,'script','multi_treeselect');

        $selected =$this->getValue();
        foreach (  $selected as &$val) {
            $val = implode('|',$val);
        }        
        $treeNode = $template->createElement('span', array('class'=>'checkboxlist','class'=>'tree_open treeselect','id'=>$ele_id));
        $node->appendChild($treeNode);
        $this->createCheckBoxTree($template,$treeNode,$data,$selected);
    }


    protected function createCheckboxTree($template,$treeNode,$data,$selected) {
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
                $has_selectable_children = $this->createCheckboxTree($template,$childNode,$d['children'],$selected);
                //we have children, so this is an expander
                $expanderNode = $template->createElement('span',array('class'=>'tree_expandable'));
                $treeNode->appendChild($expanderNode);
                if (array_key_exists('value',$d)) {
                    $this->createCheckBox($template,$expanderNode,$d,$selected);
                    $has_selectable = true;
                    $tree_toggle = $template->createElement('span',array('class'=>'tree_toggle'));
                    $tree_toggle->appendChild( $template->doc->createEntityReference( "nbsp" ) );
                    $expanderNode->appendChild( $tree_toggle ); //the toggler
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
                if (array_key_exists('value',$d)) {
                    $has_selectable= true;
                }
                $this->createCheckBox($template,$treeNode,$d,$selected);
            }
        }
        return $has_selectable;
    }


    protected function createCheckBox($template,$node,$data,$selected) {
        if (!is_array($data)) {
            return;
        }
        $checkContainer = $template->createElement('span',array('class'=>'tree_expandable'));
        $node->appendChild($checkContainer);

        $attrs = array('value'=>$data['value']);
        $attrs = array(
            'type'=>'checkbox',
            'name'=>$this->getHTMLName() . '[]',
            'value'=>$data['value'],
            );
        if ( in_array($data['value'],$selected)) {
            $attrs['checked'] = 'checked';
        }
        
        $checkContainer->appendChild($template->createElement('input', $attrs));
        $dispNode = $template->createElement('span', array(),$data['display']);                
        if (array_key_exists('title',$data)) {
            $dispNode->setAttribute('title',$data['title']);
        }

        $checkContainer->appendChild($dispNode);
    }




    /**
     * Creates a drop down list of options.
     * @param I2CE_Template $template
     * @param DOMNode $node -- the node we wish to add the drop down list under
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.  Defaults to 0
     * @returns mixed DOMNode or an array of DOMNodes to add.
     */
    protected function create_DOMEditable_list($node, $template, $form_node, $show_hidden = 0) {
        $add_limits = $this->getAdditionalLimits($template,$node,$form_node->getAttribute('limit'));
        $display_style = 'default';
        if ( $form_node->hasAttribute( 'display_style' ) ) {
            $display_style = $form_node->getAttribute('display_style');
        }
        $list = $this->getMapOptions($display_style,$show_hidden,true,$add_limits);
        $size = '5';
        if ($form_node->hasAttribute('size')) {
            $size = $form_node->getAttribute('size');
            $form_node->removeAttribute('size');
        }
        $selected =$this->getValue();
        foreach (  $selected as &$val) {
            $val = implode('|',$val);
        }        
        $selectNode = $template->createElement(
            'select',
            array( 
                'multiple'=>'multiple',
                'size'=>$size,
                'name'=>$this->getHTMLName() . '[]'));                
        $this->setElement($selectNode);
        foreach ($list as $d) {
            $attrs = array('value'=>$d['value']);
            if (in_array($d['value'],$selected)) {
                $attrs['selected'] = 'selected';
            }
            $selectNode->appendChild($template->createElement('option', $attrs, $d['display']));
        }
        $node->appendChild($selectNode);
    }

    /**
     *Check to see if the ajax_list style is allowed.
     * @returns boolean
     */
    public function checkStyle_ajax_list() {
        return I2CE_ModuleFactory::instance()->isEnabled('web-services-lists');
    }


    /**
     * Creates an ajax set of drop downs that are populated during the page load with
     * web services and as you select each level.
     * @param I2CE_Template $template
     * @param DOMNode $node -- the node that requested this drop down
     * @param int $show_hidden 0=non-hidden, 1=All, 2=hidden only.  Defaults to 0
     * @returns mixed DOMNode or an array of DOMNodes to add.
     */
    protected function create_DOMEditable_ajax_list($node, $template, $form_node,$show_hidden = 0) {
        return $this->_create_DOMEditable_ajax_list( $node, $template, $form_node, $show_hidden, true );
    }
 
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
