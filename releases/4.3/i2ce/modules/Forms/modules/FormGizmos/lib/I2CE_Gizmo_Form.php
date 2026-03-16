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
* @subpackage form
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_FormAutoTemplate
* 
* @access public
*/


class I2CE_Gizmo_Form extends I2CE_Gizmo_Form_Base {


    protected function getDefaultOptions() {
	$options =  array(
            'default_disabled'=>false,
            'task'=>false,
            'display_order'=>'',
            'display_name'=>'',
            'fields'=>array(),
            'skip_display_fields'=>array(),
            'skip_unset_fields'=>array('remap'),
            'is_edit'=>false,
            'field_data'=>array(),
            'save_link'=>false,
            'title'=>'',
            'text'=>'',
            'template'=>false
            );
        foreach ($this->getLinkTypes() as $type) {
            $options[$type . '_links']= array();
        }
        if ($this->primaryObject instanceof I2CE_Form) {
            $options['display_name'] =  $this->primaryObject->getDisplayName();
            $options['text'] =  $this->primaryObject->getDisplayName();
            if (!$options['title']) {
                $title = "View %s";
                I2CE::getConfig()->setIfIsSet($title,'/modules/forms/messages/title');             
                $options['title'] = @vsprintf($title,$options['text']);
            } else {
                if (array_key_exists('title_args',$data) && is_array($data['title_args'])) {
                    $args = array();
                    foreach ($data['title_args'] as $i=>$fieldName) {
                        $val = '';
                        if ( ($fieldObj = $this->primaryObject->getField($fieldName)) instanceof I2CE_FormField) {
                            $val =  $fieldObj->getDisplayValue();
                        }
                        $args[$i] = $val;
                    }
                    ksort($args);
                    $options['title'] = @vsprintf($title,$args);
                } else {
                    if (preg_match('/%s/',$options['title'])) {
                        $options['title'] = @vsprintf($title,$options['text']);
                    } else {
                        $options['title'] = $this->primaryObject->getDisplayName();
                    }
                }
            }
            $options['subtitle'] =  $this->primaryObject->getDisplayName();

        }
        return $options;
    }


    protected function postProcessOptions($options) {
        $options = parent::postProcessOptions($options);
        if (!$this->primaryObject instanceof I2CE_Form) {
            return $options;
        }
        foreach ($this->primaryObject->getFieldNames() as $field) {
            if (!array_key_exists($field,$options['field_data'])) {
                $options['field_data'][$field]=array();
            }
            if (!array_key_exists('enabled',$options['field_data'][$field])) {
                if ($options['default_disabled']) {
                    $options['field_data'][$field]['enabled'] =  0;
                } else {
                    $fieldObj = $this->primaryObject->getField($field);
                    $is_set = ($fieldObj instanceof I2CE_FormField) && ($fieldObj->issetValue());
                    $options['field_data'][$field]['enabled'] =  
                        (!in_array($field,$options['skip_display_fields']))
                        && ($is_set || !in_array($field,$options['skip_unset_fields']))
                        ;
                }
            }
        }
        if (is_string($options['display_order'])) {
            $options['display_order'] = explode(",",$options['display_order']);
        }
        if ($options['is_edit'] && !$options['save_link']) {
            if ($this->page->module() == 'I2CE') {
                $url = $this->page->page();
            } else {
                $url = $this->page->module() . '/' . $this->page->page();
            }
            $options['save_link'] = $url . '/' . implode("/", $this->page->request_remainder());
        }
        if (!$options['template']) {
            if ($options['is_edit']) {
                $options['template'] = "auto_edit_form.html";
            } else {
                $options['template'] = "auto_view_form.html";
            }
	}
        return $options;
    }





