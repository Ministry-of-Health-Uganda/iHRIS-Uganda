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


class I2CE_PageFormParent extends I2CE_PageForm{

    protected $primaryObject  = null;
    protected $parentObject  = null;

    protected function getParentFormName() {
        if (!array_key_exists('page_parent_form',$this->args) 
            || !is_scalar($this->args['page_parent_form']) 
            || ! $this->args['page_parent_form']) {
            I2CE::raiseError("No parent form set");
            return false;
        }
        return $this->args['page_parent_form'];
    }
    protected function getPrimaryFormName() {
        if (!array_key_exists('page_form',$this->args) 
            || !is_scalar($this->args['page_form']) 
            || ! $this->args['page_form']) {
            I2CE::raiseError("No primary form set");
            return false;
        }
        return $this->args['page_form'];
    }

    public function __get($object) {
        if (! ($primaryFormName = $this->getPrimaryFormName())) {
            return null;
        }
        if (! ($parentFormName = $this->getPrimaryFormName())) {
            return null;
        }
        if ($object == 'form_name') {
            return $primaryFormName;
        } else if ($object == 'parent_form_name') {
            return $parentFormName;
        } else if ($object == $primaryFormName) {
            return $this->primaryObject;
        } else  if ($object == $parentFormName) {
            return $this->parentObject;
        } else if ($object == 'id' && $this->primaryObject instanceof I2CE_Form) {
            return $this->primaryObject->getFormID();
        } else if ($object == 'parent_id' && $this->parentObject instanceof I2CE_Form) {
            return $this->parentObject->getFormID();
        } else {
            return null;
        }
    }

    //magic methods so that we can easily reference the primaty form object in a subclass as, for example, $this->person 
    public function __unset($object) {
        if (! ($primaryFormName = $this->getPrimaryFormName())) {
            return ;
        }
        if (! ($parentFormName = $this->getPrimaryFormName())) {
            return ;
        }

        if ($object == $primaryFormName) {
            unset($this->primaryObject);
        } else if ($object = $parentFormName) {
            unset($this->parentObject);
        }
    }


