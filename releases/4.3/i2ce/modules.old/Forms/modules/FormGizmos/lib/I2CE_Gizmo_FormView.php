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
* Class I2CE_FormAutoViewTemplate
* 
* @access public
*/


class I2CE_Gizmo_FormView extends I2CE_Gizmo_Form {

    protected function getDefaultOptions() {
        $options = parent::getDefaultOptions();
        $options['child_forms'] = array();
        $options['edit_child'] = false;
        $options['auto_edit_link'] = true;
        $options['auto_add_new_child_link'] = true;
        $options['base_url'] = '';
        $options['append_url'] = '';
        $options['ajax_load_child'] = 'on_child_links'; 
        $options['is_container'] = false;
        return $options;
    }


    protected function getLinkTypes() {
	return array_unique(array_merge(parent::getLinkTypes(),array('navigation')));
    }

    
    protected function postProcessOptions($options) {
        $options = parent::postProcessOptions($options);
        if (!$this->primaryObject instanceof I2CE_Form) {
            return $options;
        }
        if (!$options['base_url']) {
            if ($this->page->module() == 'I2CE') {
                $options['base_url'] = $this->page->page();
            } else {
                $options['base_url'] = $this->page->module() . '/' . $this->page->page();
            }
        }

        foreach ($options['child_forms'] as $child_form => &$c_data) {
            if (array_key_exists('disabled',$c_data) && $c_data['disabled']) {
                unset($options['child_forms'][$child_form]);
                continue;
            }
            if (!array_key_exists('form',$c_data) || !$c_data['form']) {
                $c_data['form'] = $child_form;
            }
            if (!array_key_exists('title',$c_data) || !$c_data['title']) {
                $ff  = I2CE_FormFactory::instance();
                if (($child_form_obj = $ff->createContainer(array($c_data['from'],0))) instanceof I2CE_Form) {
                    $c_data['title'] = $child_form_obj->getDisplayName();
                } else {
                    $c_data['title'] = $child_form;
                }
            }
            if (!array_key_exists('action_links',$c_data) || !is_array($c_data['action_links'])) {
                $c_data['action_links'] = array();
            }
            if ($options['auto_add_new_child_link']) {
                $text = 'Add new';
                I2CE::getConfig()->setIfIsSet($text,"/modules/Lists/messages/add_new_text");
                $c_data['action_links']['auto_add_new'] = array(
                    'text'=>$text,
                    'href'=>$options['base_url'] . '/edit/' . $options['append_url'] . '/' . $child_form . '?parent=' . $this->primaryObject->getNameID() ,
                    );
            }
            if (!array_key_exists('link',$c_data) || !$c_data['link']) {
                $c_data['link'] = $options['base_url'] . '/view/' . $options['append_url'] . '/' . $child_form  . '?id=';
                $c_data['link_filter'] = '.form_content';
            }
            if (!array_key_exists('container_link',$c_data) || !$c_data['container_link']) {
                $c_data['container_link'] = $options['base_url'] . '/view_container_' . $child_form . '/' . $options['append_url'] . '?id=';
                $c_data['container_link_filter'] = '.container_content';
            }
            if (!array_key_exists('ajax_load_child',$c_data)) {
                $c_data['ajax_load_child'] = $options['ajax_load_child'];
            }
        }
        unset($c_data);
        if ($options['auto_edit_link']) {
            $text = 'Update this information';
            I2CE::getConfig()->setIfIsSet($text,"/modules/Lists/messages/edit_text");
            $options['action_links']['auto_edit'] = array(
                'formfield'=>$this->primaryObject->getName() . ':id',
                'text'=>$text ,
                'href'=>$options['base_url'] . '/edit/' . $options['append_url'] . '?id='
                );
        }
        if ($options['is_container']) {
            if (array_key_exists($child_form = $options['is_container'],$options['child_forms'])) {
                $options['child_forms'] = array($child_form=>$options['child_forms'][$child_form]); //only deal with this child form
                $options['child_forms'][$child_form]['ajax_load_child'] = 'static';
            } else {
                //badness in configuration. do nothin
                $options['child_forms'] = array();
            }
        }
        return $options;
    }


