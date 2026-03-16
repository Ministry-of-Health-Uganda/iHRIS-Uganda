<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
 * 
 * This File is part of iHRIS
 * 
 * iHRIS is free software; you can redistribute it and/or modify
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
 */
/**
 * View a person's record.
 * @package iHRIS
 * @subpackage Qualify
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org> / Carl Leitner <cleitner@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying the a form and its children record.
 * @package I2CE
 * @subpackage Form
 * @access public
 */
class I2CE_PageViewChildren extends I2CE_Page{ 
    protected $primaryObject  = null;

    protected function getPrimaryFormName() {
        if (!array_key_exists('primary_form',$this->args) 
            || !is_scalar($this->args['primary_form']) 
            || ! $this->args['primary_form']) {
            I2CE::raiseError("No primary form set");
            return false;
        }
        return $this->args['primary_form'];
    }


    /**
     * Load the  template (HTML or XML) files to the template object.
     */  
    protected function loadHTMLTemplates() {
        if (array_key_exists('auto_template',$this->args) && is_array($this->args['auto_template'])
            && ! (array_key_exists('disabled',$this->args['auto_template']) && $this->args['auto_template']['disabled'])) {
            $append_node = 'siteContent';
            if (array_key_exists('append_node',$this->args['auto_template']) && is_scalar($this->args['auto_template']['append_node']) && $this->args['auto_template']['append_node']) {
                $append_node = $this->args['auto_template']['append_node'];
            }
            $this->generateAutoParentTemplate($this->args['auto_template'],$append_node);
            return true;
        } else {
            return parent::loadHTMLTemplates();
        }
    }


    protected function loadPrimaryObject() {
        if (! ($primaryFormName = $this->getPrimaryFormName())) {
            I2CE::raiseError("No primary form");
            return false;
        }
        $ff  = I2CE_FormFactory::instance();
        if ( !($primaryObjectClass = $ff->getClassName($primaryFormName))) {
            I2CE::raiseError("No object class associated to $primaryFormName");
            return false;
        }
        if ($this->request_exists('id')) {
            $id = $this->request('id');
            if (strpos($id,'|')=== false) {
                I2CE::raiseError("Deprecated use of id variable");
                $id = $primaryFormName . '|' . $id;
            }
        } else {
            $id = $primaryFormName . '|0';
        }
        $factory = I2CE_FormFactory::instance();
        $primaryObject = $factory->createContainer( $id);
        if (!$primaryObject instanceof $primaryObjectClass) {
            return false;
        }
        $primaryObject->populate();
        return $primaryObject;
    }


    /**
     * Initializes any data for the page
     * @returns boolean.  True on sucess. False on failture
     */
    protected function initPage() {
        if ( ! ($primaryFormName = $this->getPrimaryFormName())) {
            I2CE::raiseError("No primary form set");
            return false;
        }
        $ff  = I2CE_FormFactory::instance();
        if ( !($primaryObjectClass = $ff->getClassName($primaryFormName))) {
            I2CE::raiseError("No object class associated to $primaryFormName");
            return false;
        }
        if ( !($this->primaryObject = $this->loadPrimaryObject()) instanceof $primaryObjectClass) {
            I2CE::raiseError("Invalid primary form");
            return false;
        }
        $po_id = $this->primaryObject->getID();
        if ($po_id == '0' || !I2CE_FormFactory::instance()->hasRecord( $primaryFormName, $po_id ) ) {
            $message = "Unable to find that record for %s.";
            I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/invalid_record" );

            $this->userMessage( vsprintf( $message, array( $primaryFormName, $po_id ) ) );
            $this->setRedirect('home');
            return true;
        }
        $this->template->setForm( $this->primaryObject );
        return parent::initPage();
    }
            


    public function __get($object) {
        if (! ($primaryFormName = $this->getPrimaryFormName())) {
            return null;
        }
        if ($object == $primaryFormName) {
            return $this->primaryObject;
        } else {
            return null;
        }
    }

