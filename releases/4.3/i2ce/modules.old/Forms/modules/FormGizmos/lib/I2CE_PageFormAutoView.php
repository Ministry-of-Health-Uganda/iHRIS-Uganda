<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
 * View the details for then given record that is an instance of a I2CE_List.
 * @package I2CE
 * @subpackage Common
 * @access public
 * @author Carl Leitner
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying a I2CE_List record.
 * @package I2CE
 * @subpackage Common
 * @access public
 */

class I2CE_PageFormAutoView extends I2CE_PageForm {


    protected function getAction() {
	if ( count($this->request_remainder) > 0) {
	    return $this->request_remainder[0];  //view, edit etc..
	} else {
	    return 'view';
	}

    }


    protected $primaryObject  = false;
    protected $parentObject = false;


    protected function loadObjects() {
	if (count($this->request_remainder) <2) {
	    //editing or viewing the parent form
	    return $this->loadParentObject();
	} else {
	    if (!array_key_exists('auto_template',$this->args) || !is_array($this->args['auto_template'])) {
		$this->args['auto_template'] = array();
	    }
	    if (!array_key_exists('child_forms',$this->args['auto_template']) || !is_array($this->args['auto_template']['child_forms'])) {
		$this->args['auto_template']['child_forms'] = array();
	    }
	    $request = $this->request_remainder();
	    array_shift($request);	    
	    array_unshift($request,$this->getPrimaryFormName());	    
	    return $this->loadChildObject($request,$this->args['auto_template']['child_forms']);
	}
    }



    protected function loadChildObject($request,$child_forms) {
	if (!is_array($child_forms)) {
	    I2CE::raiseError("No child form data");
	    return false;
	}
	if (count($request) < 2) {
	    $parentFormName = $this->getPrimaryFormName();
	    $childFormName = $request[0];
	} else 	if (count($request) > 2) {
	    array_shift($request);
	    $childFormName = $request[0];
	    if (!array_key_exists($childFormName ,$child_forms) 
		|| !is_array($child_forms[$childFormName])
		|| !array_key_exists('child_forms',$child_forms[$childFormName])
		|| !is_array($child_forms[$childFormName]['child_forms'])
		) {
		return false;
	    }
	    return $this->loadChildObject($request,$child_forms[$childFormName]['child_forms']);
	} else {
	    $parentFormName = $request[0];
	    $childFormName = $request[1];
	}
	if (!array_key_exists($childFormName,$child_forms)) {
	    I2CE::raiseError("Invalid child $childFormName:" . implode(",",array_keys($child_forms)));
	    return false;
	}
        if ( !($childObjectClass = $this->factory->getClassName($childFormName))) {
            I2CE::raiseError("No object class associated to $childFormName");
            return false;
        }
        if ($this->isPost()) {
            $childObject = $this->factory->createContainer($childFormName);
            if (!$childObject instanceof $childObjectClass) {
                return false;
            }
            $childObject->load($this->post);
        } else {
	    if ($this->get_exists('id')) {
                $id = $this->get('id');
                if (strpos($id,'|')=== false) {
                    I2CE::raiseError("Deprecated use of id variable");
                    $id = $childFormName . '|' . $id;
                }
	    } else {
		$id = $childFormName . '|0';
            }
            $childObject = $this->factory->createContainer($id);
            if (!$childObject instanceof $childObjectClass || $childObject->getName() != $childFormName) {
                I2CE::raiseError("Could not create valid " . $childFormName . "form from id:$id");
                return false;
            }
            $childObject->populate();
        }
        if ($this->isGet() ) {
            $childObject->load($this->get());
        }
	
	if ($this->get_exists('parent')) {
	    $parentID = $this->get('parent');
	} else {
	    $parentID = $childObject->getParent();	    
	}
	$parentObject = $this->factory->createContainer($parentID);
	if (!$parentObject instanceof I2CE_Form
	    || !($parentObject->getName() == $parentFormName)
	    ||$parentObject->getID() =='0'
	    ) {
	    I2CE::raiseError("Invalid parent");
	    return false;
	}
	$parentObject->populate();
	$childObject->setParent($parentObject->getNameID()); //just in case someone tried to spoof the get/post request to edit another parent form
        $this->setObject($childObject, I2CE_PageForm::EDIT_PRIMARY);
        $this->setObject($parentObject, I2CE_PageForm::EDIT_PARENT);
        return true;
    }

    

