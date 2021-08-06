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
 * @author carl Leitner <litlfred@ibiblio.org>
 * @since v4.1.0
 * @version v4.1.0
 */
/**
 * Handles all page display and operations for multiple anonymous forms.
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
class I2CE_PageMultiForm extends I2CE_PageFormBase {

        
    /**
     * The objects related to this page
     * @var protected array $objects of I2CE_Form
     */
    protected $objects;
    protected $existing_objects;
    /**
     * A parent form (if any) for all the forms being edited on this page
     * @var protected I2CE_Form $parentObj
     */
    protected $parentObj;
    /**
     * The node id's of objects related to this page.
     * @var array
     */
    protected $node_ids;
    protected $existing_node_ids;

        


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
        $this->objects = array( );
        $this->existing_objects = array( );
        $this->node_ids = array();
        $this->existing_node_ids = array();
    }
        
    /**
     * Return the primary objects being edited.
     * @return array of I2CE_Form
     */
    protected function getPrimaryObjects() {
        return $this->objects;
    }
    /**
     * Return the parent object for this form.
     * @return I2CE_Form
     */
    protected function getParent() {
        return $this->parentObj;
    }
        
    // /**
    //  * Add an object to the objects being edited or displayed by this page.
    //  * @param I2CE_Form $object  
    //  * @param mixed $node_id  a tring which gives the node id for which this object applies to. also can be the DOMNode.   
    //  * Defaults to null meaning that it applies to the whole page
    //  *
    //  */
    // protected final function setObject( $object, $node_id = null ) {
    //     $this->objects[] = $object;
    //     $this->node_ids[] = $node_id;
    // }

    /**
     * Set the I2CE_Form object in the page template.
     * 
     * This method will pass the edit object to the page template so that it can process all the form variables.
     */
    protected function setForm() {
        if ($this->parentObj instanceof I2CE_Form) {
            $this->template->setForm($this->parentObj);
        }
        foreach( $this->existing_objects as $i=>$obj ) {
            if( !$obj instanceof I2CE_Form ) {
                continue;
            }
            if ($this->existing_node_ids[$i] !== null) {
                $this->template->setForm( $obj, $this->existing_node_ids[$i] );
            } else {
                $this->template->setForm( $obj );
            }
        }
        foreach( $this->objects as $i=>$obj ) {
            if( !$obj instanceof I2CE_Form ) {
                continue;
            }
            if ($this->node_ids[$i] !== null) {
                $this->template->setForm( $obj, $this->node_ids[$i] );
            } else {
                $this->template->setForm( $obj );
            }
        }

    }

    protected function addAnonymousTemplate($first) {

    }


    protected function addExistingTemplate($id) {

    }


    /**
     * Create and load any necessary objects for this form.
     * 
     * 
     */
    protected function loadObjects() {
        if (!$this->isPost()) {
            $this->parentObj = $this->factory->createContainer($this->request('parent'));
            if ($this->parentObj instanceof I2CE_Form) {
                $this->parentObj->populate();
            }
        } else {
            //it is a post
            $this->parentObj = $this->factory->createContainer($this->args['parent_form']);
            $this->parentObj->load($this->request());
        }
        if (!$this->parentObj instanceof I2CE_Form) {
            I2CE::raiseError("No parent form object");
            return false;
        }
        $this->parentObj->populateChildren($this->args['page_form']);
        if (array_key_exists( $this->args['page_form'],$this->parentObj->children) && is_array($this->parentObj->children[$this->args['page_form']])) {
            $this->existing_objects = $this->parentObj->children[$this->args['page_form']];
        }
        $this->objects = $this->factory->createContainersFromPost($this->request(),$this->args['page_form'],false,array_keys($this->existing_objects));
        if (!$this->isPost()) {            
            $this->objects[] = $this->factory->createContainer($this->args['page_form']); //create one anoymous one
        }
        foreach( $this->objects as $obj ) {
            $obj->setParent( $this->parentObj );
        }

        if ( !$this->isPost() ) {
            $this->setEditing();
        } else {
            foreach( $this->existing_objects as $obj) {
                $obj->load( $this->post ,  false,false) ;
            }
        }
        return true;
    }
        


    /**
     * Load the  template (HTML or XML) files to the template object.
     *  
     * 
     */  
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->setDisplayData('parent',$this->request('parent'));
        $first = true;
        foreach ($this->existing_objects as $id=>$obj) {
            $this->addExistingTemplate($id);
        }
        if (count($this->existing_objects) > 0) {
            $this->template->setDisplayData('has_existing',1);
        } else {
            $this->template->setDisplayData('has_existing',0);
        }
        foreach ($this->objects as $obj) {
            $this->addAnonymousTemplate($first);
            $first = false;
        }
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
            if ($this->parentObj instanceof I2CE_Form) {
                foreach( $this->objects as $obj ) {
                    if( !$obj instanceof I2CE_Form ) {
                        continue;
                    }                

                    $obj->setParent( $this->parentObj );
                }
            }
            foreach( $this->objects as $obj) {
                if( !$obj instanceof I2CE_Form ) {
                    continue;
                }                
                $obj->validate();
            }
            foreach( $this->existing_objects as $obj) {
                if( !$obj instanceof I2CE_Form ) {
                    continue;
                }               
                $obj->validate();
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
        $this->parentObj = $this->factory->createContainer($this->args['parent_form']);
        $this->parentObj->load($this->request());
        
        foreach( $this->objects as $obj ) {
            $obj->setParent($this->parentObj);
            if (!$obj->save( $this->user )) {
                I2CE::raiseError("Could not save object" . get_class($obj));
                return false;
            }
        }
        foreach( $this->existing_objects as $obj ) {
            if (!$obj->save( $this->user )) {
                I2CE::raiseError("Could not save object" . get_class($obj));
                return false;
            }
        }
        return true;
    }
        
    
        
    /**
     *Checks to see if any of the forms on this page have invalid messages
     *@returns boolean
     */
    public function hasInvalid() {
        $invalid = false;        
        foreach ($this->objects as $form) {
            if (!$form instanceof I2CE_Form) {
                continue;
            }
            $invalid |= $form->hasInvalid();
        }
        return $invalid;
    }


    
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
