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


class I2CE_Swiss_FormClassBuilder extends I2CE_Swiss {



    protected function getHandlers() {
        return I2CE::getConfig()->getAsArray('/modules/form-builder/class_handlers');
    }

    public function getChildNames() {
        return $this->storage->getKeys();
    }


    protected function getChildType($class) {
        if ( ! ($class)
            || ! class_exists($class)
            || ! ($class == 'I2CE_Form' || is_subclass_of($class,'I2CE_Form'))
            ){
            return false;
        }        
        $handlers =  $this->getHandlers();
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
        $required_keys = array('extends','name');
        foreach ($required_keys as $required_key) {
            if (!array_key_exists($required_key,$vals)
                || !is_string($vals[$required_key])
                || strlen($vals[$required_key]) == 0) {
                I2CE::raiseError('Missing required key: ' . $required_key);
                return false;
            }
        }
        $name = $vals['name'];
        $extends = $vals['extends'];

        $existing_classes = $this->storage->getKeys();
        if (! I2CE_MagicDataNode::checkKey($name)
            || (class_exists($name) && !is_subclass_of($name,'I2CE_Form'))
            || in_array($name,$existing_classes)
            ) {
            I2CE::raiseError("Invalid class name:" . $name);
            return false;
        }

        $handlers =  $this->getHandlers();
        $class = $extends;
        $found =false;
        while ((class_exists($class) 
                && (is_subclass_of($class,'I2CE_Form') || $class == 'I2CE_Form'))
            ){
            if (array_key_exists($class,$handlers) ) {
                $found = true;
                break;
            }
            $class = get_parent_class($class);
        }
        if (!$found) {
            I2CE::raiseError('No handler for the class: ' . $extends . ' has been registered');
            return false;
        }
        I2CE::raiseError("Creating page named $name which extends class $extends");
        $this->storage->$name = array('extends'=>$extends); //add the new page with its class into root magic data node 
        return true;
    }




    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode('form_class_builder_menu.html','div',$content_node)) instanceof DOMNode) {
            I2CE::raiseError("no template form_class_builder_menu.html");
            return false;
        }
        if ( ($nameNode = $this->template->getElementByName('name',0,$mainNode)) instanceof DOMNode) {
            $all_classes = $this->storage->getKeys(); //these are all the classes, not just the ones with a registered handler
            $this->template->setClassValue($nameNode,'validate_data',array('notinlist'=>$all_classes), '%');
        }
        if ( ($extendsNode = $this->template->getElementByName('extends',0,$mainNode)) instanceof DOMNode) {
            $classes = $this->getChildNames(); 
            foreach ($classes as $class) {
                $attrs = array('value'=>$class);
                $extendsNode->appendChild($this->template->createElement('option',$attrs,$class));
            }
        }
        $this->renameInputs(array('extends','name'),$mainNode);        

        if (  ($append_node = $this->template->getElementById('form_classes',$mainNode)) instanceof DOMNode) {
            $classes = $this->getChildNames(); //these are the pages that have a registered handler 
            foreach ($classes as $class) {
                if (! ($swissChild = $this->getChild($class)) instanceof I2CE_Swiss_FormClass
                    || ! ($classNode = $this->template->appendFileByNode( 'form_class_builder_each.html','li',$append_node))
                    ) {
                    continue;
                }
                $this->template->setDisplayDataImmediate("form_class",$class,$classNode);
                $this->template->setDisplayDataImmediate("form_class_edit_link",$this->getURLRoot('edit') . $this->path .'/' . $class,$classNode);
                $this->template->setDisplayDataImmediate("form_class_delete_link",$this->getURLRoot('delete') . $this->path . '/' . $class,$classNode);
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