    protected function loadParentObject() {
	if (! ($primaryFormName = $this->getPrimaryFormName())) {
            return false;
        }
        if ( !($primaryObjectClass = $this->factory->getClassName($primaryFormName))) {
            I2CE::raiseError("No object class associated to $primaryFormName");
            return false;
        }
        if ($this->isPost()) {
            $primaryObject = $this->factory->createContainer($primaryFormName);
            if (!$primaryObject instanceof $primaryObjectClass) {
                return false;
            }
            $primaryObject->load($this->post);
        } else {
            if ($this->get_exists('id')) {
                $id = $this->get('id');
                if (strpos($id,'|')=== false) {
                    I2CE::raiseError("Deprecated use of id variable");
                    $id = $primaryFormName . '|' . $id;
                }
            } else {
                $id = $primaryFormName . '|0';
            }
            $primaryObject = $this->factory->createContainer($id);
            if (!$primaryObject instanceof $primaryObjectClass || $primaryObject->getName() != $primaryFormName) {
                I2CE::raiseError("Could not create valid " . $primaryFormName . "form from id:$id");
                return false;
            }
            $primaryObject->populate();
        }
        if ($this->isGet() ) {
            $primaryObject->load($this->get());
        }
        $this->setObject($primaryObject, I2CE_PageForm::EDIT_PRIMARY);
        return true;
    }


    protected function getChildForm() {
	if (count($this->request_remainder) <2) {
	    return false;
	}
	end($this->request_remainder);
	return current($this->request_remainder);
    }
    

    protected function getPrimaryFormName() {
        if (!array_key_exists('primary_form',$this->args) 
            || !is_scalar($this->args['primary_form']) 
            || ! $this->args['primary_form']) {
            I2CE::raiseError("No primary form set");
            return false;
        }
        return $this->args['primary_form'];
    }

