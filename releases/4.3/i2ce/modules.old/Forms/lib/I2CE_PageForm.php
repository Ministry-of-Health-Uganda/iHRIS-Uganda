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
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v1.0.0
 * @version v2.0.0
 */
/**
 * Handles all page display and operations for forms.
 *  
 * This class extends the {@link I2CE_Page} class and adds some default methods and variables
 * for form templates that will interface with a {@link I2CE_Form} object to update the
 * database.  The {@link loadObjects()} method must be overwritten for all objects
 * extending this abstract class.
 * @package I2CE
 * @abstract
 * @access public
 * @see I2CE_Template
 */
abstract class I2CE_PageForm extends I2CE_PageFormBase {

    /**
     * Constant values for the type of object being set to be edited for this page: Primary
     * The primary object is the main object being edited by this page.  There can only be one.
     */
    const EDIT_PRIMARY = 1;
    /**
     * Constant values for the type of object being set to be edited for this page: Parent
     * The parent for the primary and secondary objects being edited by this page.  The can only be one.
     */
    const EDIT_PARENT = 2;
    /**
     * Constant values for the type of object being set to be edited for this page: Secondary
     * Any secondary objects being edited by this page.  They share the parent of the primary.  Multiple objects
     * can be added.
     */
    const EDIT_SECONDARY = 3;
    /**
     * Constant values for the type of object being set to be edited for this page: Children
     * Any child objects of the primary object will use this type.  Multiple objects can be added.
     */
    const EDIT_CHILD = 4;
        
    /**
     * The objects related to this page.  Using the PAGE_FORM_EDIT constants to determine the key for the 
     * array and the values will either be a {@link I2CE_Form} or an array of {@link I2CE_Form}s.
     * @var array
     */
    protected $objects;
    /**
     * The node id's of objects related to this page.
     * @var array
     */
    protected $node_ids;
        
    /**
     * Create a new instance of a form page.
     * 
     * This will call the constructor for all Page objects and then set up some additional
     * member variables for forms.
     * @param string $title The title for this page.
     * @param string $defaultHTMLFile The default HTML file for this page.
     * @param mixed $access The role required to access this page.
     * @param array $files The list of template files to load for this page.
     */
    public function __construct( $args,$request_remainder, $get = null,$post = null) {
        parent::__construct( $args,$request_remainder,$get,$post);
        $this->template->addHeaderLink('I2CE_ClassValues.js');
        $this->template->addHeaderLink('I2CE_SubmitButton.js');
        $this->node_ids = array();
        $this->objects = array( 
            self::EDIT_PRIMARY => false,
            self::EDIT_PARENT => false,
            self::EDIT_SECONDARY => array(),
            self::EDIT_CHILD => array(),
            );
    }
        
    /**
     * Add an object to the objects being edited or displayed by this page.
     * @param I2CE_Form $object  
     * @param integer $type The type of object being added from EDIT constants.
     * @param mixed $node_id  a tring which gives the node id for which this object applies to. also can be the DOMNode.   
     * @param boolean $set_form Set to true to set the form at this point.
     * Defaults to null meaning that it applies to the whole page
     *
     */
    public final function setObject( $object, $type = self::EDIT_PRIMARY,$node_id = null, $set_form = false ) {
        switch ( $type ) {
        case self::EDIT_PARENT :
        case self::EDIT_PRIMARY :
            $this->objects[$type] = $object;
            $this->node_ids[$type] = $node_id;
            break;
        case self::EDIT_SECONDARY :
        case self::EDIT_CHILD :
            $this->objects[$type][] = $object;
            $this->node_ids[$type][] = $node_id;
            break;
        }
        if ( $set_form ) {
            $this->template->setForm( $object, $node_id );
        }
    }
        
    /**
     * Return the primary object being edited.
     * @return I2CE_Form
     */
    protected function getPrimary() {
        return $this->objects[self::EDIT_PRIMARY];
    }
    /**
     * Return the parent object for this form.
     * @return I2CE_Form
     */
    protected function getParent() {
        return $this->objects[self::EDIT_PARENT];
    }
        
    /**
     * Set the I2CE_Form object in the page template.
     * 
     * This method will pass the edit object to the page template so that it can process all the form variables.
     */
    protected function setForm() {
        foreach( $this->objects as $type => $edit ) {
            if ( is_array( $edit ) ) {
                foreach( $edit as $i=> $obj ) {
                    if( $obj instanceof I2CE_Form ) {
                        if ($this->node_ids[$type][$i] !== null) {
                            $this->template->setForm( $obj, $this->node_ids[$type][$i] );
                        } else {
                            $this->template->setForm( $obj );
                        }
                    }
                }
            } elseif ( $edit instanceof I2CE_Form ) {
                if ($this->node_ids[$type] !== null) {
                    $this->template->setForm( $edit, $this->node_ids[$type] );
                } else {
                    $this->template->setForm( $edit );
                }
            }
        }
    }
    /**
     *Get the child objects we are editing on the page
     *@param string $form the name of the form
     *@returns mixed.  array of obects
     */
    public function getChildObjects($form) {
        $ret = array();
        foreach( $this->objects[ self::EDIT_CHILD] as $obj ) {
            if (!$obj instanceof I2CE_Form
                ||$obj->getName() !=  $form
                ){
                continue;
            }
            $ret[] = $obj;
        }
        return $ret;
        
    }
    /**
     *Get the objects we are editing on the page
     *@param string $form the name of the form
     *@returns mixed.  array of obects
     */
    public function getSecondaryObjects($form) {
        $ret = array();
        foreach( $this->objects[ self::EDIT_SECONDARY] as $obj ) {
            if (!$obj instanceof I2CE_Form
                ||$obj->getName() !=  $form
                ){
                continue;
            }
            $ret[] = $obj;
        }
        return $ret;
        
    }

