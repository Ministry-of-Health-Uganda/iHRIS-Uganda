<?php
/**
* © Copyright 2007 IntraHealth International, Inc.
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
*/
/**
*  iHRIS_PageFormParent
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @copyright Copyright &copy; 2007 IntraHealth International, Inc. 
* This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
* the terms of the GNU General Public License as published by the Free Software Foundation; either 
* version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
* that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
* received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
* @version 2.1
* @access public
*/


class I2CE_PageFormAuto extends I2CE_PageForm{

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

    public function __get($object) {
        if (! ($primaryFormName = $this->getPrimaryFormName())) {
            return null;
        }
        if ($object == 'form_name') {
            return $primaryFormName;
        } else if ($object == $primaryFormName) {
            return $this->primaryObject;
        } else if ($object == 'id' && $this->primaryObject instanceof I2CE_Form) {
            return $this->primaryObject->getFormID();
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



    /**
     * Return the form name for this page.
     * 
     * It will be used for the default form template and php page for the form submission.
     * @param boolean $html Set to true if this is to be used for the html template page to load.
     * @return string
     */
    protected function getForm( $html=false ) { 
        return $this->getPrimaryFormName();
    }



    /**
     * Checks to see if there are any permissions in the page's args for the given action.
     * If so, it evaluates them.  If not returns true.
     * @returns boolean
     */
    protected function checkActionPermission($action) {
        if (!($primaryFormName = $this->getPrimaryFormName())) {
            return false; //weirdness.  should just stop whatever is happening
        }
        if (!parent::checkActionPermission($action)) {
            return false;
        }
        $task =   $primaryFormName . "_can_" . $action;
        return $this->hasPermission("task($task)");
    }


                
    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the list object and if this is a form submission load
     * the data from the form data.  It determines the type based on the
     * {@link $type} member variable.
     */
    protected function loadObjects() {
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
        if ($this->isGet() && $this->loadGet()) {
            $primaryObject->load($this->get());
        }
        $this->primaryObject = $primaryObject;
        $this->setObject($primaryObject, I2CE_PageForm::EDIT_PRIMARY, null, true);
        return true;
    }
        

    protected function loadGet() {
        return  true;
    }


    protected function getBaseTemplate() {
        return "auto_edit_form.html";
    }


        
    /**
     * Load the HTML template files for editing.
     */
    protected function loadHTMLTemplates() {
        if (array_key_exists('auto_template',$this->args) && is_array($this->args['auto_template'])
            && ! (array_key_exists('disabled',$this->args['auto_template']) && $this->args['auto_template']['disabled'])) {
            $append_node = 'siteContent';
            if (array_key_exists('append_node',$this->args['auto_template']) && is_scalar($this->args['auto_template']['append_node']) && $this->args['auto_template']['append_node']) {
                $append_node = $this->args['auto_template']['append_node'];
            }
	    if (! ($node = $this->template->appendFileById($this->getBaseTemplate(), 'div', $append_node )) instanceof DOMNode
		) {
		I2CE::raiseError("Could not load template:" . $this->getBaseTemplate());
		return false;
	    }

            $options = $this->args['auto_template'];
            $options['is_edit'] = true;
            $gizmo = new I2CE_Gizmo_Form($this,$this->primaryObject,$options);
            $gizmo->generate($node);
            return true;
        } else {
            return  parent::loadHTMLTemplates();
        }
    }


    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited and return to the view page.
     * @global array
     */
    protected function save() {
        $saved = parent::save();
        if ($saved !== false) {
            $message = "This record has been saved.";
            I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/" .   $this->getPrimaryFormName() .  "_save" );
        } else {
            $message = "This record has not been saved.";
            I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/".   $this->getPrimaryFormName() . "_not_save" );            
        }
        $this->userMessage($message);
        $this->setRedirect(  $this->getViewLink());
        return $saved;
    }

    protected function  getViewLink() {
        if (array_key_exists('view_link',$this->args) && is_scalar($this->args['view_link']) && $this->args['view_link']) {
            return $this->args['view_link'] . $this->getPrimary()->getNameID();
        } else {
            return "view_" . $this->getPrimaryFormName() . "?id=" . $this->getPrimary()->getNameID();
        }
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
            //$return_node->setAttribute('name','id');
            $return_node->setAttribute('href',$this->getReturnLink($this->getPrimary()->getID() != '0'));
        }
    }

    
    protected function  getReturnLink($append_id) {
        if (array_key_exists('view_link',$this->args) && is_scalar($this->args['view_link']) && $this->args['view_link']) {
            $link=  $this->args['view_link'];
        } else {
            $link = "view_" . $this->getPrimaryFormName() . "?id=";
        }
        if ($append_id) {
            $link .=  $this->getPrimary()->getNameID();
        }
        return $link;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
