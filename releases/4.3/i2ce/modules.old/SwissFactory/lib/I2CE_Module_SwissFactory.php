<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
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
*  I2CE_Module_SwissFactory
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Module_SwissFactory extends I2CE_Module {
    public static function getMethods() {
        return array(
            'I2CE_Swiss_Default_Leaf->editValue_string_single'=>'editValue_string_single',
            'I2CE_Swiss_Default_Leaf->editValue_string_many'=>'editValue_string_many',
            'I2CE_Swiss_Default_Leaf->editValue_delimited_single'=>'editValue_delimited_single',
            'I2CE_Swiss_Default_Leaf->editValue_delimited_many'=>'editValue_delimited_many',
            'I2CE_Swiss_Default_Leaf->editValue_boolean_single'=>'editValue_boolean_single',
            'I2CE_Swiss_Default_Leaf->editValue_boolean_many'=>'editValue_boolean_many',
            'I2CE_Swiss_Default_Leaf->editValue_list_single'=>'editValue_list_single',
            'I2CE_Swiss_Default_Leaf->editValue_list_many'=>'editValue_list_many',

            'I2CE_Swiss_Default_Leaf->viewValue_string_single'=>'viewValue_string_single',
            'I2CE_Swiss_Default_Leaf->viewValue_string_many'=>'viewValue_string_many',
            'I2CE_Swiss_Default_Leaf->viewValue_delimited_single'=>'viewValue_delimited_single',
            'I2CE_Swiss_Default_Leaf->viewValue_delimited_many'=>'viewValue_delimited_many',
            'I2CE_Swiss_Default_Leaf->viewValue_boolean_single'=>'viewValue_boolean_single',
            'I2CE_Swiss_Default_Leaf->viewValue_boolean_many'=>'viewValue_boolean_many',
            'I2CE_Swiss_Default_Leaf->viewValue_list_single'=>'viewValue_list_single',
            'I2CE_Swiss_Default_Leaf->viewValue_list_many'=>'viewValue_list_many'
            );
    }





    public function editValue_delimited_many($swiss) {                
        return  $this->addTable(
            $swiss,
            'configuration_delimited_many.html',
            'configuration_delimited_individual.html',
            'delimited_many',
            true);

    }

    public function viewValue_delimited_many($swiss) {                
        return  $this->addTable(
            $swiss,
            'configuration_delimited_many.html',
            'configuration_delimited_individual_view.html',
            'delimited_many',
            false);

    }


    public function editValue_delimited_single($swiss) {        
        return  $this->addTable(
            $swiss,
            'configuration_delimited_single.html',
            'configuration_delimited_individual.html',
            'delimited_single',
            true);
    }



    public function viewValue_delimited_single($swiss) {        
        return  $this->addTable(
            $swiss,
            'configuration_delimited_single.html',
            'configuration_delimited_individual_view.html',
            'delimited_single',
            false);
    }






    public function editValue_boolean_single($swiss) {        
        $template = $swiss->getPage()->getTemplate();
        $config = $swiss->getStorage();
        $node = $template->loadFile("configuration_boolean_single.html",'li');
        if (!$node instanceof DOMNode) {
            I2CE::raiseError("Unable to load/invalid configuraiton_string_single.html");
            return  null;
        }
        if (!$config instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Expected magic data, but not received");
            return null;
        }  
        if (!$config->is_scalar()) {
            I2CE::raiseError("Warning: expecting to have a value/leaf node. Instead got node of type  at ". $config->getPath());
            return null;
        }                                             
        $attrs = array( 'name'=>$swiss->prefixName('boolean_single'));
        if ($config->getValue()) {
            $attrs['checked'] = '1';
        }
        $template->setDisplayDataImmediate('boolean_single', $attrs,$node);
        return $node;
    }




    public function viewValue_boolean_single($swiss) {        
        $template = $swiss->getPage()->getTemplate();
        $config = $swiss->getStorage();
        $node = $template->loadFile("configuration_boolean_single_view.html",'li');
        if (!$node instanceof DOMNode) {
            I2CE::raiseError("Unable to load/invalid configuraiton_string_single.html");
            return  null;
        }
        if (!$config instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Expected magic data, but not received");
            return null;
        }  
        if (!$config->is_scalar()) {
            I2CE::raiseError("Warning: expecting to have a value/leaf node. Instead got node of type  at ". $config->getPath());
            return null;
        } 
        $val = '';
        if ($config->getValue()) {
            $val = '1';
        }
        $template->setDisplayDataImmediate('boolean_single', $val,$node);
        return $node;
    }





    public function editValue_list_single($swiss) {
        $template = $swiss->getPage()->getTemplate();
        $config = $swiss->getStorage();
        $node = $template->loadFile("configuration_list_single.html",'li');
        if (!$node instanceof DOMNode) {
            I2CE::raiseError("Unable to load/invalid configuraiton_list_single.html");
            return $template->createElement("div"); //send something
        }
        if (!$config instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Expected magic data, but not received");
            return null;
        }                                               
        if (!$config->is_scalar()) {
            I2CE::raiseError("Did not receive expected magic data scalar at " . $swiss->getPath());
        }
        $selected = array();
        if ($config->setIfIsSet($selected,'')) {
            $selected = array($selected);
        }
        $list = $swiss->getStatus('list');
        if (!is_array($list)) {
            $list = array();
        }
        $template->setDisplayDataImmediate('list_single[]',$list,$node);
        $template->selectOptions('list_single[]',$selected,$node);
        $swiss->renameInputs('list_single[]',$node);
        return $node;        
    }


    public function viewValue_list_single($swiss) {
        $template = $swiss->getPage()->getTemplate();
        $config = $swiss->getStorage();
        $node = $template->loadFile("configuration_list_single_view.html",'li');
        if (!$node instanceof DOMNode) {
            I2CE::raiseError("Unable to load/invalid configuraiton_list_single.html");
            return $template->createElement("div"); //send something
        }
        if (!$config instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Expected magic data, but not received");
            return null;
        }                                               
        if (!$config->is_scalar()) {
            I2CE::raiseError("Did not receive nexpected magic data scalar at " . $swiss->getPath());
        }
        $text = '';
        $list = $swiss->getStatus('list');
        if (!is_array($list)) {
            $list = array();
        }
        if ($config->setIfIsSet($selected,'')) {
            if (in_array($selected,$list)) {
                $text = $list[$selected];
            }
        }
        $template->setDisplayDataImmediate('list_single_selected_value',$text,$node);
        return $node;        
    }



    public function editValue_list_many($swiss) {
        $template = $swiss->getPage()->getTemplate();
        $config = $swiss->getStorage();
        $node = $template->loadFile("configuration_list_many.html",'li');
        if (!$node instanceof DOMNode) {
            I2CE::raiseError("Unable to load/invalid configuraiton_list_many.html");
            return $template->createElement("div"); //send something
        }
        if (!$config instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Expected magic data, but not received");
            return null;
        }                                               
        if ($config->is_scalar()) {
            I2CE::raiseError("Unexpected magic data scalar at " . $config->getPath(false));
        }
        $selected = array();
        $config->setIfIsSet($selected,'',true);
        $list = $swiss->getStatus('list');
        if (!is_array($list)) {
            $list = array();
        }
        $template->setDisplayDataImmediate('list_many[]',$list,$node);
        $template->selectOptions('list_many[]',$selected,$node);
        $swiss->renameInputs('list_many[]', $node);
        return $node;
    }



    public function viewValue_list_many($swiss) {
        $template = $swiss->getPage()->getTemplate();
        $config = $swiss->getStorage();
        $node = $template->loadFile("configuration_list_many_view.html",'li');
        if (!$node instanceof DOMNode) {
            I2CE::raiseError("Unable to load/invalid configuraiton_list_many.html");
            return $template->createElement("div"); //send something
        }
        if (!$config instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Expected magic data, but not received");
            return null;
        }                                               
        if ($config->is_scalar()) {
            I2CE::raiseError("Unexpected magic data scalar at " . $config->getPath(false));
        }
        $selected = array();
        $config->setIfIsSet($selected,'',true);
        $list = $swiss->getStatus('list');
        if (!is_array($list)) {
            $list = array();
        }
        $list = $swiss->getStatus('list');
        if (!is_array($list)) {
            $list = array();
        }
        $values = array();
        foreach ($selected as $k) {
            if (in_array($k,$list)) {
                $values[] = $k . '=>' . $list[$k];
            } else {
                $values[] = $k;
            }
        }        
        $template->setDisplayDataImmediate('list_many_selected_values',implode(',',$values),$node);
        return $node;
    }




    public function editValue_string_single($swiss) {         
        $template = $swiss->getPage()->getTemplate();
        $config = $swiss->getStorage();
        $node = $template->loadFile("configuration_string_single.html",'li');
        if (!$node instanceof DOMNode) {
            I2CE::raiseError("Unable to load/invalid configuraiton_string_single.html");
            return  null;
        }
        if (!$config instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Expected magic data, but not received");
            return null;
        }  
        if (!$config->is_scalar()) {
            I2CE::raiseError("Warning: expecting to have a value/leaf node. Instead got node of type  at ". $config->getPath());
            return null;
        }                                             
        $validate = $swiss->getStatus('validate');
        if (!is_array($validate)) {
            $validate = array();
        }        
        if ($swiss->getStatus('required')) {
            if (!in_array('nonempty',$validate)) {
                $validate[] = 'nonempty';
            }
        }
        $value = 
        $attrs = array(
            'value'=>$config->getTranslation($swiss->getLocale()),
//            'name'=>$swiss->prefixName('string_single')
            );                                             
        if (count($validate) > 0) {
            $attrs['validate'] = implode(',',$validate);
        }        
        $template->setDisplayDataImmediate('string_single', $attrs,$node);
        $swiss->renameInputs('string_single',$node);
        return $node;
    }



    public function viewValue_string_single($swiss) {         
        $template = $swiss->getPage()->getTemplate();
        $config = $swiss->getStorage();
        $node = $template->loadFile("configuration_string_single_view.html",'li');
        if (!$node instanceof DOMNode) {
            I2CE::raiseError("Unable to load/invalid configuraiton_string_single.html");
            return  null;
        }
        if (!$config instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Expected magic data, but not received");
            return null;
        }  
        if (!$config->is_scalar()) {
            I2CE::raiseError("Warning: expecting to have a value/leaf node. Instead got node of type  at ". $config->getPath());
            return null;
        }                                             
        $value = $config->getTranslation($swiss->getLocale());
        $template->setDisplayDataImmediate('value_string_single', $value,$node);
        return $node;
    }


    public function editValue_string_many($swiss) {                
        $template = $swiss->getPage()->getTemplate();
        if ( $swiss->getStatus('showindex') === true) {
            $file  ="configuration_string_many.html";
            $sub_file = "configuration_string_many_individual.html";
        } else {
            $file = "configuration_noindex_string_many.html";
            $sub_file = "configuration_noindex_string_many_individual.html";
        }
        $node = $this->addTable($swiss,$file,$sub_file,'string_many',true);
        return $node;
    }


    public function viewValue_string_many($swiss) {                
        $template = $swiss->getPage()->getTemplate();
        if ( $swiss->getStatus('showindex') === true) {
            $file  ="configuration_string_many.html";
            $sub_file = "configuration_string_many_individual_view.html";
        } else {
            $file = "configuration_noindex_string_many.html";
            $sub_file = "configuration_noindex_string_many_individual_view.html";
        }
        $node = $this->addTable($swiss,$file,$sub_file,'string_many',false);
        return $node;
    }



    protected function addTable($swiss,$file,$sub_file, $type_value, $editable) {
        $config = $swiss->getStorage();
        $template = $swiss->getPage()->getTemplate();
        $node = $template->loadFile($file,'li');
        if (!$node instanceof DOMNode) {
            I2CE::raiseError("Unable to load/invalid configuraiton_string_single.html");
            return null;
        }
        if (!$config instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("Expected magic data, but not received");
            return null;
        }                                            
        if ($config->is_scalar()) {
            I2CE::raiseError("Have unxpected scalar at " . $config->getPath(false));
            return null;
        }
        $tableNode = $template->query(".//table[@id='value_table']",$node);
        if ($tableNode->length != 1) {
            I2CE::raiseError("Got unexpected number of results when looking for table with id 'value_table'.  Got " . $tableNode->length);
            return $node;
        }
        $tableNode = $tableNode->item(0);

        if ($editable) {
            $validate = $swiss->getStatus('validate');
            if (!is_array($validate)) {
                $validate = array();
            }
            if (count($validate) > 0) {
                $validate = implode(',',$validate);
            } else {
                $validate = false;
            }
            $key_validate = $swiss->getStatus('key_validate');
            if (!is_array($key_validate)) {
                $key_validate = array();
            }
            if (!in_array('magicdatakey',$key_validate)) {
                $key_validate[] = 'magicdatakey';
            }
            if (!in_array('nonempty',$key_validate)) {
                $key_validate[] = 'nonempty';
            }
            if (count($key_validate) > 0) {
                $key_validate = implode(',',$key_validate);
            } else {
                $key_validate = false;
            }
        } else {
            $validate = false;
            $key_validate = false;
        }
        foreach ($config as $key=>$val) {
            if (!is_scalar($val) ) {
                I2CE::raiseError("Expecting to have a value/leaf node. Instead got an internal node at ". $config->getPath(false) . '/' . $key);
                continue;
            }
            $kNode = $template->loadFile($sub_file,'tr');
            if (!$kNode instanceof DOMNode) {
                I2CE::raiseError("Could not append row node");
                continue;
            }
            $tableNode->appendChild($kNode);
            $tr_val = $config->traverse($key,false,false)->getTranslation($swiss->getLocale());
            if ($editable) {
                $valAttr = array(
                    'name'=>$swiss->prefixName($type_value) . ":vals:$key" ,
                    'value'=>$tr_val
                    );
                $keyAttr = array(
                    'name'=>$swiss->prefixName($type_value) .":keys:$key" ,
                    'value'=>$key
                    );
                if ($validate) {
                    $valAttr['validate'] = $validate;
                }
                if ($key_validate) {
                    $keyAttr['validate'] = $key_validate;
                }
                $template->setDisplayDataImmediate('value',  $valAttr, $kNode);
                $template->setDisplayDataImmediate('key',  $keyAttr, $kNode);
            } else {
                $template->setDisplayDataImmediate('value',  $tr_val, $kNode);
                $template->setDisplayDataImmediate('key',  $key, $kNode);
            }
        }
        return $node;
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
