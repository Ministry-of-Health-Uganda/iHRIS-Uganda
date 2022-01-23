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
* @subpackage FormRelationship
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.1.6.1
* @since v4.1.6.1
* @filesource 
*/ 
/** 
* Class I2CE_PageEditRelationship
* 
* @access public
*/


class I2CE_PageEditRelationship extends I2CE_PageActionRelationship {
    
    protected function getOrderings() {
	$vals = array();
	foreach ($this->args['forms'] as $formName=>$data) {
            $vals[$formName] = $data['ordering'];
	}
	return $vals;
    }
    protected function getFields() {
	$this->setupDefaults();
	$vals = array();
	foreach ($this->args['forms'] as $formName=>$data) {
	    $vals[$formName] = array();
	    foreach($data['fields'] as $field=>$fData) {
                if ($fData['enabled']) {
		    $vals[$formName][] = $field;
		}
	    }
	}
	return $vals;
    }

    protected $set_defaults =false;
    protected $formObjs = array();
    protected function setupDefaults() {
	if ($this->set_defaults) {
	    return;
	}
	if (!array_key_exists('default_enabled',$this->args)) {
	    $this->args['default_enabled'] = 1;
	}
	$default_enabled = $this->args['default_enabled'];
	$fields = array();
	if (!array_key_exists('forms',$this->args) || !is_array($this->args['forms'])) {
	    $this->args['forms'] = array();
	}
	foreach ($this->formRelationship->getFormNames() as $formName) {
	    $form = $this->formRelationship->getForm($formName);
	    if (! ($formObj = $this->ff->createContainer($form)) instanceof I2CE_Form) {
		continue;
	    }
	    $this->formObjs[$formName] = $formObj;
	    if (!array_key_exists($formName,$this->args['forms']) || !is_array($this->args['forms'][$formName])) {
		$this->args['forms'][$formName] = array();
	    }
	    if (!array_key_exists('fields',$this->args['forms'][$formName]) || !is_array($this->args['forms'][$formName]['fields'])) {
		$this->args['forms'][$formName]['fields'] = array();
	    }
	    if (!array_key_exists('ordering',$this->args['forms'][$formName])) {
		$this->args['forms'][$formName]['ordering'] = array();
	    }
	    if (!array_key_exists('display_order',$this->args['forms'][$formName])) {
		$this->args['forms'][$formName]['display_order'] = '';
	    }
	    $f_default_enabled = $default_enabled;
	    if (array_key_exists('default_enabled',$this->args['forms'][$formName])) {
		$f_default_enabled = $this->args['forms'][$formName];
	    } else {
                $this->args['forms'][$formName]['default_enabled']= $default_enabled;
            }
	    foreach ($formObj->getFieldNames() as $field) {
                if (!array_key_exists($field,$this->args['forms'][$formName]['fields']) || !is_array($this->args['forms'][$formName]['fields'][$field])) {
                    $this->args['forms'][$formName]['fields'][$field] = array();
                }
                if (!array_key_exists('enabled',$this->args['forms'][$formName]['fields'][$field])) {
                    if ($field == 'i2ce_hidden' || $field == 'remap' || !$formObj->getField($field)->isInDB()) {
                        $this->args['forms'][$formName]['fields'][$field]['enabled'] = 0;
                    } else {
                        $this->args['forms'][$formName]['fields'][$field]['enabled'] = $f_default_enabled;
                    }
                }
                if (!array_key_exists('attributes',$this->args['forms'][$formName]['fields'][$field]) || !is_array($this->args['forms'][$formName]['fields'][$field]['attributes'])) {
                    $this->args['forms'][$formName]['fields'][$field]['attributes'] = array();
                }

            }
	}

    }

