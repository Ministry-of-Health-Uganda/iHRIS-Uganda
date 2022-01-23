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
* Class I2CE_LinkedListsAutoTemplate
* 
* @access public
*/


class I2CE_Gizmo_LinkedLists extends I2CE_Gizmo_Form_Base {

    protected function getDefaultOptions() {    
	return array(
	    'template'=>'auto_view_linked.html',
	    'task'=>false,
	    'style'=>'default',
	    'orders'=>array(),
            'limit'=>false,
	    'where'=>array(),
	    'printf'=>'',
	    'printf_args'=>array(),
	    'link'=>false,
	    'form'=>false,
            'link_field'=>array('id'),
            'mapped_field'=>''
	    );
    }

    protected function postProcessOptions($options) {
	if ( ! array_key_exists('form',$options)
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
		'operand'=>array(0=>$where, 1=>$options['where'])
		);
	} else {
	    $options['where'] =  $where;
	}


        $is_list = 
            ($formClass = I2CE_FormFactory::instance()->getClassName($options['form']))
            && I2CE_List::isList($formClass);

	if (count($options['orders']) == 0 && $is_list) {
            $options['orders'] = I2CE_List::getSortFields($options['form']);	    
	}

	if (count($options['printf_args']) == 0 || !$options['printf']) {
            if ($is_list) {
                $options['printf_args'] = I2CE_List::getDisplayFields($options['form'],$options['style']);
                $options['printf'] = I2CE_List::getDisplayString($options['form'],$options['style']);
            }
	}
	if (!$options['link']) {
	    if ($this->page->module() == 'I2CE') {
		$url = $this->page->page() . '/' . implode('/', $this->page->request_remainder());
	    } else {
		$url = $this->page->module() . '/' . $this->page->page() . '/' . implode('/', $this->page->request_remainder());
	    }
            $options['link']  = $url . "?type=" . $options['form'] . "&id=";
        }
        if (is_scalar(        $options['link_field'] )) {
            $options['link_field']  = explode(':',$options['link_field']);
        }
	return $options;
    }

    public function generate($node) {
        if ( (  $this->options['task'] 
		&& I2CE_PermissionParser::taskExists($this->options['task']) 
		&& !$this->page->hasPermission("task(" . $this->options['task'] .")",$node)
		 )
	     || ! ($linkedNode = $this->template->appendFileByNode($this->options['template'],'div',$node)) instanceof DOMNode
	     || ! ($pageDispNode = $this->template->getElementByName('pager_display',0,$linkedNode))instanceof DOMNode
	     || ! ($pageResultsNode = $this->template->getElementByName('pager_results',0,$linkedNode))instanceof DOMNode
	     || ! ($tbodyNode = $this->template->getElementByName('link_fields',0,$node)) instanceof DOMNode
	    ){
	    return;
	}     
        $arg_walker =array();
        $printf_args = $this->options['printf_args'];
        foreach ($printf_args as $i=>&$arg) {
            $t_arg = explode(":",$arg);
            if (count($t_arg) > 1) {
                $arg_walker[$i] = $t_arg;
                $arg = $t_arg[0];
            }
        }
        unset($arg);

	$dispData = I2CE_FormStorage::listDisplayFields(
	    $this->options['form'],
	    $printf_args,
	    false,
	    $this->options['where'],
	    $this->options['orders'],
	    $this->options['limit']);
        if ( count ($dispData  ) == 0) {
            return ;
        }
	$pageDispNode->setAttribute('id','linked_' . $this->options['form'] . '_pager_display');
	$pageResultsNode->setAttribute('id','linked_' . $this->options['form'] . '_results');
	$dispData = $this->paginateList($dispData,array('form'=>$this->options['form']),'linked_' . $this->options['form']);
        $this->template->setDisplayDataImmediate('link_title',$this->options['title']);
        foreach ($dispData as $id=>$r_dispData) {
            $dispData = array();
            foreach($printf_args as $i=>$arg) {
                $dispData[$i] = $r_dispData[$arg];
            }
            foreach ($arg_walker as $i=>$fields) {
                $val = $dispData[$i];
                $count = 0;
                foreach ($fields as $field) {
                    $count++;
                    if ($count == 1) {
                        continue;
                    }
                    list($wform,$wid) =array_pad(explode('|',$val,2),2,'');
                    $val = I2CE_FormStorage::lookupField($wform,$wid,array($field),'');
                }
                $dispData[$i] = $val;
            }
            $text = @vsprintf($this->options['printf'],$dispData);
            //form = person_position 
            $linkid = $this->options['form'] . '|' . $id ;
            foreach ($this->options['link_field'] as $lfield) {
                list($lform,$lid) =array_pad(explode('|',$linkid,2),2,'');
                $linkid = I2CE_FormStorage::lookupField($lform,$lid,array($lfield),'');
            }
            $attrs = array(
		'href'=> $this->options['link'] . $linkid
		);
            $tbodyNode->appendChild($trNode  = $this->template->createElement('tr',array('colspan'=>2)));
            $trNode->appendChild($tdNode  = $this->template->createElement('td',array('colspan'=>2)));
            $tdNode->appendChild($this->template->createElement('a',$attrs,$text));
        }
    }



    public function paginateList($list,$qry_fields = array(), $jumper_id = 'select_list',$page_size = 50) {
        if ($this->page->module() == 'I2CE') {
            $url = $this->page->page();
        } else {
            $url = $this->page->module() . '/' . $this->page->page();
        }
	$url .='/view';
        $page_size = (int) $page_size;
        if ($page_size <=  0) {
            $page_size = 50;
        }
        $total_pages = max(1,ceil (count($list)/$page_size));
        $pageVar = 'page';
        if ($jumper_id != 'select_list') {
            $pageVar = $jumper_id . '_page';
        }
        if ($total_pages > 1) {
            $page_no =  (int) $this->page->request($pageVar);
            $page_no = min(max(1,$page_no),$total_pages);
            $offset = (($page_no - 1)*$page_size );
            $list = array_slice($list, $offset, $page_size,true);
	    I2CE_Util::merge_recursive($qry_fields,$this->page->request());
	    foreach (array($pageVar) as $key) {
                if (array_key_exists($key,$qry_fields)) {
                    unset($qry_fields[$key]);
                }
            }        
            $this->page->makeJumper($jumper_id,$page_no,$total_pages,$url,$qry_fields,$pageVar);                
        }
        return $list;
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