    //magic methods so that we can easily reference the primaty form object in a subclass as, for example, $this->person 
    public function __unset($object) {
        if (! ($primaryFormName = $this->getPrimaryFormName())) {
            return ;
        }
        if ($object == $primaryFormName) {
            unset($this->primaryObject);
        }
    }


    public function __set($object,$value) {
        if (! ($primaryFormName = $this->getPrimaryFormName())) {
            return ;
        }
        if ($object == $primaryFormName) {
            $this->primaryObject = $value;
        }
    }


    protected function getChildFormData() {
        $children = array();
        reset($this->parentObjs);
        if ((! ($topObj = current($this->parentObjs)) instanceof I2CE_Form)
            ||  ($topObj->getName() != $this->getPrimaryFormName())) {
            return $children;
        }
        end($this->parentObjs);
        if (! ($parentFormObj = current($this->parentObjs)) instanceof I2CE_Form) {
            reset ($this->parentObjs);
            return array();
        }
        reset ($this->parentObjs);
        $parentFormName = $parentFormObj->getName();
        $allowed_children = $parentFormObj->getChildForms();

        reset($this->parentObjs);
        next($this->parentObjs);
        $children_key = 'children';
        while ( ($obj = current($this->parentObjs)) != null) {
            if (!$obj instanceof I2CE_Form) {
                I2CE::raiseError("Unexpected object");
                reset($this->parentObjs);
                return $children;
            }
            $children_key .= '_' . $obj->getName();
            next($this->parentObjs);
        }
        reset($this->parentObjs);
        if (array_key_exists($children_key,$this->args) && is_array($this->args[$children_key])) {           
            foreach ($this->args[$children_key] as $child_form => $data) {
                if (!in_array($child_form,$allowed_children)) {
                    continue;
                }
                $children[$child_form] = $data;
            }
        } else {
            foreach ($allowed_children as $child_form) {
                $children[$child_form] = array('method'=>$this->getViewChildMethod($parentFormName,$child_form));
            }
        }
        return $children;
    }

    protected function displayChildForm($child_form,$data) {
        if (!is_array($data)) {
            return false;
        }
        //first we get the children we want.
        $where = array();
        $order= null;
        $tag = 'div';
        $set_on_node = null;
        $template = false;
        $append_node = null;
        $limit =false;
        $type = 'default';
        if (array_key_exists('where',$data) && is_array($data['where'])) {
            $where = $data['where'];
        }
        if (array_key_exists('order',$data) && $data['order']) {
            $order = $data['order'];
        }
        if (array_key_exists('tag',$data) && $data['tag']) {
            $tag = $data['tag'];
        }
        if (array_key_exists('type',$data) && $data['type']) {
            $type = $data['type'];
        }
        if (array_key_exists('append_node',$data) && $data['append_node']) {
            $append_node = $data['append_node'];
        }
        if (!$append_node) {
            $append_node = $child_form;
        }
        if (array_key_exists('template',$data) && $data['template']) {
            $template = $data['template'];
        }
        if (array_key_exists('limit',$data) && $data['limit']) {
            $limit = $data['limit'];
        }
        if (array_key_exists('set_on_node',$data) && $data['set_on_node']) {
            $set_on_node = $data['set_on_node'];
        }
        end($this->parentObjs);
        if (! ($parentObj = current($this->parentObjs)) instanceof I2CE_Form) {
            reset($this->parentObjs);
            return false;
        }        
        reset($this->parentObjs);
        $parentObj->populateChild(  $child_form, $order, $where, $type, $limit );
        if (array_key_exists('href',$data) && is_array($data['href'])) {
            foreach ($data['href'] as $field=>$link) {
                if (!is_scalar($link) || ! $link) {
                    continue;
                }
                foreach ($this->primaryObject->children[$child_form] as $childObj) {   
                    if ( ! ($fieldObj = $childObj->getField($field)) instanceof I2CE_FormField) {
                        continue;
                    }
                    $fieldObj->setHref($link);
                }
            }
        }        
        return $this->appendChildTemplate($child_form,$set_on_node,$template,$tag, $append_node);

    }