    public function __set($object,$value) {
        if (! ($primaryFormName = $this->getPrimaryFormName())) {
            return ;
        }
        if (! ($parentFormName = $this->getPrimaryFormName())) {
            return ;
        }
        if ($object == $primaryFormName) {
            $this->primaryObject = $value;
        } else if ($object = $parentFormName) {
            $this->parentObject = $value;
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
     * The link used to access this form
     * $var protected string $form_link
     */
    protected $form_link = null;
        
    /**
     * Sets the form link 
     * @param string $link
     */
    public function setFormLink($link) {
        $this->form_link = $link;
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
        if (!($parentFormName = $this->getParentFormName())) {
            return false; //weirdness.  should just stop whatever is happening
        }
        if (!parent::checkActionPermission($action)) {
            return false;
        }
        $task =   $parentFormName . "_can_" . $action . "_child_form_" . $primaryFormName;
        return $this->hasPermission("task($task)");
    }


    /**
     * Creates and populates the parent (person) object on request variables
     * and sets the form in the page for use by any permission checking methods.
     * @return iHRIS_Person
     */
    protected function loadParentObject( $parent = null ) {
        if (! ($parentFormName = $this->getParentFormName())) {
            return null;
        }
        if ( !($parentObjectClass = $this->factory->getClassName($parentFormName))) {
            I2CE::raiseError("No object class associated to $parentFormName");
            return false;
        }
        if ( !$parent && $this->request_exists( 'parent' ) ) {
            $parent = $this->request( 'parent' );
        }
        if ( strpos( $parent, '|' ) === false ) {
            I2CE::raiseError( "Deprecated use of parent variable:" . $parent);
            $parent = $parentFormName . "|" . $parent;
        }
        $parentObject = $this->factory->createContainer( $parent );
        if ( !$parentObject instanceof $parentObjectClass) {
            I2CE::raiseError( "Could not create $parentFormName from " . $parent );
            return null;
        }
        $parentObject->populate();
        //$this->template->setForm( $parentObject );
        return $parentObject;
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
        if (! ($parentFormName = $this->getParentFormName())) {
            return false;
        }
        if ( !($parentObjectClass = $this->factory->getClassName($parentFormName))) {
            I2CE::raiseError("No object class associated to $parentFormName");
            return false;
        }

        if ($this->isPost()) {
            $primaryObject = $this->factory->createContainer($primaryFormName);
            if (!$primaryObject instanceof $primaryObjectClass) {
                return false;
            }
            $primaryObject->load($this->post);
        } elseif ( $this->get_exists('id') ) {
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
                I2CE::raiseError("Could not create valid " . $primaryFornName . "form from id:$id");
                return false;
            }
            $primaryObject->populate();
        } elseif ( $this->get_exists('parent') ) {
            $primaryObject = $this->factory->createContainer($primaryFormName);
            if (!$primaryObject instanceof $primaryObjectClass) {
                return;
            }
            $parent = $this->get('parent');
            if (strpos($parent,'|')=== false) {
                I2CE::raiseError("Deprecated use of parent variable");
                $parent =  $parentFormName . '|' . $parent;            
            }
            $primaryObject->setParent($parent);
        } else {
            I2CE::raiseError("No valid form details");
            return false;
        }
        
        if ($this->isGet()) {
            $primaryObject->load($this->get());
        }
        $parentObject = $this->loadParentObject(  $primaryObject->getParent() );
        if (!$parentObject instanceof $parentObjectClass) {
            I2CE::raiseError("Could not create parent form from " . $primaryObject->getParent());
            return;
        }
        $this->primaryObject = $primaryObject;
        $this->parentObject = $parentObject;
        $this->setObject($primaryObject, I2CE_PageForm::EDIT_PRIMARY, null, true);
        $this->setObject($parentObject, I2CE_PageForm::EDIT_PARENT, null, true);        
        return true;
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
            if (! ($node = $this->template->getElementById(  $append_node )) instanceof DOMNode) {
                I2CE::raiseError("Could not find add template to $append_node");
                return false;
            }
            $options = $this->args['auto_template'];
            $options['is_edit'] = true;
            $options['parent_obj'] = $this->parentObj;
            $gizmo = new I2CE_Gizmo_FormParent($this,$this->primaryObject,$options);
            $gizmo->generate($node);
            return true;
        } else {
            parent::loadHTMLTemplates();
            $this->template->appendFileById( "form_" . $this->getForm(true) . ".html", "tbody", $this->getParentFormName() . "_form" );
            return true;
        }
    }


    /**
     * Set the data to be displayed for the page.
     */
    protected function setDisplayData() {
        parent::setDisplayData();
        $this->template->setDisplayData( $this->getParentFormName() . "_header", $this->getTitle() );
        if ( !($form_link = $this->form_link)) {
            if ($this->module == 'I2CE') {
                $form_link  = $this->page;
            } else {
                $form_link  = $this->module .'/' . $this->page;
            }
        }
        $this->template->setDisplayData( $this->getParentFormName() . "_form", $form_link);
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
            I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/"   . $this->getParentFormName() .  "_save" );
        } else {
            $message = "This record has not been saved.";
            I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/"  . $this->getParentFormName() . "_not_save" );            
        }
        $this->userMessage($message);
        $this->setRedirect(  $this->getParentLink(true));
        return $saved;
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
        if ( ($node = $this->template->getElementByID('button_return')) instanceof DOMElement) {
            $node->setAttribute('href',$this->getParentLink(false));
        }
    }

    protected function  getParentLink($append_id) {
        if (array_key_exists('parent_view_link',$this->args) && is_scalar($this->args['parent_view_link']) && $this->args['parent_view_link']) {
            $link=  $this->args['parent_view_link'];
        } else {
            $link = "view_" . $this->getParentFormName() . "?id=";
        }
        if ($append_id) {
            $link .=  $this->getPrimary()->getParent() ;
        }
        return $link;
    }
    protected function  getViewLink() {
        if (array_key_exists('view_link',$this->args) && is_scalar($this->args['view_link']) && $this->args['view_link']) {
            return $this->args['view_link'] . $this->getPrimary()->getNameID();
        } else {
            return "view_" . $this->getPrimaryFormName() . "?id=" . $this->getPrimary()->getNameID();
        }
    }






}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
