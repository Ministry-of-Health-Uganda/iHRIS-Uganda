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
*  I2CE_SwissConfig_FormRelationship_Joins
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_Swiss_FormBuilder extends I2CE_Swiss {






    protected function getChildType($child) {
        return 'Form';
    }


    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        $required_keys = array('storage','name','class','display');
        foreach ($required_keys as $required_key) {
            if (!array_key_exists($required_key,$vals)
                || !is_string($val = $vals[$required_key])
                || strlen($val) == 0) {
                I2CE::raiseError('Missing required key: ' . $required_key);
                return false;
            }
            $$required_key = $val; //sets is so if $key =='storage', then $storage = $val
        }

        $existing_forms = $this->storage->getKeys();
        if (! I2CE_MagicDataNode::checkKey($name)
            || in_array($name,$existing_forms)
            ) {
            I2CE::raiseError("Invalid form name:" . $name);
            return false;
        }
        if (!in_array($storage, $handlers = I2CE::getConfig()->getKeys('/modules/form-builder/storage_handlers')) ) {
            I2CE::raiseError("Invalid storage ($storage) not one of:" . implode(",",$handlers));
            return false;
        };
        if (!in_array($class, I2CE::getConfig()->getKeys("/modules/forms/formClasses"))) {
            I2CE::raiseError("Invalid class: $class");
        }

        $this->storage->$name = array('storage'=>$storage,'class'=>$class,'display'=>$display); //add the new page with its class into root magic data node 
        $this->storage->$name->setTranslatable('display');
        return true;
    }




    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('form_builder_menu.html','div',$content_node)) instanceof DOMNode) {
            I2CE::raiseError("no template form_builder_menu.html");
            return false;
        }
        if ( ($nameNode = $this->template->getElementByName('name',0,$mainNode)) instanceof DOMNode) {
            $all_forms = $this->storage->getKeys(); //these are all the classes, not just the ones with a registered handler
            $this->template->setClassValue($nameNode,'validate_data',array('notinlist'=>$all_forms), '%');
        }
        if ( ($storageNode = $this->template->getElementByName('storage',0,$mainNode)) instanceof DOMNode) {
            $storeid = "class_" . rand(0,getrandmax());
            $storageNode->setAttribute('id',$storeid);
            $storages = I2CE::getConfig()->getAsArray('/modules/form-builder/storage_handlers');
            foreach ($storages as $storage=>$data) {
                if (!is_array($data)
                    ||!array_key_exists('swiss',$data)
                    || !is_scalar($swiss = $data['swiss'])
                    || !$swiss
                    ) {
                    continue;
                }
                $attrs = array('value'=>$storage);
                if (array_key_exists('description',$data)) {
                    $attrs['title'] = $data['description'];
                } 
                $storageNode->appendChild($this->template->createElement('option',$attrs,$storage));
            }
        }
        if ( ($classNode = $this->template->getElementByName('class',0,$mainNode)) instanceof DOMNode) {
            $classes = I2CE::getConfig()->getKeys("/modules/forms/formClasses");
            $def_storage = array();
            foreach ($classes as $class) {
                if ($class == 'I2CE_List' || is_subclass_of($class,'I2CE_List')) {
                    $def_storage[$class] = 'magicdata';
                } else {
                    $def_storage[$class] = 'entry';
                }
                $attrs = array('value'=>$class);
                $classNode->appendChild($this->template->createElement('option',$attrs,$class));
            }
            $js = 'var storages = ' . json_encode($def_storage) . '; var storage=storages[this.value];  var storageNode = $("' . $storeid . '"); if (storageNode) { storageNode.set("value",storage);}';
            $classNode->setAttribute('onChange',$js);

        }


        $this->renameInputs(array('class','name','display','storage'),$mainNode);        

        if (  ($append_node = $this->template->getElementById('forms',$mainNode)) instanceof DOMNode) {
            $forms = $this->storage->getKeys();
            foreach ($forms as $form) {
                if (! ($swissChild = $this->getChild($form)) instanceof I2CE_Swiss_Form
                    || ! ($formNode = $this->template->appendFileByNode( 'form_builder_each.html','li',$append_node))
                    ) {
                    continue;
                }
                $this->template->setDisplayDataImmediate("form",$form,$formNode);
                $this->template->setDisplayDataImmediate("form_edit_link",$this->getURLRoot('edit') . $this->path .'/' . $form,$formNode);
                $this->template->setDisplayDataImmediate("form_delete_link",$this->getURLRoot('delete') . $this->path .'/' . $form,$formNode);
            }
        }
        return true;
    }


    
  }
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
