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
* Class I2CE_FormAjaxViewTemplate
* 
* @access public
*/


class I2CE_Gizmo_FormAjaxChildView extends I2CE_Gizmo_Form_Base {



    protected function getDefaultOptions() {
        return  array(
            'link'=>false,
            'link_filter'=> false,
            'task'=>false,
            'form'=>false,
            'printf' =>false,
            'printf_args' =>array(),
            'orders' =>array(),
            'where' =>array(),
            'limit'=>false,
            'template'=>'auto_view_child.html',
            'template_child'=>"auto_view_child_no_ajax.html",
            'action_links'=>array(),
            'title'=>false,
            'do_ajax'=>true
            );
    }

    protected function postProcessOptions($options) {
        if (!$options['title'] ) {
            $ff = I2CE_FormFactory::instance();
            if (($formObj = $ff->createContainer($options['form'])) instanceof I2CE_Form) {
                $options['title'] = $formObj->getDisplayName();
            }
        }
        return $options;
    }

    protected function getLinkTypes() {
	return array('action','edit','navigation');
    }

    public function generate($node) {        
	if (!$node instanceof DOMNode
	    || !$this->primaryObject instanceof I2CE_Form
            || (I2CE_PermissionParser::taskExists($this->options['task']) &&  !$this->page->hasPermission("task(" . $this->options['task'] .")",$node))
            || ! ($linkedNode = $this->template->appendFileByNode($this->options['template'],'div',$node)) instanceof DOMNode
            ||  !( $tbodyNode = $this->template->getElementByName('child_fields',0,$node)) instanceof DOMNode
            ) {
	    return false;
	}
	$added = 0;
	if (count($this->options['action_links']) > 0
	    && ($ulNode = $this->template->getElementByName('child_links',0,$linkedNode)) instanceof DOMNode 
	    ){
            $added =  $this->addLinks('li',$this->options['action_links'],$ulNode);
        }
	if ($added == 0
	    && ($containerNode = $this->template->getElementByName('child_actions',0,$linkedNode)) instanceof DOMNode
	    ) {
	    $this->template->removeNode($containerNode);
	}
        $this->template->setDisplayDataImmediate('child_title',$this->options['title'],$linkedNode);
        $dispDatas = array();
        $arg_walker = array();
        if ( $this->options['printf']
             &&  is_array($this->options['printf_args'])           
            ){
            foreach ($this->options['printf_args'] as $i=>&$arg) {
                $t_arg = explode(":",$arg);
                if (count($t_arg) > 1) {
                    $arg_walker[$i] = $t_arg;
                    $arg = $t_arg[0];
                }
            }
            unset($arg);
            $dispDatas  = I2CE_FormStorage::listDisplayFields(
                $this->options['form'],
                $this->options['printf_args'],
                $this->primaryObject->getNameId(),
                $this->options['where'],
                $this->options['orders'],
                $this->options['limit']);
            $ids = array_keys($dispDatas);
        } else {
            $ids  = I2CE_FormStorage::search(
                $this->options['form'],
                $this->primaryObject->getNameId(),
                $this->options['where'],
                $this->options['orders'],
                $this->options['limit']);
        }
        if ( count ($ids)==0) {
            return false;
        }

        foreach ($ids as $id) {
            $text = $this->options['title']; //default text for child in case printf wasn't set.   
            if (array_key_exists($id,$dispDatas)
                && is_array($dispDatas)
                ){
                $dispFields = $dispDatas[$id];
                foreach($this->options['printf_args'] as $i=>$arg) {
                    $dispData[$i] = $dispFields[$arg];
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
            } 
            $this->generateAjaxLink($id,$text,$tbodyNode);
        }

    }



    
    protected function generateAjaxLink($id,$text,$tbodyNode) {
        $form = $this->options['form'];

        $tbodyNode->appendChild($trNode  = $this->template->createElement('tr',array('colspan'=>2)));
        $trNode->appendChild($tdNode  = $this->template->createElement('td',array('colspan'=>2)));

        if ($this->options['do_ajax'] && $this->options['link']) {
            $url = 'index.php/' . $this->options['link'] . $form . '|' . $id;
            $attrs = array(
                'href'=> $url
                );
            $content_id = 'content-' . $form . '-' . $id;
            if ($this->options['link_filter']) {
                $js = 'ajaxLoadDiv(this,"' . $content_id .  '","' . $url . '","' . $this->options['link_filter'] . '");'; 
            } else {
                $js = 'ajaxLoadDiv(this,"' . $content_id .  '","' . $url . '");'; 
            }
            $this->template->addHeaderLink('mootools-core.js');
            $this->template->addHeaderLink('mootools-more.js');
            $this->template->addHeaderLink('view.js');
		
            $tdNode->appendChild($panelNode = $this->template->createElement('div',array('class'=>'panel panel-default')));
            $panelNode->appendChild($panelHNode = $this->template->createElement('div',array('class'=>'panel-heading')));
            $panelHNode->appendChild ($this->template->createElement('a',array('onClick'=>$js,'class'=>'clicker_hide')));
            $panelHNode->appendChild ($this->template->createElement('a',$attrs,$text));
            $panelNode->appendChild($panelCNode = $this->template->createElement('div',
                                                                                 array('class'=>'panel-heading', 
                                                                                       'id'=>$content_id,
                                                                                       'style'=>'display:none'
                                                                                     )));
            //$tdNode->appendChild($this->template->createElement('a',$attrs,$text));
        } else if (
            ($childObj = I2CE_FormFactory::instance()->createContainer(array($form,$id))) instanceof I2CE_Form
            && ($tdNode->appendChild($appendNode = $this->template->createElement('span',array('name'=>"child:$form|$id")))) instanceof DOMNode
//	    &&  ($childNode = $this->template->appendFileByNode($this->options['template_child'], 'div',  $appendNode)) instanceof DOMNode
            ) {
            $childObj->populate();
            $options = $this->options;
            $options['auto_add_new_child_link'] =false;
            $options['template'] = $this->options['template_child'];
            if (array_key_exists('auto_add_new',$options['action_links'])) {
                unset($options['action_links']['auto_add_new']);
            }
            $gizmo = new I2CE_Gizmo_FormView($this->page,$childObj,$options);
            $appendNode->setAttribute("name",$childObj->getNameID());
            I2CE::raiseError("A0");
            $gizmo->generate($appendNode);
            I2CE::raiseError("A1");
        } else {
            $tdNode->appendChild($this->template->createElement('span',array('name'=>"child:$form|$id"),$text));
        }
    }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
