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
* @subpackage list
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_FormAutoListTemplate
* 
* @access public
*/


class I2CE_Gizmo_List extends I2CE_Gizmo_Form {


    protected function getDefaultOptions() {
        $options = parent::getDefaultOptions();
        $options['mapped']=array();
        $options['linked']=array();
        return $options;
    }

    protected function postProcessOptions($options) {
        if (!$options['template']) {
            if ($options['is_edit']) {
                $options['template'] = "auto_edit_list.html";
            } else {
                $options['template'] = "auto_view_list.html";
                if ( !array_key_exists( 'skip_display_fields', $options ) || !is_array( $options['skip_display_fields'] ) ) {
                    $options['skip_display_fields'] = array();
                }
                if ( !in_array( 'i2ce_hidden', $options['skip_display_fields'] ) ) {
                    $options['skip_display_fields'][] = 'i2ce_hidden';
                }
            }
	}
        return parent::postProcessOptions($options);
    }


    public function generate( $node) {
	parent::generate($node);
	if (!$node instanceof DOMNode
	    || ! $this->primaryObject  instanceof I2CE_Form
            || $this->options['is_edit']
            ) {
	    return;
	}
	if (count($this->options['mapped'])> 0 
            && ($mapped_node = $this->template->getElementById('mapped_forms',$node)) instanceof DOMNode
            ) {
            $this->template->addHeaderLink('view.js');
            foreach ($this->options['mapped'] as $list => $form_data) {
                $mapped_node->appendChild($divNode = $this->template->createElement('div'));
                $link_data['form'] =$form;
                $gizmo = new I2CE_Gizmo_MappedLists($this->page,$this->primaryObject,$form_data);
                $gizmo->generate($divNode);
            }
        }	
        if (count($this->options['linked']) > 0
            && ($linked_node = $this->template->getElementById('linked_forms',$node)) instanceof DOMNode
            ) {
            foreach ($this->options['linked'] as  $link_data) {
                $linked_node->appendChild($divNode = $this->template->createElement('div'));
                $gizmo = new I2CE_Gizmo_LinkedLists($this->page,$this->primaryObject,$link_data);
                $gizmo->generate($divNode);
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
