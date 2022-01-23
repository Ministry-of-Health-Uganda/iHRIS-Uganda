<?php
/**
* © Copyright 2013 IntraHealth International, Inc.
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
* @subpackage Page
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1.6
* @since v4.1.6
* @filesource 
*/ 
/** 
* Class I2CE_PageTabbed
* 
* @access public
*/


class I2CE_PageTabbed extends I2CE_PageViewChildren {

    /**
     * Load the  template (HTML or XML) files to the template object.
     *  
     * 
     */  
    protected function loadHTMLTemplates() {
	if (! ( parent::loadHTMLTemplates())) {
	    return false;
	}
	if ( ! ($this->template->appendFileByNode('tab_container.html','div',$this->template->getElementByID('siteContent')))) {
	    return false;
	}
	return true;
    }

    public function action() {
        $this->template->addHeaderLink('I2CE_AjaxTabPanel.js');
        $this->template->addHeaderLink('tabs.css');
	if (!array_key_exists('tabs',$this->args) || !is_array($this->args['tabs'])) {
	    I2CE::raiseError("No Tabs Defined");
	    return false;
	}
	$tabs = $this->args['tabs'];
	if (!($tabsNode = $this->template->getElementByID('tabs_link'))) {
	    I2CE::raiseError("Don't know where to put tab links");
	    return false;
	}
	if (!($tabContentsNode = $this->template->getElementByID('tabs_content'))) {
	    I2CE::raiseError("Don't know where to put tab content");
	    return false;
	}
	$selected = false;
	if (array_key_exists('selected_tab',$this->args) && is_scalar($this->args['selected_tab']) && $this->args['selected_tab']) {
	    $selected = $this->args['tab'];
	}
	if ($this->request_exists('selected_tab') && is_scalar($this->request('selected_tab')) && $this->request('selected_tab')) {
	    $selected = $this->request('tab');
	}
	$order = array();
	if (array_key_exists('tab_order',$this->args) && is_scalar($this->args['tab_order'])) {
	    $order = explode(",",$this->args['tab_order']);
	}
	$order = array_unique(array_merge($order,array_keys($tabs)));
	foreach ($order as $i=>$tab) {
	    if (!array_key_exists($tab,$tabs) || !is_array($data = $tabs[$tab])
		|| !array_key_exists('href',$data) || !is_scalar($data['href'])
		|| (array_key_exists('task',$data) && is_scalar($data['task']) && ($task = $data['task']) && I2CE_PermissionParser::taskExists($task) 
		    && !$this->hasPermission("task($task)"))
		){       	  
		I2CE::raiseError("Skipping tab $tab as not valid");
		unset($order[$i]);
	    }
	}
	if (count($order) == 0) {
	    I2CE::raiseError("No valid tabs");
	    return false;
	}
	if (!in_array($selected,$order)) {
	    reset($order);
	    $selected = current($order);
	}
	$sources = array();
	$ff = I2CE_FormFactory::instance();
	foreach ($order as $tab) {
	    $data = $tabs[$tab];
	    if (!array_key_exists('content',$data) || !is_scalar($data['content']) || !$data['content']) {
		$data['content'] = 'siteContent';
	    }
	    $title = $tab;
	    if (array_key_exists('title',$data) && is_scalar($data['title']) && $data['title']) {
		$title=$data['title'];
	    }
	    $attrs  = array('class'=>'tab_link','id'=>'tab_link_' . $tab);
	    $tabsNode->appendChild($liNode = $this->template->createElement('li',$attrs,$title));
	    $href = $data['href'];
	    $reqs = array();
	    if (array_key_exists('request_vars',$data) && is_array($data['request_vars'])) {
		$reqs = array();
		foreach ($data['request_vars'] as $tgt_var=>$src_var) {
		    if (!$this->request_exists($src_var)) {
			continue;
		    }
		    $reqs[$tgt_var] = $this->request[$src_var];
		}
	    }
	    if (count($reqs) > 0) {
		$reqs =http_build_query(self::flattenRequestVars($reqs));
		if (strpos($href,'?') !== false) {
		    $href .= '&'  . $reqs;
		} else {
		    $href .= '?' . $reqs;
		}
	    }
	    $attrs = array('class'=>'tab_content','id'=>'tab_content_' . $tab, 'href'=>$href);
	    if (array_key_exists('formfield',$data) && is_scalar($data['formfield']) && $data['formfield']) {
		$attrs['type']='form';
		$attrs['name']=$data['formfield'];
	    }
	    $tabContentsNode->appendChild($contentNode =$this->template->createElement('span',$attrs));
	    $sources[$tab] = '#' . $data['content'] ;
	    $formObj = false;
	    $formID = false;
	    if (array_key_exists('form',$data) && is_scalar($form = $data['form'])) {
		$where = array();
		$order = null;
		if (array_key_exists('where',$data) && is_array($data['where'])) {
		    $where = $data['where'];
		}
		if (array_key_exists('order',$data) && is_scalar($data['order'])) {
		    $order = $data['order'];
		}
		if ( ($formID= I2CE_FormStorage::search($form,$this->primaryObject->getNameID(),$where,$order,1))) {
		    $formObj = $ff->createContainer($form . '|' . $formID);
		}
	    }
	    if ($formObj instanceof I2CE_Form) {
		$formObj->populate();
		$this->template->setForm($formObj,$liNode);
		$this->template->setForm($formObj,$contentNode);
	    }
	}
	$options = json_encode(array('responseFilters'=>$sources));
	$js = 'document.addEvent("domready", function() {
	      var tab = new I2CE_AjaxTabPanel("tab_panel",'.$options . ');
              if (tab) {; tab.showTab("' . addslashes($selected) . '")};
});';
        $this->template->addHeaderText($js,'script','create_tabs');

    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