    public function generate( $top_node) {	
        if (!$top_node instanceof DOMNode            
            || ! ($node = $this->template->appendFileByNode($this->options['template'], 'div',  $top_node))
            || !$this->primaryObject instanceof I2CE_Form
            || ($this->options['task'] 
                && I2CE_PermissionParser::taskExists($this->options['task']) 
                && !$this->page->hasPermission("task(" . $this->options['task'] . ")",$node)
                )
            ) {
	    return false;
        }

        I2CE::raiseError("Generating for " . $this->primaryObject->getNameID()); 
        if ($this->options['is_edit'])  {
            if (! (  $formNode = $this->template->getElementByTagName('form',0,$node)) instanceof DOMNode) {
                return false;
            }
            $formNode->setAttribute('action',$this->options['save_link']);
        }
        
        $this->template->setForm($this->primaryObject,$node);
        $this->template->setDisplayDataImmediate('form_display_name',$this->options['display_name'],$node);
        $this->template->setDisplayDataImmediate('form_title',$this->options['title'],$node);
        $this->template->setDisplayDataImmediate('form_subtitle',$this->options['subtitle'],$node);

	foreach ($this->getLinkTypes() as $type) {
	    $added = 0;
	    if ( ($ulNode= $this->template->getElementByName('form_'. $type . '_links',0,$node)) instanceof DOMNode 
		) {           
		$added = $this->addLinks('li', $this->options[$type .'_links'],$ulNode);
	    }
	    if ($added == 0
		&& ($linkContainer = $this->template->getElementByName('form_' . $type,0,$node)) instanceof DOMNode) {
		$this->template->removeNode($linkContainer);
	    }
	}
        if ( ($fieldsNode= $this->template->getElementByName('form_fields',0,$node)) instanceof DOMNode ) {
            $field_tasks = array();
            $all_field_names = $this->primaryObject->getFieldNames();
            $field_names = array();
            $display_order =$this->options['display_order'];
            foreach($display_order  as $field_name) {
                if ( ($pos = array_search($field_name,$all_field_names)) === false) {
                    continue;
                }
                unset($display_order[$pos]);
                $field_names[] = $field_name;
            }
            $listed_fields = array_keys($this->options['field_data']);
            $field_names = array_unique(array_merge($field_names,$all_field_names,$listed_fields));
	    $count = 0;
            foreach ($field_names as $field_name) {
                if (!$this->options['field_data'][$field_name]['enabled']) {
                    continue;
                }
                if ($this->options['is_edit']) {
		    if (($count % 2) == 0) {
			$trNode = $this->template->createElement('tr');
			$fieldsNode->appendChild($trNode);
		    }
		    $this->fieldEdit($field_name,$this->options['field_data'][$field_name],$trNode);
		} else {
		    $this->fieldView($field_name,$this->options['field_data'][$field_name],$fieldsNode,$count %2);
		} 
                $count++;
            }
        }
	return true;
    }

    protected function fieldEdit($field,$f_data,$node) {
	if (!$node instanceof DOMNode
	    ||!is_array($f_data)
	    ||!is_scalar($field)
	    ||!$this->primaryObject instanceof I2CE_Form
	    ){
	    return;
	}
        $form = $this->primaryObject->getName() ;	
        if (!$f_data['enabled']) { 
            return;	    
	}

	$attrs = array();
	if (array_key_exists('attributes',$f_data) && is_array($f_data['attributes'])) {
	    $attrs = $f_data['attributes'];
	}
	$attrs['type']='form';
	$attrs['name']=$form. ':' . $field;
	if (!array_key_exists('showhead',$attrs)) {
	    $attrs['showhead'] = 'default';
	}
	if (!$attrs['showhead']) {
	    unset($attrs['showhead']);
	}
        if ( ($fieldObj = $this->primaryObject->getField($field)) instanceof I2CE_FormField_MAPPED
             && $fieldObj->getOption(array('meta','display','default','reportSelect','enabled'))
            ) {
            $attrs['display'] = 'reportSelect';
        }
	$tdNode = $this->template->createElement('td');
	$tdNode->appendChild($this->template->createElement('span',$attrs));
	$node->appendChild($tdNode);
    }


    protected function fieldView($field,$f_data,$node,$parity=0) {
	if (!$node instanceof DOMNode
	    ||!is_array($f_data)
	    ||!is_scalar($field)
	    ||!$this->primaryObject instanceof I2CE_Form
	    ){
	    return;
	}
        $form = $this->primaryObject->getName() ;	
        if (!$f_data['enabled']) { 
            return;
	}
	$attrs = array();
	if (array_key_exists('attributes',$f_data) && is_array($f_data['attributes'])) {
	    $attrs = $f_data['attributes'];
	}
	$attrs['type']='form';
	if (array_key_exists('is_method',$f_data) && $f_data['is_method']) {
	    $attrs['name']=$form. '->' . $field_name .'()';
	    $node->appendChild($trNode  = $this->template->createElement('tr',array('colspan'=>2)));
	    $trNode->appendChild($tdNode  = $this->template->createElement('td',array('colspan'=>2)));
            $trNode->setAttribute('class',$parity? 'even':'odd');
	    $tdNode->appendChild($this->template->createElement('span',$attrs));
	} else {
	    $attrs['name']=$form. ':' . $field;
	    if (!array_key_exists('showhead',$attrs)) {
		$attrs['showhead'] = 'default';
	    }
	    if (!$attrs['showhead']) {
		unset($attrs['showhead']);
	    }
	    $attrs['auto_link'] = 1;
            $attrs['class']=$parity? 'even':'odd';
	    $node->appendChild($this->template->createElement('span',$attrs));
	}
    }




    protected function getLinkTypes() {
	return array('action','edit');
    }











}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
