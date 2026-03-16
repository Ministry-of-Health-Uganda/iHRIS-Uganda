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
* Class I2CE_FormAutoTemplate_Base
* 
* @access public
*/


abstract class I2CE_Gizmo_Form_Base extends I2CE_Fuzzy{
    protected $template;
    protected $page;
    protected $options;
    protected $primaryObject;
    public function __construct($page,$primaryObject,$options = array()) {
	$this->primaryObject = $primaryObject;
	$this->page = $page;
	$this->template = $this->page->getTemplate();
	if (!is_array($options)) {
	    $options = array();
	}
	$def_options = $this->getDefaultOptions();
	I2CE_Util::merge_recursive($def_options,$options);
	$this->options = $this->postProcessOptions($def_options);
    }


    protected function postProcessOptions($options) {
        if( !$options['task'] 
            && ($this->primaryObject instanceof I2CE_Form)
            && ($form = $this->primaryObject->getName())
            ){
            if ($this->options['is_edit']) {
                $task = 'can_edit_form_' . $form;
            } else {
                $task = 'can_view_form_' . $form;
            }
            if (I2CE_PermissionParser::taskExists($task)) {
                $options['task'] =$task;
            }
        }
	return $options;
    }

    protected function getDefaultOptions() {
        return array(
            'is_edit'=>false,
            'task'=>false
            );
    }


    /**
     * Check if a named option exists
     * @param string $option
     * @returns boolean
     **/
    public function hasOption($option) {
        return (array_key_exists($option,$this->options) && $this->options[$option] !== null);
    }
    /**
     * set a named option exists
     * @param string $option
     * @param mixed $val
     **/
    public function setOption($option,$val) {
        if (is_scalar($option)) {
            $option = array($option);
        }
        if (!is_array($option)) {
            return;
        }
        $options =  &$this->options;
        foreach ($option as $key) {
            if (!is_array($options)) {
                $options = array();
            }
            if (!array_key_exists($key,$options)) {
                $options[$key] = null;
            }
            $options = &$options[$key];
        }
        $options = $val;
    }
    /**
     * Get the value stored at a named option exists
     * @param string $option
     * @returns mixed
     **/
    public function getOption($option) {
        if (is_scalar($option)) {
            $option = array($option);
        }
        if (!is_array($option)) {
            return;
        }
        $options = $this->options;
        foreach ($option as $key) {
            if (!is_array($options) || !array_key_exists($key,$options)) {
                return null;
            }
            $options = $options[$key];
        }
        return $options;
    }



    protected function optionsHasPath($path) {
        $options = $this->options;
        if (is_string($path)) {
            $path = explode('/',$path);
        } 
        if (!is_array($path)) {
            return false;
        }
        while(count($path) > 0) {
            if (!is_array($options)) {
                return false;
            }
            $p = array_shift($path);
            if (!array_key_exists($p,$options)) {
                return false;
            }
            $options = $options[$p];
        }
        return true;
    }

    protected function getOptionsByPath($path) {
        $options = $this->options;
        $path = explode('/',$path);
        while(count($path) > 0) {
            if (!is_array($options)) {
                return null;
            }
            $p = array_shift($path);
            if (!array_key_exists($p,$options)) {
                return null;
            }
            $options = $options[$p];
        }
        return $options;
    }


    abstract public function generate( $node);


    protected function addLinks($tag,$data,$containerNode) {
        $added =0;
        foreach ($data as $name=>$link_data) {
            if (array_key_exists('task',$link_data) && $link_data['task']) {
                $task = $link_data['task'];
                if (I2CE_PermissionParser::taskExists($task) && !$this->page->hasPermission("task($task)",$containerNode)) {
                    continue;
                }     
            }
            if (!array_key_exists('href',$link_data) || !$link_data['href']
                || !array_key_exists('text',$link_data) || !$link_data['text']
                ) {
                continue;
            }
            if (array_key_exists('formfield',$link_data) && $link_data['formfield']) {
                $attrs = array(
                        'type'=>'form',
                        'name'=>$link_data['formfield'],
                        'href'=>$link_data['href']
                        );
                if (array_key_exists('attributes',$link_data) && is_array($link_data['attributes'])) {
                    foreach ($link_data['attributes'] as $attr=>$val) {
                        if (!is_scalar($val)) {
                            continue;
                        }
                        $attrs[$attr] = $val;
                    }
                }
		if (!array_key_exists('class',$attrs)) {
		    $attrs['class'] = '';
		}
		$attrs['class'] .= ' action_link_' . $name;
                $tagNode = $this->template->createElement($tag);
                $tagNode->appendChild($this->template->createElement('span',$attrs,$link_data['text']));
                $containerNode->appendChild($tagNode);
            } else {
                $attrs = array('href'=>$link_data['href'],'class'=>'action_link_' . $name);
                $tagNode = $this->template->createElement($tag);
                $tagNode->appendChild($this->template->createElement('a',$attrs,$link_data['text']));
                $containerNode->appendChild($tagNode);
            }
            $added++;
        }
        return $added; 
    }


    protected function getLinkTypes() {
	return array();
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
