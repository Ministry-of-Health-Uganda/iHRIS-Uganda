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
* @package i2ce
* @subpackage page
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_Swiss_Page
* 
* @access public
*/


class I2CE_Swiss_FormClass extends I2CE_Swiss {

    public function getParentSwiss() {
        if ( $this->parent instanceof I2CE_Swiss_FormClassBuilder) {
            return $this->parent->getChild($this->getField('extends'));
        }
        return false;        
    }

    public function getAllParentSwiss() {
        $parents = array();
        if (($parent = $this->getParentSwiss()) instanceof I2CE_Swiss_FormClass) {
            $parents = array_merge(array($parent) ,$parent->getAllParentSwiss());
        }
        return $parents;
    }


    /**
     * Update config for given values
     * @param array $vals.  An array of values (presumably from $_POST) to update magic data from
     * @returns  true on sucess
     */
    public function processValues($vals) {
        if (array_key_exists('extends',$vals)
            && is_scalar($extends = $vals['extends'])
            && ($extends == 'I2CE_Form' || is_subclass_of($extends,'I2CE_Form'))
            ) {
            //check to make sure we don't make a circular reference against an existing class
            //e.g  if we have  'I2CE_MyClass' extends $this->name = 'I2CE_OtherClass' extends I2CE_Form
            // and if $extends = 'I2CE_MyClass' we are not good
            $classes = array();
            $class  = $extends ;
            while ($class) {
                $classes[] = $class;
                $class = get_parent_class($class);
                
            }
            if (!in_array($this->name,$classes)) {
                $this->setField('extends',$extends);
            }
        }
        //for the moment only allow entry or magic data
        if (array_key_exists('display',$vals)
            && is_scalar($display = $vals['display'])
            ){
            $this->setTranslatableField('display',$display);
        }
	return true;
    }

    protected function getTemplate() {
        return 'swiss_form_class.html';
    }

    protected function getMetaHandler() {
        return 'FormClass_Meta';
    }

    protected function getChildType($child) {
        if ($child =='meta') {
            return $this->getMetaHandler();
        } else  if ($child =='fields') {
            return 'FormClass_Fields';
        } else{
            return parent::getChildType($child);
        }
    }

    public function displayValues($content_node,$transient_options, $action) {
        if (! ($mainNode = $this->template->appendFileByNode($this->getTemplate(),'div',$content_node)) instanceof DOMNode) {
            I2CE::raiseError("Could not load " . $this->getTemplate());
            return false;
        }
        $this->displayMain($mainNode,$transient_options,$action);
        if ( ($fieldsChild = $this->getChild('fields',true)) instanceof I2CE_Swiss
             && ( $fieldsNode = $this->template->getElementById('fields',$mainNode)) instanceof DOMNode
            ) {
            $fieldsChild->addAjaxLink('fields_link','fields_container', 'fields_ajax' ,$fieldsNode,$action, $transient_options);
        }
        if ( ($metaChild = $this->getChild('meta',true)) instanceof I2CE_Swiss
             && ( $metaNode = $this->template->getElementById('meta',$mainNode)) instanceof DOMNode
            ) {
            $metaChild->addAjaxLink('meta_link','meta_container', 'meta_ajax' ,$metaNode,$action, $transient_options);
        }
        return true;
    }

    protected function displayMain($mainNode,$transient_options,$action) {
        $this->template->setDisplayDataImmediate('display',$this->getField('display'),$mainNode);
        $this->template->setDisplayDataImmediate('name',$this->name,$mainNode);
        if ( ($extendsNode = $this->template->getElementByName('extends',0,$mainNode)) instanceof DOMNode) {
            $extends = $this->getField('extends');
            $classes = array();
            if ($this->parent instanceof  I2CE_Swiss_FormClassBuilder) {
                $classes = $this->parent->getChildNames();
            }
            foreach ($classes as $class) {
                //now check this isn't making a circular reference
                $pclasses = array();
                $pclass  = $extends ;
                while ($pclass) {
                    $pclasses[] = $pclass;
                    $pclass = get_parent_class($pclass);
                }
                if (in_array($this->name,$pclasses)) {
                    continue;
                }
                $attrs = array('value'=>$class);
                if ($class == $extends) {
                    $attrs['selected'] = 'selected';
                }
                $extendsNode->appendChild($this->template->createElement('option',$attrs,$class));
            }
        }

        $this->renameInputs(array('extends','display'),$mainNode);
        return true;
    }

	


  }
# Local Variables:
# mode: php
# c-default-task: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