    protected $ff;
    protected function action_main() {
        $this->ff = I2CE_FormFactory::instance();
        $page_rems = preg_split('/\//',$this->pageRemainder(),-1,PREG_SPLIT_NO_EMPTY);
        $action = 'view';
        if (count($page_rems) > 0) {
            $action = array_shift($page_rems);
        }
        if ( ! ($node = $this->template->getElementById('siteContent')) instanceof DOMNode) {
            I2CE::raiseError("No siteContent node");
            return false;
        }
        switch ($action) {
        case 'configure':
            if (!$this->user->getRole() == 'admin') {
                I2CE::raiseError("No permission to configure this page");
                return false;
            }
            return $this->action_configure($page_rems,$node);
        case 'view':
        default:
            if (!$this->primObj instanceof I2CE_Form) {
                I2CE::raiseError("No primary object");
                return false;
            }
            $this->setupDefaults();
            $this->loadData(false);
            $style ='tree';	
            if ($this->request_exists('style') && (is_scalar($this->request('style'))) && $this->request('style')) {
                $style = $this->request('style');
            }
            $method = 'display_'  . $style;
            if ($this->_hasMethod($method)) {
                return $this->$method('primary_form',$this->data,$node);
            }                
            return $this->display_tree('primary_form',$this->data,$node);
        }
    }

    protected function action_configure($path,$node) {
        if (!is_array($path)) {
            $path = array();
        }
        if (count($path) > 0) {
            $md_node = array_shift($path);
            $method = 'action_configure_' . $md_node;
            if ($this->_hasMethod($method)) {
                return $this->$method($path,$node);
            }
        }
        return $this->action_configure_menu($node);
    }


    protected function action_configure_relationship_definition($swiss_path,$node) {

        if ($this->module = 'I2CE') {
            $url_base =  $this->page . "/configure/relationship_definition/";
        } else {
            $url_base = $this->module ."/" . $this->page . "/configure/relationship_definition";
        }
        $init_options = array(
            'root_url'=>$url_base,
            'root_path'=>$this->relConfig->getPath(false),
            'root_path_create'=>false,                    
            'root_type'=>'FormRelationship');        
        try {
            $swiss_factory = new I2CE_SwissMagicFactory($this,$init_options);
        } catch (Exception $e) {
            I2CE::raiseError("Could not create swissmagic for relationship_definition:" . $e->getMessage());
            return false;
        }
        try {
            $swiss_factory->setRootSwiss();
        } catch (Exception $e) {
            I2CE::raiseError("Could not create root swissmagic for relationship_definition:" . $e->getMessage());
            return false;
        }
        $action = array_shift($swiss_path); 
        if (!$action) {
            $action = 'edit';
        }
        if ($action == 'update' && $this->isPost()) {
            if ($this->get('noRedirect')) {
                $redirect = false;
            } else {
                $redirect = true;
            }
            $msgs = array(
                'not_updated'=>'Unable to Update Values',
                'updated'=>'Updated Values');
            foreach ($msgs as $k=>&$v) {
                I2CE::getConfig()->setIfIsSet($v,"/modules/CustomReports/text/user_messages/$k");
            }
            if ( $swiss_factory->updateValues($this->post(),$redirect)) {                        
                $msg = $msgs['updated'];
            } else {
                $msg = $msgs['not_updated'];
            }
            if ($redirect) {
                $this->userMessage($msg, 'notice',true);
                $swiss = $swiss_factory->getSwiss($swiss_path);
                if ($swiss instanceof I2CE_Swiss) { 
                    $redirect = $swiss->getURLRoot('edit') . $swiss->getPath() . $swiss->getURLQueryString();
                } else {
                    $redirect = $url_base;
                }
                $this->setRedirect($redirect);
                return true;
            }
        }
        if ($action == 'update') {
            $action = 'edit';
        }
        return $swiss_factory->displayValues( $node,$swiss_path, $action);
    }
    
