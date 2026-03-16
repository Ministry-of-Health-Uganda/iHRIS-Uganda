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
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.2.0
* @since v4.2.0
* @filesource 
*/ 
/** 
* Class I2CE_FormsAutoTemplate
* 
* @access public
*/


class I2CE_Gizmo_MappedLists extends I2CE_Gizmo_Form_Base {


    protected getDefaultOptions() {
	return array(
	    'task'=>false,
	    'style'=>'default',
            'orders' =>array(),
            'where' =>array(),
            'limit'=>false,
	    'mapped_field'=>false,
	    'template'=>'auto_view_list.html',
	    'auto_template'=>array(),
	    'form'=>false
	    );
    }

    protected postProcessOptions($options) {
	if (!I2CE_List::isList($this->options['form'])
	    || !$this->primaryObject instanceof I2CE_Form
	    ) {
	    return $options;
	}
	if (!$options['mapped_field']) {
	    $options['mapped_field'] = $this->primaryObject->getName();
	}
	$where = array(
	    'operator'=>'FIELD_LIMIT',
	    'field'=>$options['mapped_field'],
	    'style'=>'equals',
	    'data'=>array('value'=>$this->primaryObject->getNameId()));
	if (count($options['where'])>0) {
	    $options['where'] = array(
		'operator'=>'AND',
		'operand'=>array(0=>$data['where'], 1=>$options['where'])
		);
	} else {
	    $options['where'] =  $where;
	}
	if (count($options['orders']) = 0) {
	    $options['orders'] = I2CE_List::getSortFields($options['form'],$options['style']);
	}

        if (!$options['task'] && $options['form'] && I2CE_PermissionParser::taskExists( $task = 'can_view_database_list_' . $options['form'])) {
            $options['task']= $task;
        }
        if (!$options['title'] ) {
            $ff = I2CE_FormFactory::instance();
            if (($formObj = $ff->createContainer($options['form'])) instanceof I2CE_Form) {
                $options['title'] = $formObj->getDisplayName();
            }
        }
	return $options;
    }


    public function generate($node) {
        if ( (  $this->options['task'] 
		&& I2CE_PermissionParser::taskExists($this->options['task']) 
		&& !$this->page->hasPermission("task(" . $this->options['task'] .")",$node)
		 )
	     || !I2CE_List::isList($this->options['form'])	     
	    ){
	    return;
	}     
        $map_ids = I2CE_FormStorage::search($this->options['form'], false,$this->options['where'],$this->options['orders'],$this->options['limit']);
        foreach ($map_ids as $map_id) {
            $mapObj =$ff->createContainer($this->options['form'] .'|'.$map_id);
            if (!$mapObj instanceof I2CE_Form
                || ! $node->appendChild($divNode = $this->template->createElement('div'))
                || ! ($mapNode = $this->template->appendFileByNode($this->options['template'],'div',$divNode)) instanceof DOMNode
                ) {
                continue;
            }                                  
            $mapObj->populate();
            $mapGizmo= new I2CE_Gizmo_FormAuto($this->page,$mapObject,$this->options['auto_template']);
            $mapGizmo->generate($divNode);
        }
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