    /**
     * Create and load any necessary objects for this form.
     * 
     * This method must be written for each class extending this class.
     */
    protected function loadObjects() {
        if ( $this->getParent() instanceof I2CE_Form ) {
            if ($this->getPrimary() instanceof I2CE_Form) {
                $this->getPrimary()->setParent( $this->getParent() );
            }
            foreach( $this->objects[self::EDIT_SECONDARY] as $secondary ) {
                $secondary->setParent( $this->getParent() );
            }
        }
        if ( $this->isPost() ) {
            if ($this->getPrimary() instanceof I2CE_Form) {
                $this->getPrimary()->load( $this->post);
            }
            foreach( $this->objects[self::EDIT_CHILD] as $child ) {                
                $child->load( $this->post ,  $child->getId() != '0');                
                $child->setParent( $this->getPrimary(), true );
            }
            foreach( $this->objects[self::EDIT_SECONDARY] as $secondary ) {
                $secondary->load( $this->post ,  $secondary->getId()!= '0');
            }
        } elseif ( $this->getPrimary() instanceof I2CE_Form ) {
            if ( $this->getPrimary()->getId() != '0' ) {
                $this->getPrimary()->populate();
                foreach( $this->objects[self::EDIT_CHILD] as $child ) {
                    $child->setParent( $this->getPrimary(), true );
                    $child->populate();
                }
                // Nothing needs to be done for secondary objects here because 2 objects can't be edited on the same form,
                // only new objects can be added to the database.
                $this->setEditing();
            }
        }
        return true;
    }
        

    /**
     * Run the validation methods for all the objects being edited.
     * 
     * If this is a form submit then run the validation methods for the default object being edited.  The default method
     * calls the {@link I2CE_Form::validate() validate} method on the {@link $edit_obj} object.
     */
    protected function validate() {
        if ($this->checked_validation) {
            return;
        }
        if ( $this->isPost() ) {
            foreach( $this->objects as $type => $edit ) {
                if ( $type == self::EDIT_PARENT ) continue;
                if ( is_array( $edit ) ) {
                    foreach( $edit as $i=>$obj ) {
                        if( $obj instanceof I2CE_Form ) {
                            $obj->validate();
                        }
                    }
                } elseif ( $edit instanceof I2CE_Form ) {
                    $edit->validate();
                }
            }
        }
        $this->checked_validation = true;
    }
                
    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited b
     * @global array
     */
    protected function save() {
        $saved = $this->getPrimary()->save( $this->user );
        foreach( $this->objects[ self::EDIT_SECONDARY] as $obj ) {
            if (!$obj->save( $this->user )) {
                I2CE::raiseError("Could not save secondary object" . get_class($obj));
                return false;
            }
        }
        foreach( $this->objects[ self::EDIT_CHILD] as $obj ) {
            if ( $obj->getId() == '0' ) {
                $obj->setParent( $this->getPrimary() );
            }
            if (!$obj->save( $this->user )) {
                I2CE::raiseError("Could not save child object" . get_class($obj));
                return false;
            }
        }
        return $saved;
    }
        
        
    
        
    /**
     *Checks to see if any of the forms on this page have invalid messages
     *@returns boolean
     */
    public function hasInvalid() {
        $invalid = false;        
        foreach (array(self::EDIT_PARENT,self::EDIT_PRIMARY) as $key) {
            if ( !$this->objects[$key]  instanceof I2CE_Form) {
                continue;
            }
            $invalid |= $this->objects[$key]->hasInvalid();
        }
        foreach (array(self::EDIT_SECONDARY,self::EDIT_CHILD) as $key) {
            if (!is_array($this->objects[$key])) {
                continue;
            }
            foreach ($this->objects[$key] as $form) {
                if (!$form instanceof I2CE_Form) {
                    continue;
                }
                $invalid |= $form->hasInvalid();
            }
        }
        return $invalid;
    }

    /**
     * Add the form_error template to the page if the template is marked as invalid.
     */
    public  function invalidMessage() {  
        $node = parent::invalidMessage();
        if ($node instanceof DOMNode) {
            foreach (array(self::EDIT_PARENT,self::EDIT_PRIMARY) as $key) {
                if ( !$this->objects[$key]  instanceof I2CE_Form) {
                    continue;
                }
                if ( $this->objects[$key]->hasInvalid()) {
                    foreach ($this->objects[$key] as $field) {
                        if (!$field instanceof I2CE_FormField ) {
                            continue;
                        }
                        if ($field->hasInvalid()) {
                            $node->appendChild($this->template->createElement('span',array('style'=>'display:none'),$field->getHTMLName()));
                        }

                    }
                }
            }
            foreach (array(self::EDIT_SECONDARY,self::EDIT_CHILD) as $key) {
                if (!is_array($this->objects[$key])) {
                    continue;
                }
                foreach ($this->objects[$key] as $form) {
                    if (!$form instanceof I2CE_Form) {
                        continue;
                    }
                    if ($form->hasInvalid()) {
                        foreach ($form as $field) {
                            if (!$field instanceof I2CE_FormField ) {
                                continue;
                            }
                            if ($field->hasInvalid()) {
                                $node->appendChild($this->template->createElement('span',array('style'=>'display:none'),$field->getHTMLName()));
                            }
                        }
                    }
                }
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