    protected function action_configure_menu($node) {
        if (! ($menunode = $this->template->appendFileByNode('auto_view_configure.html','div',$node))instanceof DOMNode) {
            I2CE::raiseError("Cannot load auto_view_configure.html");
            return false;
        }
        if ($this->module = 'I2CE') {
            $url_base = $this->page . "/configure/";
        } else {
            $url_base = $this->module ."/" . $this->page . "/configure/";
        }
        if ($this->has_relationship_def) {           
            $this->template->setDisplayDataImmediate('has_relationship_def',1,$menunode);
            $this->template->setDisplayDataImmediate('edit_relationship_link',$url_base . 'relationship_definition',$menunode);
        } else {
            $this->template->setDisplayDataImmediate('has_relationship_def',0,$menunode);
        }
        return true;
    }
 
    protected $relConfig  =null;
    protected $has_relationship_def = false;
    protected function loadRelationship() {
        $relationship = false;
        $rel_base =false;
        if (array_key_exists('relationship_definition',$this->args) 
            && is_array($this->args['relationship_definition'])
            ) {
            $rel_base = $this->args['relationship_definition'];
            $relationship = 'page_relationship_' . $this->module  . '_' . $this->page;
            $relationship = 'relationship_definition'; 
            $this->has_relationship_def = true;
            if ($this->module == 'I2CE') {
                $rel_base = '/I2CE/page/' . $this->page . '/args/';
            }  else {
                $rel_base = '/modules/'. $this->module . '/page/' . $this->page . '/args/'; 
            }
        } else if (array_key_exists('relationship',$this->args)
                   && is_scalar($this->args['relationship'])) {
            $relationship = $this->args['relationship'];
            $rel_base = '/modules/CustomReports/relationships';
            if (array_key_exists('relationship_base',$this->args)
                && is_scalar($this->args['relationship_base'])) {
                $rel_base = $this->args['relationship_base'];
            }
        }
        if (!I2CE_MagicDataNode::checkKey($relationship)) {
            I2CE::raiseError("Invalid relationship");
            return false;
        }
        
        if ( ! ($this->relConfig = I2CE::getConfig()->traverse($rel_base . '/' . $relationship)) instanceof I2CE_MagicDataNode) {
            I2CE::raiseError("No relationship data at $rel_base/$relationship");
            return false;
        }
        $use_cache = I2CE_ModuleFactory::instance()->isEnabled('CachedForms');
        if (array_key_exists('use_cache',$this->args)) {
            $use_cache = $this->args['use_cache'];
        }
        if ($this->request_exists('use_cache')) {
            $use_cache = $this->request('use_cache');
        }
        if ($use_cache) {
            $cache_callback =array('I2CE_CachedForm','getCachedTableName');
        } else {
            $cache_callback = null;
        }        
        try {            
            $this->formRelationship = new I2CE_FormRelationship($relationship,$rel_base,$cache_callback);
        } catch (Exception $e) {
            I2CE::raiseError("Could not create form relationship : $relationship under $rel_base:\n" . $e->getMessage());
            return false;
        }
        if (array_key_exists('use_display_fields',$this->args) && (!$this->args['use_display_fields'])) {
            $this->formRelationship->useRawFields();
        }
        return true;
    }

    protected function display_tree($form,$data_tree,$node) {
        $this->template->addHeaderLink('view.js');
        $this->template->addHeaderLink('FormRelationship.css');
        if (! ($contNode = $this->template->appendFileByNode('auto_view_tree.html','div',$node))instanceof DOMNode) {
            return false;
        }
        if ($this->module = 'I2CE') {
            $url_base =  $this->page .'/' ;
        } else {
            $url_base = $this->module ."/" . $this->page .'/' ;
        }
        $this->template->setDisplayData('configure_link',$url_base . 'configure');
        return $this->displayDataTree($form,$data_tree,$contNode);
    }

