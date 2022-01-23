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
	} else if ( 
	    ($md = $this->getRelationshipMD() ) instanceof I2CE_MagicDataNode
	    && ($form = $this->getPrimaryFormName())
	    && is_array($join_forms  = $md->getAsArray())
	    ){
	    //editing or viewing one of the joined(child) forms
	    $request = $this->request_remainder();
	    array_shift($request); //shift off the view/edit or whatever action	    
	    array_unshift($request, $form); //add the primary_form of the relationship to the beginning
	    return $this->loadChildObject($request,$join_forms);
	} else {
	    I2CE::raiseError("Don't know which form");
	    return false;
	}
    }



    protected function loadChildObject($request,$join_forms) {
	if (!is_array($join_forms)
	    || !array_key_exists('joins',$join_forms)
	    || !is_array($join_forms['joins'])
	    || !array_key_exists('form',$join_forms)
	    || !is_scalar($parentFormName = $join_forms['form'])
	    || !in_array($parentFormName,I2CE::getConfig()->getKeys("/modules/forms/forms"))
	    || count($request) < 2) {
	    I2CE::raiseError("No joined form data/invalid request");
	    return false;
	} else if (count($request) > 2) {
	    array_shift($request);
	    $joinFormName = $request[0];
	    if (!array_key_exists($joinFormName ,$join_forms['joins']) 
		|| !is_array($join_forms['join'][$joinFormName])
		) {
		return false;
	    }
	    return $this->loadChildObject($request,$join_forms['joins'][$joinFormName]);
	}
	$joinRepFormName = $request[1];
	if (!array_key_exists($joinRepFormName,$join_forms['joins'])
	    ||!array_key_exists('form',$join_forms['joins'][$joinRepFormName])
	    || !is_scalar($joinFormName = $join_forms['joins'][$joinRepFormName]['form'])
	    || ! in_array($joinFormName,I2CE::getConfig()->getKeys("/module/forms/forms"))
	    || !($joinObjectClass = $this->factory->getClassName($joinFormName))
	    ){
	    I2CE::raiseError("Invalid join $joinRepFormName");
	    return false;
	}
        if ($this->isPost()) {
            if ( ! ($joinObject = $this->factory->createContainer($joinFormName))  instanceof $joinObjectClass) {
		I2CE::raiseError("Could not instantiate $joinFormName");
                return false;
            }
            $joinObject->load($this->post);
        } else {
	    if ($this->get_exists('id')) {
                $id = $this->get('id');
                if (strpos($id,'|')=== false) {
                    I2CE::raiseError("Deprecated use of id variable");
                    $id = $joinFormName . '|' . $id;
                }
	    } else {
		$id = $joinFormName . '|0';
            }
            if (! ($joinObject = $this->factory->createContainer($id)) instanceof $joinObjectClass
		|| ! ( $joinObject->getName() == $joinFormName)
		) {
                I2CE::raiseError("Could not create valid " . $joinFormName . "form from id:$id");
                return false;
            }
            $joinObject->populate();
        }
        if ($this->isGet() ) {
            $joinObject->load($this->get());
        }
	
	///WHAT DO I DO HERE WITH PARENTS __ ONLY JOIN STYLE AS CHILD? ////
	if ($this->get_exists('parent')) {
	    $parentID = $this->get('parent');
	} else {
	    $parentID = $joinObject->getParent();	    
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
	//$joinObject->setParent($parentObject->getNameID());   /// THIS NEEDS TO CHANGE

        $this->setObject($joinObject, I2CE_PageForm::EDIT_PRIMARY);
        $this->setObject($parentObject, I2CE_PageForm::EDIT_SECONDARY);
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



    protected $md = null;

    protected function getRelationshipMD() {
	if ($this->md === null) {
	    $this->md = false;
	    if (array_key_exists('relationship',$this->args)) {
		if (is_scalar($rel = $this->args['relationship'])
		    && I2CE_MagicDataNode::checkKey($rel)
		    ) {
		    $path = "/modules/CustomReports/relationships/"  . $rel;
		} else if (is_array($this->args['relationship'])) {
		    if ($this->module =='I2CE') {
			$path = '/I2CE/page/' . $this->page . '/args/relationship';
		    } else {
			$path = '/modules/' . $this->module . '/page/' . $this->page . '/args/relationship';
		    }
		}
		$md = I2CE::getConfig()->traverse($path,false);
		if (!$md instanceof I2CE_MagicDataNode) {
		    I2CE::raiseError("Invalid relationship data found at $path");
		    return false;
		} else {
		    $this->md = $md;
		}
	    }
	}
	return $this->md;
    }

    protected function getPrimaryFormName() {
	$form = false;
	if (! ($md = $this->getReltaionshipMD())
	    || ! ($md->setIfIsSet($form,'form'))
	    || ! (in_array($form,I2CE::getConfig()->getKeys("/modules/forms/forms")))
	    ) {
	    return false;
	}
	$return $form;
    }

    protected function loadHTMLTemplates() {
	$templateConfig = array();

	if ($this->module =='I2CE') {
	    $url = $this->page;
	} else {
	    $url = $this->module . '/' . $this->page;
	}
	echo "<pre>";
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
	var_export($request_remainder);
	while (count($request_remainder) > 0) {
	    $form = $request_remainder[0];
	    echo "Shifting on " . implode("/" , $request_remainder) . "\n"; print_r($templateConfig);
	    if (!array_key_exists('child_forms',$templateConfig)
		|| !is_array($templateConfig['child_forms'])
		|| !array_key_exists($form,$templateConfig['child_forms'])
		|| !is_array($templateConfig['child_forms'][$form])		   
		) {
		$error = true;
		break;
	    }
	    $templateConfig = $templateConfig['child_forms'][$form];		   
	    array_shift($request_remainder);
	}
	$templateConfig['form'] = $form;
	if ($error) {
	    I2CE::raiseError("Could not find details for: " . $form);
	    return false;
	}
	print_r($templateConfig);
	//die("MM");
	$templateConfig['base_url'] = $url;
	$request_remainder = $this->request_remainder;
	array_shift($request_remainder);
	$templateConfig['append_url'] = implode('/',$request_remainder);
	
	if ($this->getAction() == 'edit') {
	    if (! ($node = $this->template->appendFileById("auto_edit_form.html", 'div', 'siteContent' )) instanceof DOMNode
		) {
		I2CE::raiseError("Could not load auto_edit_form.html");
		return false;
	    }
	    $templateConfig['is_edit'] = true;
	} else {
	    if( ! ($node = $this->template->appendFileById("auto_view_form.html", 'div',  'siteContent')) instanceof DOMNode) {
		I2CE::raiseError("Could not load auto_view_form.html");
		return false;
	    }
	}
	$gizmo = new I2CE_Gizmo_FormView($this,$this->getPrimary(),$templateConfig);
	$gizmo->generate($node);
	return true;

    }


    protected function setDisplayData() {
	if ( ($obj = $this->getPrimary()) instanceof I2CE_Form) {
	    $this->template->setDisplayData('id',$obj->getNameID());
	}
	////
	NOT SURE WHAT TO HERE..  MAYBE ONLY IF JOINED FROM A PARENT FORM
	///
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



    protected function save() {
	if ( parent::save() === false) {
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