    protected function loadHTMLTemplates() {
	$templateConfig = array();

	if ($this->module =='I2CE') {
	    $url = $this->page;
	} else {
	    $url = $this->module . '/' . $this->page;
	}
	if (!array_key_exists('auto_template', $this->args)
	    || !is_array($this->args['auto_template'])) {
	    $this->args['auto_template'] = array();
	}
	$request_remainder = $this->request_remainder;
	//shift off the action (edit, view)
	array_shift($request_remainder);
	$form = $this->getPrimaryFormName();
	$templateConfig = $this->args['auto_template'];
	$error = false;
	$nav_links =array();
	$p_forms = array();
	while (count($request_remainder) > 0) {
	    $p_forms[] = $form;
	    $form = $request_remainder[0];
	    if (!array_key_exists('child_forms',$templateConfig)
		|| !is_array($templateConfig['child_forms'])
		|| !array_key_exists($form,$templateConfig['child_forms'])
		|| !is_array($templateConfig['child_forms'][$form])		   
		) {
		$error = true;
		break;
	    }
	    $templateConfig = $templateConfig['child_forms'][$form];		   
	    if (($p_formObj = $this->factory->createContainer(end($p_forms))) instanceof I2CE_Form) {
		$view_parent_text = 'View %s';
		I2CE::getConfig()->setIfIsSet($view_parent_text,"/modules/form-gizmos/messages/view_parent");
		$nav_links['view_parent_' . end($p_forms)] = array(
		    'text'=> @sprintf($view_parent_text,$p_formObj->getDisplayName()),
		    'formfield'=> end($p_forms) . ':id',
		    'href'=> $url . '/view/'. implode('/',array_slice($p_forms,1)) . '?id='
		    );
	    }
	    array_shift($request_remainder);
	}
	$templateConfig['form'] = $form;
	if (!array_key_exists('navigation_links',$templateConfig)
	    || !is_array($templateConfig['navigation_links'])
	    ) {
	    $templateConfig['navigation_links'] = array();
	}
	foreach ($nav_links as $link=>$data) {
	    if (array_key_exists($link,$templateConfig['navigation_links'])
		&&is_array($templateConfig['navigation_links'][$link])) {
		continue;
	    }
	    $templateConfig['navigation_links'][$link] = $data;
	}
	if ($error) {
	    I2CE::raiseError("Could not find details for: " . $form);
	    return false;
	}
	//die("MM");
	$templateConfig['base_url'] = $url;
	$request_remainder = $this->request_remainder;
	array_shift($request_remainder);
	$templateConfig['append_url'] = implode('/',$request_remainder);
	if (!($node = $this->template->getElementById('siteContent')) instanceof DOMNode) {
	    return false;
	}
	$action = $this->getAction();
	$templateConfig['is_edit'] = ($action == 'edit');
	if (substr($action ,0,strlen('view_container_')) == 'view_container_') {
	    $templateConfig['is_container'] = substr($action,strlen('view_container_'));
	}

	$gizmo = new I2CE_Gizmo_FormView($this,$this->getPrimary(),$templateConfig);
	$gizmo->generate($node);
	//now make sure all the parent objects are set on the 
	$p_forms = array_reverse($p_forms);
	$obj = $this->getPrimary();
	foreach($p_forms as $p_form) {	    
	    if ( $p_form !=   $obj->getParentForm()
		 || ! ($p_obj = $this->factory->createContainer(array($obj->getParentForm(),$obj->getParentID()))) instanceof I2CE_Form
		) {
		break;
	    }
	    $p_obj->populate();
	    $this->template->setForm($p_obj,$node);
	    $obj = $p_obj;
	}
	$this->template->addHeaderLink('I2CE_DropdownMenu.js');
	$this->template->addHeaderLink('ihris_menu.js');

	$js = 'window.addEvent("domready",  function() {  

    $$(".clickslider").each(function(e) {new ClickSlider(e); });
    $$(".ajaxlink").each(function(e) {  new AjaxLink(e);});
    $$(".dropdown").each(function(e) {  new I2CE_DropdownMenu(e); });
    var error = document.id("error_message"); 
    if (error && error.get("html").replace(/^\s+|\s+$/, "").length == 0) { error.dispose();}
});
';
	$this->template->addHeaderText($js,'script','dropdown-js');


	return true;

    }


    protected function setDisplayData() {
	if ( ($obj = $this->getPrimary()) instanceof I2CE_Form) {
	    $this->template->setDisplayData('id',$obj->getNameID());
	}
	if ( ($obj = $this->getParent()) instanceof I2CE_Form) {
	    $this->template->setDisplayData('parent',$obj->getNameID());
	}	 

    }



    protected function action_display() {
	if ($this->getAction() == 'edit') {
	    parent::action_display();
	} else {
	    if (!$this->getPrimary() instanceof I2CE_Form) {
		I2CE::raiseError("No primary object");
		return false;
	    }
	} 
	return true;
    }


     /**
     * Display the save or confirm buttons as needed.
     * 
     * If the page is a confirmation view then the save / edit button template will be displayed.  
     * Otherwise the confirm and return buttons will be shown.
     * @param boolean $save Flag to show the save button. (Defaults to false)
     * @param boolean $show_edit (defaults to true)
     * @global array
     */
    protected  function displayControls( $save = false, $show_edit = true ) {
        parent::displayControls($save,$show_edit);
        if ( ($return_node = $this->template->getElementByID('button_return')) instanceof DOMElement) {
            $return_node->setAttribute('href',$this->getReturnLink());
        }
    }


    protected function action() {
	switch($this->getAction()) {
	case 'xml':
	    header("Content-type: text/xml");
	    $children = ($this->request_exists('children') && $this->request('children'));
	    if ( ! ($obj = $this->getPrimary()) instanceof I2CE_Form
		) {
		I2CE::raiseError("Bad Object");
		return false;
	    }
	    $out = $obj->getXMLRepresentation(false,null,false,$children);
	    if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
		I2CE::raiseError("Got errors:\n$errors");
	    }	    
	    header("Content-disposition: attachment; filename=\"" . $obj->getName() . "_" . $obj->getID() . ".xml");
	    echo $out;
	    exit(0);
	case 'json':
	    header("Content-type: application/json");
	    $children = ($this->request_exists('children') && $this->request('children'));
	    if ( ! ($obj = $this->getPrimary()) instanceof I2CE_Form
		) {
		I2CE::raiseError("Bad Object");
		return false;
	    }
	    $out = json_encode(simplexml_load_string($obj->getXMLRepresentation(false,null,false,$children)));
	    if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
		I2CE::raiseError("Got errors:\n$errors");
	    }	    
	    echo $out;
	    exit(0);
	case 'json_small':
	    header("Content-type: application/json");
	    if ( ! ($obj = $this->getPrimary()) instanceof I2CE_Form
		) {
		I2CE::raiseError("Bad Object");
		return false;
	    }
	    if ($this->isPost()) {
		$vals = json_decode($in = file_get_contents('php://input'),true);
		$out = "{'success':0}";
		if (is_array($vals)) {
		    if (array_key_exists('id',$vals)) {
			$obj->setID($vals['id']);
			$obj->populate();
		    }
		    $ok_fields = $obj->getFieldNames();
		    $ok_fields[] = 'parent';
		    foreach ($vals as $field=>$data) {
			if (!is_string($data)
			    || ! in_array($field,$ok_fields)
			    || ! ($fieldObj = $obj->getField($field)) instanceof I2CE_FormField
			    ){
			    continue;
			}
			$fieldObj->setFromDB($data);
		    }
		    I2CE::raiseError("JSON Save:" . $obj->getNameID());
		    $obj->save($this->user);
		    $out = "{'success':1}";
		}
	    } else {
		$vals = array();
		$fields  = $obj->getFieldNames();
		$fields = array_merge($fields,array('id','parent','created','last_modified'));
		foreach ( $fields as $field) {
		    if (! ($fieldObj=$obj->getField($field)) instanceof I2CE_FormFIeld) {
			continue;
		    }
		    $vals[$field ] = $fieldObj->getDBValue();
		}
		$out = json_encode($vals);
	    }
	    if ( ($errors  = I2CE_Dumper::cleanlyEndOutputBuffers())) {
		I2CE::raiseError("Got errors:\n$errors");
	    }	    
	    echo $out;
	    exit(0);	    
	default:
	    return parent::action();
	}
    }


    protected function save() {
	if ( ! (parent::save())) {
	    return false;
	}
	$this->setRedirect($this->getReturnLink(true));
    }

    protected function  getReturnLink($drop_last=false) {
	if ($this->module =='I2CE') {
	    $url = $this->page;
	} else {
	    $url = $this->module . '/' . $this->page;
	}
	$url .= '/view';
	$request_remainder = $this->request_remainder;
	array_shift($request_remainder);
	$is_primary = (count($request_remainder) == 0);
	if($drop_last) {
	    array_pop($request_remainder);
	    if ($is_primary) {
		$obj = $this->getPrimary();
	    } else {
		$obj = $this->getParent();
	    }
	} else {
	    if (count($request_remainder) <1) {
		$obj = $this->getPrimary();
	    } else {
		//doing a child form
		$obj = $this->getParent();
	    }	 
	}
	$url .=  "/" . implode('/',$request_remainder);
	if ($obj instanceof I2CE_Form) {
	    $url .=  '?id=' . $obj->getNameID();
	}
        return $url;
    }


    public function getButtons() {
        return array(
            'button_save'=> 'button_save.html',
            'button_save_only'=> 'button_save_only.html',
            'button_save_return'=>'auto_button_save_return.html',
            'button_confirm'=> 'auto_button_confirm.html' ,
            'button_return_only'=> 'auto_button_return_only.html'
            );
    }



    
    






}