    protected function displayDataTree($form,$data_tree,$node) {
        if ( !is_array($data_tree) 
	     ||!array_key_exists($form,$data_tree) 
	     || !is_array($data_tree[$form]) 
	     || count($data_tree[$form]) == 0
	    ) {
	    //can't do anthing
	    return ;
        }
        $row = 0;
        foreach ($data_tree[$form] as $formid=>$data) {
            $row++;
            $args = $this->args['forms'][$form];
            if (!array_key_exists('fields',$data)) {
                $data['fields']= array();
            }
            if ( ! ($parent_node = $this->displayForm($args,$formid,$data['fields'],$node,$row == 1))) {
                I2CE::raiseError("No parent Node");
                continue;
            }            
            if (!($children_node = $this->template->getElementByName('child_forms',0,$parent_node))) {
                I2CE::raiseError("No Child Nodes");
                continue;
            }
            if (!array_key_exists('joins',$data) || !is_array($data['joins'])) {
                I2CE::raiseError("No joins for $form");
                continue;
            }
            foreach ($this->formRelationship->getChildFormNames($form) as $child_form) {//may want to have a way order to this later
                if (!array_key_exists($child_form,$data['joins'])) {
                    continue;
                }
                $this->displayDataTree($child_form,$data['joins'],$children_node);
            }

        }

    }

    protected function displayForm($args,$formid,$data,$containerNode,$is_first) {
        if (! ($formObj = $this->ff->createContainer($formid))instanceof I2CE_Form) {
            I2CE::raiseError("Cannot create $formid");
            return false;
        }
        $form = $formObj->getName();
        $all_field_names = $formObj->getFieldNames();
        foreach ($all_field_names as $field) {
            if (!array_key_exists($field,$data)) {
                continue;
            }
            if (! ($fieldObj = $formObj->getField($field)) instanceof I2CE_FormField) {
                continue;
            }
            $fieldObj->setFromDB($data[$field]);
        }
        if (! ($node = $this->template->appendFileByNode('auto_view_relationship_form.html','div',$containerNode))instanceof DOMNode) {
            I2CE::raiseError("Cannot load auto_view_relationship_form.hmtl");
            return false;
        }
        $this->template->setForm($formObj,$node);

        if ( !($tbodyNode= $this->template->getElementByName('form_fields',0,$node)) instanceof DOMNode ) {
            I2CE::raiseError("Don't know where to put form fields");
            return false;
        }

        $display_order = array();
        if (array_key_exists('display_order',$args) && is_string($args['display_order'])) {
            $display_order = explode(",",$args['display_order']);
        }
        $field_names = array();
        foreach($display_order as $field_name) {
            if ( ($pos = array_search($field_name,$all_field_names)) === false) {
                continue;
            }
            unset($display_order[$pos]);
            $field_names[] = $field_name;
        }
        $field_data = $args['fields'];
        $listed_fields = array_keys($field_data);
        $field_names = array_unique(array_merge($field_names,$all_field_names,$listed_fields));
        $default_enabled = $args['default_enabled'];
        $added =false;
        foreach ($field_names as $field_name) {
            $f_data = $field_data[$field_name];
            if (!$f_data['enabled']) { 
                continue;
            }
            $attrs = $f_data['attributes'];
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
            $added =true;
        }
        $this->template->setDisplayData('has_field_data',$added,$node);  
        if (array_key_exists('display_name',$args) && $args['display_name']) {
            $display_name = $args['display_name'];
        } else {
            $display_name =  $formObj->getDisplayName();
        }
        $this->template->setDisplayDataImmediate('form_display_name',$display_name,$node);

        if (array_key_exists('title',$args) && $args['title']) {
            $titl = $args['title'];
            if (array_key_exists('title_args',$args) && is_array($args['title_args'])) {
                $args = array();
                foreach ($args['title_args'] as $i=>$fieldName) {
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
            if (array_key_exists('nohide',$args) && $args['nohide']) {
                $this->template->removeNode($hideNode);
            } else{
                $hideNode->setAttribute('onclick',"return hideDiv('$dataNodeID',this);");
            }
        }

        return $node;
        
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