    protected function processPrimaryFormData() {
        if (!array_key_exists('primary_form_data',$this->args) || !is_array($this->args['primary_form_data'])) {
            return;
        }
        if (array_key_exists('href',$this->args['primary_form_data']) && is_array($this->args['primary_form_data']['href'])) {
            foreach ($this->args['primary_form_data']['href'] as $field=>$link) {
                if (!is_scalar($link) || ! $link) {
                    continue;
                }
                if ( ! ($fieldObj = $this->primaryObject->getField($field)) instanceof I2CE_FormField) {
                    continue;
                }
                $fieldObj->setHref($link);
            }
        }
    }

    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        if (! ($primaryFormName = $this->getPrimaryFormName())) {
            I2CE::raiseError("No primary form name set");
            return false;
        }
        $task = $primaryFormName . '_can_view';
        if (I2CE_PermissionParser::taskExists($task) && !$this->hasPermission("task($task)")) {
            $this->userMessage("You do not have permission to view this $primaryFormName",true);
            return false;
        }        
        I2CE_ModuleFactory::callHooks( "pre_page_view_" . $primaryFormName, $this ); 
        $this->processPrimaryFormData();
        return $this->action_children($this->primaryObject);
    }

    protected $parentObjs = array();

    public function getParentObjs() {
        return $this->parentObjs;
    }

    public function action_children($parentObj,$node = null) {
        if (!$parentObj instanceof I2CE_Form) {
            return array();
        }
        $this->parentObjs[] =$parentObj;
        $child_forms = $this->getChildFormData();
        $parentFormName = $parentObj->getName();
        $all_children_task = "{$parentFormName}_can_view_child_forms";
        $all_grandchildren_task = $this->primaryObject->getName() . "_can_view_child_forms";
        $perms = array();
        if (I2CE_PermissionParser::taskExists($all_grandchildren_task)) {
            $perms[] = $all_grandchildren_task;
        }
        if (I2CE_PermissionParser::taskExists($all_children_task)) {
            $perms[] = $all_children_task;
        }
        if (count($perms) > 0) {
            $all_children =  (bool) $this->hasPermission("task(" . implode(" " , $perms) . ")");
        } else{
            $all_children = null;
        }
        foreach($child_forms as $form =>$data) {
            if (!is_array($data)) {
                continue;
            }
            $child_task = "{$parentFormName}_can_view_child_form_{$form}";
            if (I2CE_PermissionParser::taskExists($child_task)) {
                if (!$this->hasPermission("task($child_task)")) {
                    continue;
                }
            } else {
                if ($all_children === false) {
                    continue;
                }
            }
            $child_node = $this->template->getElementByID($form,$node);
            if (!$child_node) {
                $child_node = $this->template->createElement('div',array('id'=>$form));
                $this->template->getElementByID('siteContent')->appendChild($child_node);
            }
            if (array_key_exists('method',$data) && is_scalar($data['method']) && $data['method']) {
                $method = $data['method'];
                if ($this->_hasMethod($method)) {
                    if (!$this->$method($child_node)) {
                        I2CE::raiseError("Could not do action for child $form of $parentFormName");
                    }
                }
            } else {
                $data['append_node'] = $child_node;
                if (!  $this->displayChildForm($form,$data)) {
                    I2CE::raiseError("Could not display child form $form from" . print_r($data,true));
                }
            }
        }
        array_pop($this->parentObjs);
        if (count($this->parentObjs) == 0) {
            I2CE_ModuleFactory::callHooks( "post_page_view_{$parentFormName}", $this );
        }
        return true;
    }
    
    protected function getViewChildMethod($parentForm,$childForm) {
        return $parentForm . '_action_' . $childForm;
    }

    protected function getViewChildTemplate($parentForm,$childForm) {
        return $parentForm . '_view_' . $childForm . '.html';
    }


    public function hasChildForm($form, $populate = false) {
        if ($populate) {
            $this->primaryObject->populateChildren($form);
        }
        return (array_key_exists($form,$this->primaryObject->children) && is_array($this->primaryObject->children[$form]) && count($this->primaryObject->children[$form]) > 0);
    }

    public function addChildForms($form, $set_on_node = null , $template = false, $tag = 'div', $append_node = null) {
        $this->primaryObject->populateChildren($form);
        return $this->appendChildTemplate($form,$set_on_node,$template,$tag, $append_node );
    }


    public function addLastChildForm($form, $field,  $set_on_node = null,  $template = false, $tag = 'div', $append_node = null) {
        $this->primaryObject->populateLast(array($form=> $field));
        return $this->appendChildTemplate($form,$set_on_node,$template,$tag, $append_node );
    }



    protected function getChildTemplate($form) {
        return 'view_' . $form . '.html';
    }



    
    protected function addLinks($tag,$data,$containerNode,$has_existing = null, $is_first = null) {
        $added =0;
        foreach ($data as $link_data) {
            if (array_key_exists('task',$link_data) && $link_data['task']) {
                $task = $link_data['task'];
                if (I2CE_PermissionParser::taskExists($task) && !$this->hasPermission("task($task)",$containerNode)) {
                    continue;
                }     
            }
            if (!array_key_exists('href',$link_data) || !$link_data['href']
                ||!array_key_exists('formfield',$link_data) || !$link_data['formfield']
                || !array_key_exists('text',$link_data) || !$link_data['text']
                ) {
                continue;
            }
            if ($has_existing === true && array_key_exists('only_new',$link_data) && $link_data['only_new']) {

            }
            if ($is_first !==null  && !$is_first && array_key_exists('only_first',$link_data) && $link_data['only_first']) {
                continue;
            }

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
            $tagNode = $this->template->createElement($tag);
            $tagNode->appendChild($this->template->createElement('span',$attrs,$link_data['text']));
            $containerNode->appendChild($tagNode);
            $added++;
        }
        return $added; 
    }

    protected $indices = array();

    protected function generateAutoChildTemplate($formObj,$data,$appendNode) {
        $form=$formObj->getName();
        $is_first = null;
        if($formObj->getID() != '0') {
            if (!array_key_exists($form,$this->indices)) {
                $this->indices[$form] = array();
            }
            $this->indices[$form][] = $formObj->getID();
            $is_first = count($this->indices[$form]) == 1;
        }

        $this->template->addHeaderLink('view.js');
        if (array_key_exists('task',$data) && $data['task']) {
            $task = $data['task'];
            if (I2CE_PermissionParser::taskExists($task) && !$this->hasPermission("task($task)",$appendNode)) {
                return false;
            }     
        }
        $node = $this->template->appendFileByNode("auto_view_child_form.html", 'div',  $appendNode );
        if (!$node instanceof DOMNode) {
            I2CE::raiseError("Could not load auto_view_child_form.html");
            return false;
        }
        $added = false;
        if ( $formObj->getID() != '0' && ($ulNode= $this->template->getElementByName('child_form_edit_links',0,$node)) instanceof DOMNode 
             && array_key_exists('edit_links',$data) && is_array($data['edit_links'])
            ) {           
            $added = $this->addLinks('li',$data['edit_links'],$ulNode);
        }
        $this->template->setDisplayData('has_edit_links',$added,$node);
       
        if ( ($pNode= $this->template->getElementByName('child_form_action_links',0,$node)) instanceof DOMNode 
             && array_key_exists('action_links',$data) && is_array($data['action_links'])
            ) {           
            $this->addLinks('span',$data['action_links'],$pNode,$formObj->getID() != '0',$is_first);
        }
        $field_data =false;
        if ( $formObj->getID() != '0' && ($tbodyNode= $this->template->getElementByName('child_form_fields',0,$node)) instanceof DOMNode ) {
            $all_field_names = $formObj->getFieldNames();
            $display_order = array();
            if (array_key_exists('display_order',$data) && is_string($data['display_order'])) {
                $display_order = explode(",",$data['display_order']);
            }
            $field_names = array();
            foreach($display_order as $field_name) {
                if ( ($pos = array_search($field_name,$all_field_names)) === false) {
                    continue;
                }
                unset($display_order[$pos]);
                $field_names[] = $field_name;
            }
            $field_data = array();
            if (array_key_exists('fields',$data) && is_array($data['fields'])) {
                $field_data= $data['fields'];
            }
	    $listed_fields = array_keys($field_data);
            $field_names = array_unique(array_merge($field_names,$all_field_names,$listed_fields));
	    $default_enabled = 1;
	    if (array_key_exists('default_disabled',$data)) {
		$default_enabled = !$data['default_disabled'];
	    }
            foreach ($field_names as $field_name) {
                if (!array_key_exists($field_name,$field_data) || !is_array($field_data[$field_name])) {
                    $f_data = array();
                } else {
                    $f_data = $field_data[$field_name];
                }
                if (array_key_exists('enabled' , $f_data)) {
                    if (!$f_data['enabled']) { 
                        continue;
                    }
                } else {
                    if (!$default_enabled) {
                        continue;
                    }
                }        
		if (array_key_exists('attributes',$f_data) && is_array($f_data['attributes'])) {
		    $attrs = $f_data['attributes'];
		}
		$attrs['type']='form';
		if (array_key_exists('is_method',$f_data) && $f_data['is_method']) {
		    $attrs['name']=$form. '->' . $field_name .'()';
		    $tbodyNode->appendChild($trNode  = $this->template->createElement('tr',array('colspan'=>2)));
		    $trNode->appendChild($tdNode  = $this->template->createElement('td',array('colspan'=>2)));
		    $tdNode->appendChild($this->template->createElement('span',$attrs));
		} else {
		    $attrs['name']=$form. ':' . $field_name;
		    if (!array_key_exists('showhead',$attrs)) {
			$attrs['showhead'] = 'default';
		    }
		    if (!$attrs['showhead']) {
			unset($attrs['showhead']);
		    }
		    $tbodyNode->appendChild($this->template->createElement('span',$attrs));
		}
                $has_field_data =true;
            }
        }
        $this->template->setDisplayData('has_field_data',$added,$node);  //ADD THIS TO OTHER PLACES!!!
        if (array_key_exists('display_name',$data) && $data['display_name']) {
            $display_name = $data['display_name'];
        } else {
            $display_name =  $formObj->getDisplayName();
        }
        $this->setDisplayDataImmediate('child_form_display_name',$display_name,$node);

        if (array_key_exists('title',$data) && $data['title']) {
            $title = $data['title'];
            if (array_key_exists('title_args',$data) && is_array($data['title_args'])) {
                $args = array();
                foreach ($data['title_args'] as $i=>$fieldName) {
                    $val = '';
                    if ( ($fieldObj = $formObj->getField($fieldName)) instanceof I2CE_FormField) {
                        $val =  $fieldObj->getDisplayValue();
                    }
                    $args[$i] = $val;
                }
                ksort($args);
                if (count($args) > 0 ) {
                    $title = @vsprintf($title,$args);
                }
            }
        } else {
            $title =  $formObj->getDisplayName();
        }
        if ($formObj->getID() == '0' || $is_first === true) {
            $this->setDisplayDataImmediate('child_form_title',$title,$node);
        }
        $dataNodeID= 'data_container_' . $formObj->getHTMLName();
        if ( ($dataNode= $this->template->getElementByName('data_container',0,$node)) instanceof DOMNode) {
            $dataNode->setAttribute('id', $dataNodeID);
        }
        if ( ($hideNode= $this->template->getElementByName('hide_data_container',0,$node)) instanceof DOMNode) {
            if (array_key_exists('nohide',$data) && $data['nohide']) {
                $this->template->removeNode($hideNode);
            } else{
                $hideNode->setAttribute('onclick',"return hideDiv('$dataNodeID',this);");
            }
        }

        return $node;
    }



    protected function generateAutoParentTemplate($data,$appendNode ) {
        $form = $this->primaryObject->getName() ;
        if (array_key_exists('task',$data) && $data['task']) {
            $task = $data['task'];
            if (I2CE_PermissionParser::taskExists($task) && !$this->hasPermission("task($task)",$appendNode)) {
                return false;
            }     
        }
        $node = $this->template->appendFileById("auto_view_parent_form.html", 'div',  $appendNode );
        if (!$node instanceof DOMNode) {
            I2CE::raiseError("Could not load auto_view_parent_form.html");
            return false;
        }
        $added = 0;
        if ( ($ulNode= $this->template->getElementByName('parent_form_edit_links',0,$node)) instanceof DOMNode 
             && array_key_exists('edit_links',$data) && is_array($data['edit_links'])
            ) {           
            $added = $this->addLinks('li',$data['edit_links'],$ulNode);            
        }
        $this->template->setDisplayData('has_edit_links',$added,$node);
        if ( ($pNode= $this->template->getElementByName('parent_form_action_links',0,$node)) instanceof DOMNode ) {
            $added =0;
            if ( array_key_exists('action_links',$data) && is_array($data['action_links'])) {
                $added = $this->addLinks('span',$data['action_links'],$pNode);
            }
            if($added == 0) {
                $this->template->removeNode($pNode);
            }
        }
        if ( ($tbodyNode= $this->template->getElementByName('parent_form_fields',0,$node)) instanceof DOMNode ) {
            $all_field_names = $this->primaryObject->getFieldNames();
            $display_order = array();
            if (array_key_exists('display_order',$data) && is_string($data['display_order'])) {
                $display_order = explode(",",$data['display_order']);
            }
            $field_names = array();
            
            foreach($display_order as $field_name) {
                if ( ($pos = array_search($field_name,$all_field_names)) === false) {
                    continue;
                }
                unset($display_order[$pos]);
                $field_names[] = $field_name;
            }
            $field_names = array_unique(array_merge($field_names,$all_field_names));
            $field_data = array();
            if (array_key_exists('fields',$data) && is_array($data['fields'])) {
                $field_data= $data['fields'];
            }
	    $default_enabled = 1;
	    if (array_key_exists('default_disabled',$data)) {
		$default_enabled = !$data['default_disabled'];
	    }    
            $added = false;
            foreach ($field_names as $field_name) {
                if (!array_key_exists($field_name,$field_data) || !is_array($field_data[$field_name])) {
                    $f_data = array();
                } else {
                    $f_data = $field_data[$field_name];
                }
                if (array_key_exists('enabled' , $f_data)) {
                    if (!$f_data['enabled']) { 
                        continue;
                    }
                } else {
                    if (!$default_enabled) {
                        continue;
                    }
                }        
                $attrs = array('type'=>'form','name'=>$form. ':' . $field_name);
                if (array_key_exists('attributes',$f_data) && is_array($f_data['attributes'])) {
                    $attrs = $f_data['attributes'];
                }
                if (!array_key_exists('showhead',$attrs)) {
                    $attrs['showhead'] = 'default';
                }
                if (!$attrs['showhead']) {
                    unset($attrs['showhead']);
                }
                $tbodyNode->appendChild($this->template->createElement('span',$attrs));
                $added = true;
            }
            $this->template->setDisplayData('has_field_data',$added,$node);  
        }
        if (array_key_exists('display_name',$data) && $data['display_name']) {
            $display_name = $data['display_name'];
        } else {
            $display_name =  $this->primaryObject->getDisplayName();
        }
        $this->setDisplayDataImmediate('parent_form_display_name',$display_name,$node);
        if (array_key_exists('title',$data) && $data['title']) {
            $title = $data['title'];
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
                if (count($args) > 0 ) {
                    $title = @vsprintf($title,$args);
                }
            }
        } else {
            $title = $this->getTitle();
        }

        if (array_key_exists('subtitle',$data) && !is_array($data['subtitle'])) {
            $subtitle = $data['subtitle'];
            if (array_key_exists('subtitle_args',$data) && is_array($data['subtitle_args'])) {
                $args = array();
                foreach ($data['subtitle_args'] as $i=>$fieldName) {
                    $val = '';
                    if ( ($fieldObj = $this->primaryObject->getField($fieldName)) instanceof I2CE_FormField) {
                        $val =  $fieldObj->getDisplayValue();
                    }
                    $args[$i] = $val;
                }
                ksort($args);
                if (count($args) > 0 ) {
                    $subtitle = @vsprintf($subtitle,$args);
                }
            }

        } else {
            $subtitle =  $this->primaryObject->getDisplayName();
        }

        $this->setDisplayDataImmediate('parent_form_title',$title,$node);
        $this->setDisplayDataImmediate('parent_form_subtitle',$subtitle,$node);
        return $node;
    }





    public function appendChildTemplate($form,$set_on_node = null, $template = false, $tag = 'div', $appendNode = null) {        
        end($this->parentObjs);
        if ( ! ($parentObject = current($this->parentObjs)) instanceof I2CE_Form) {
            I2CE::raiseError("Unexpected object");
            return false;
        }
        $auto_template =  (array_key_exists('children',$this->args) && is_array($this->args['children'])
                           && array_key_exists($form,$this->args['children']) && is_array($this->args['children'][$form])
                           && array_key_exists('auto_template',$this->args['children'][$form]) && is_array($this->args['children'][$form]['auto_template'])
                           && ! (array_key_exists('disabled',$this->args['children'][$form]['auto_template']) && $this->args['children'][$form]['auto_template']['disabled']));
        if (!$auto_template) {
            if ( !array_key_exists($form,$parentObject->children) || !is_array($parentObject->children[$form])) {
                return true;
            }
            $child_forms =$parentObject->children[$form];
        } else {
            if ( !array_key_exists($form,$parentObject->children) || !is_array($parentObject->children[$form])) {
                $child_forms = array(I2CE_FormFactory::instance()->createContainer($form));
            } else {
                $child_forms =$parentObject->children[$form];
            }

        }
        if ($appendNode === null) {
            $appendNode = $form;
        }
        if (is_string($appendNode)) {
            $appendNode = $this->template->getElementById($appendNode);
        }        
        if (! $appendNode instanceof DOMNode) {
            I2CE::raiseError("Do not know where to add child form $form ");
            return false;
        }
        foreach ($child_forms as $formObj) {
            if ($formObj instanceof I2CE_Form) {
                I2CE_ModuleFactory::callHooks( 'pre_add_child_form_' . $form, 
                                               array( 'form' => $formObj, 
                                                      'page' => $this, 'set_on_node' => $set_on_node, 
                                                      'append_node' => $appendNode ) );
            }
            if (!$auto_template) {
                if ( !$formObj instanceof I2CE_Form) {
                    continue;
                }
                if (!is_string($template) || !$template) {
                    $template = $this->getChildTemplate($form);
                }       
                $node = $this->template->appendFileByNode($template, $tag,  $appendNode );
            }else {
                $node = $this->generateAutoChildTemplate($formObj,$this->args['children'][$form]['auto_template'],$appendNode);
            }
            if (!$node instanceof DOMNode) {
                I2CE::raiseError("Could not find template $template for child form $form of " . $parentObject->getName());
                return false;
            }
            if ($formObj instanceof I2CE_Form) {
                $this->template->setForm($formObj,$node);
                if ($set_on_node !== null) {
                    $this->template->setForm($formObj,$set_on_node);
                }
                I2CE_ModuleFactory::callHooks( 'post_add_child_form_' . $form,
                                               array( 'form' => $formObj, 'node' => $node,
                                                      'page' => $this, 'set_on_node' => $set_on_node,
                                                  'append_node' => $appendNode ) );
                if (!  $this->action_children($formObj,$node)) {
                    I2CE::raiseError("Couldn't display child " . $form);
                }
            }
        }
        return $appendNode;
    }


  }



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
