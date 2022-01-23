<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
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
* @subpackage pages
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_PageArgs
* 
* @access public
*/


class I2CE_Swiss_FormClass_Fields extends I2CE_Swiss {


    protected function getHandlers() {
        return I2CE::getConfig()->getAsArray('/modules/form-builder/field_handlers');
    }

    protected function getChildType($child) {
        $formfield = false;
        $class = false;
        if (! ($this->storage->setIfIsSet($formfield,"$child/formfield"))
            || !I2CE_MagicDataNode::checkKey($formfield)
            || ! (I2CE::getConfig()->setIfIsSet($class,"/modules/forms/FORMFIELD/$formfield"))
            || ! ($class)
            || ! class_exists($class)
            || ! ($class == 'I2CE_FormField' || is_subclass_of($class,'I2CE_FormField'))
            ){
            return false;
        }        
        $handlers =   $handlers = $this->getHandlers();
        while ($class) {
            //See if any swiss object has been registered to handle this class.
            if (array_key_exists($class,$handlers)
                && is_array($handlers[$class])
                && array_key_exists('swiss',$handlers[$class])
                && is_scalar($swiss =  $handlers[$class]['swiss'])
                && $swiss
                ) {
                return $swiss;
            }
            //nothing valid, try the parent class
            $class = get_parent_class($class);
        }
        return false;
    }

    
    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        //add a new field name,formfield,header
        foreach (array('name','formfield','header') as $key) {
            if (!array_key_exists($key,$vals)
                ||!is_scalar($$key = $vals[$key])
                || !$$key
                ) {
                I2CE::raiseError("Missing $key when creating a new field");
                return false;
            }
        }
        if (!I2CE_MagicDataNode::checkKey($name)
            || in_array($name,$this->getFieldNames(true))
            ) {
            I2CE::raiseError("Could not create a new field named  $name");
            return false;
        }
        $formfields = I2CE::getConfig()->getKeys("/modules/forms/FORMFIELD");
        if (!in_array($formfield,$formfields)) {
            I2CE::raiseError("Invalid form field");
            return false;
        }
        $this->storage->$name = array('formfield'=>$formfield);
        if (! ($swissField = $this->getChild($name)) instanceof I2CE_Swiss_FormField) {
            I2CE::raiseError("Could not get field controler");
            return false;
        }
        if (! ($swissHeaders = $swissField->getChild('headers',true)) instanceof I2CE_Swiss_FormField_Headers) {
            I2CE::raiseError("Could not get headers controler");
            return false;
        }
        $swissHeaders->processValues(array('val'=>array('default'=>$header)));
        return true;
    }


    public function getFieldNames($inherited) {
        $names = $this->storage->getKeys();
        if ($inherited
            && $this->parent instanceof I2CE_Swiss_FormClass
            && ($parentSwiss = $this->parent->getParentSwiss()) instanceof I2CE_Swiss_FormClass
            && ($parentFields = $parentSwiss->getChild('fields',true)) instanceof I2CE_Swiss_FormClass_Fields
            ){
            $names = array_unique(array_merge($names,$parentFields->getFieldNames(true)));
        }
        return $names;
    }

    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('swiss_form_class_fields.html','div',$content_node)) instanceof DOMNode
            ) {
            return false;
        }
        if (($formfieldNode = $this->template->getElementByName('formfield',0,$mainNode))instanceof DOMNode) {
            $formfields = I2CE::getConfig()->getKeys("/modules/forms/FORMFIELD");
            foreach ($formfields as $formfield) {
                $formfieldNode->appendChild($this->template->createElement('option',array('value'=>$formfield),$formfield));
            }
        }
        if (($nameNode = $this->template->getElementByName('name',0,$mainNode))instanceof DOMNode) {
            $names = $this->getFieldNames(true);
            $this->template->setClassValue($nameNode,'validate_data',array('notinlist'=>$names), '%');
        }        


        $this->renameInputs(array('header','name','formfield'),$mainNode);
        if ( ($listNode = $this->template->getElementById('field_list', $mainNode)) instanceof DOMNode
             &&($this->parent instanceof I2CE_Swiss_FormClass)
            ) {                 
            $parents = array_merge(array($this->parent) , $this->parent->getAllParentSwiss());
            foreach ($parents as $parent) {
                if (! ($swissFields = $parent->getChild('fields')) instanceof I2CE_Swiss_FormClass_Fields
                    || count($fieldnames = $swissFields->getFieldNames(false)) == 0) {
                    continue;
                }
                if ($this->parent->name == $parent->name) {
                    $listNode->appendChild($liNode = $this->template->createElement('li',array(),$this->parent->name));
                } else {
                    $url = $this->getURLRoot($action)  .  $parent->path ;
                    $linkNode = $this->template->createElement('a',array('href'=>$url),$parent->name);
                    $listNode->appendChild($liNode = $this->template->createElement('li',array()));
                    $liNode->appendChild($linkNode);
                        
                }
                $liNode->appendChild($ulNode = $this->template->createElement('ul',array()));
                foreach ($fieldnames as $fieldname) {
                    if (!($swissFields = $parent->getChild('fields')) instanceof I2CE_Swiss_FormClass_Fields
                        || !($swissField = $swissFields->getChild($fieldname)) instanceof I2CE_Swiss_FormField
                        || ! ($ulNode->appendChild($liFNode = $this->template->createElement('li',array('class'=>'relationship_joins')))) instanceof DOMNode
                        || ! ($fieldNode = $this->template->appendFileByNode('swiss_form_class_fields_each.html','div',$liFNode)) instanceof DOMNode
                        ) {
                        continue;
                    }
                    $this->template->setDisplayDataImmediate('name',$fieldname,$fieldNode);
                    $swissField->addAjaxLink('field_link','field_contents','field_ajax',$fieldNode,$action, $transient_options);            
                    $delete_link = $swissField->getURLRoot('delete_class')  .  $swissField->path;
                    $this->template->setDisplayDataImmediate('delete_link',$delete_link,$fieldNode);
                }                
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