    public function generate( $node) {	
	parent::generate($node);
	if (!$node instanceof DOMNode
	    || ! $this->primaryObject  instanceof I2CE_Form
            || ! ($linked_node = $this->template->getElementById('child_forms',$node)) instanceof DOMNode
            || ($this->options['is_edit'] && $this->options['edit_child'])
            ) {
	    return;
	}
		if ( is_array( $this->options['child_forms'] ) ) {
			$t_child_forms = array();
			foreach ( $this->options['child_forms'] as $k => $v ) {
				$t_child_forms[] = $k;
			}
        } else {
			I2CE::raiseError('No array');
		}
        $child_forms = array();
        if (array_key_exists('child_form_order',$this->options)
            && is_array($order = $this->options['child_form_order'])) {
			foreach ($order as $child_form) {
				if (!in_array($child_form,$t_child_forms)) {
                    continue;
                }
                $child_forms[]  = $child_form;
            }
        }
        $child_forms = array_unique(array_merge($child_forms,$t_child_forms));
        
        $id = $this->primaryObject->getNameId();
        foreach ($child_forms as  $child_form) {
            if (!array_key_exists($child_form,$this->options['child_forms'])
                || !is_array($c_data = $this->options['child_forms'][$child_form])
                ) {
                continue;
            }
            if (!array_key_exists('base_url',$c_data)) {
                $c_data['base_url'] = $this->options['base_url'];
                $c_data['append_url'] = $this->options['append_url'] . '/' . $child_form;
            }
            $container_id = 'container-' . $child_form . '-' . $id;
            $attrs = array('name'=>'child_' . $child_form, 'id'=>$container_id,'class'=>'container_content');
            $linked_node->appendChild($divNode = $this->template->createElement('div',$attrs));
            switch ($c_data['ajax_load_child']) {
            case 'on_container':
                $url = 'index.php/' . $c_data['container_link'] . $id;
                $attrs = array(
                    'href'=> $url
                    );
                if ($c_data['container_link_filter']) {
                    $js = 'ajaxLoadDiv(this,"' . $container_id .  '","' . $url . '","' . $c_data['container_link_filter'] . '");'; 
                } else {
                    $js = 'ajaxLoadDiv(this,"' . $container_id .  '","' . $url . '");'; 
                }
                $this->template->addHeaderLink('mootools-core.js');
                $this->template->addHeaderLink('mootools-more.js');
                $this->template->addHeaderLink('view.js');

                //this will be replaced by container on ajax load
                $divNode->appendChild($panelNode = $this->template->createElement('div',array('class'=>'panel panel-default')));
                $panelNode->appendChild($panelHNode = $this->template->createElement('div',array('class'=>'panel-heading')));
                $panelHNode->appendChild ($this->template->createElement('a',array('onClick'=>$js,'class'=>'clicker_hide')));
                $panelHNode->appendChild ($titleNode = $this->template->createElement('h3',array('style'=>'display:inline-block;margin-bottom:1em')));
                $titleNode->appendChild ($this->template->createElement('a',$attrs,$c_data['title']));
                $panelNode->appendChild($panelCNode = $this->template->createElement('div',
                                                                                     array('class'=>'panel-heading', 
                                                                                           'id'=>$container_id,
                                                                                           'style'=>'display:none'
                                                                                         )));

                break;
            case 'static':
                if (!array_key_exists('do_ajax',$c_data)) {
                    $c_data['do_ajax'] = false;
                }
                $gizmo= new I2CE_Gizmo_FormAjaxChildView($this->page,$this->primaryObject,$c_data);
                $gizmo->generate($divNode);
                break;
            case 'on_child_links':
            default:
                if (!array_key_exists('do_ajax',$c_data)) {
                    $c_data['do_ajax'] = true;
                }
                $gizmo= new I2CE_Gizmo_FormAjaxChildView($this->page,$this->primaryObject,$c_data);
                $gizmo->generate($divNode);
                break;
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
