<?php
/*
 * © Copyright 2006, 2007, 2008, 2009 IntraHealth International, Inc.
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
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v1.0.0
 * @version v2.0.0
 */
/**
 * Abstract class for objects using the {@link I2CE_Template} class that interact with a database and HTML form.
 * 
 * This class has a few default functions that are used when interacting with the template engine
 * in setting up form values and displaying the data for objects that tie directly to database tables.
 *
 * @package I2CE 
 * @access public
 * @see I2CE_Template
 */
class I2CE_Form extends I2CE_FieldContainer {

    
    public $lookup_for_matching;

    /**
     * @param mixed $node DOMNode of XML containing XML source
     * @param mixed $fields.  Defaults to false.  If it is an array, it is the list of the fields we should populate.
     */
    public function loadFromXML($node, $fields = false) {
        if( is_string($node)) {
            $doc = new DOMDocument();
            if (! ($doc->loadXML($node))) {
                return false;
            }
            $node = $doc->documentRoot;
        } else if ($node instanceof DOMDocument) {
            $node = $node->documentElement;
        }
        if (!$node instanceof DOMElement) {
            I2CE::raiseError("Not XML");
            return false;
        }
        if (! (parent::loadFromXML($node,$fields))) {
            return false;
        }
        if ( $node->hasAttribute('parent_form') && $node->hasAttribute('parent_id')) {
            $this->setParent($node->getAttribute('parent_form') . '|' . $node->getAttribute('parent_id'));
        }
        if ($node->hasAttribute('created')) {
            $this->createdField->setFromDB($node->getAttribute('created'));
        }
        if ($node->hasAttribute('modified')) {
            $this->lastModifiedField->setFromDB($node->getAttribute('modified'));
        }
        
        if (($form_nodes = $node->getElementsByTagName('form')) instanceof DOMNodeList) {
            foreach ($form_nodes as $form_node) {
                if (!$form_node instanceof DOMElement
                    || !$form_node->hasAttribute('name')
                    || !$form_node->hasAttribute('id')
                    || ! ($obj = $this->factory->createContainer($form_node->getAttribute('name') . '|0')) instanceof I2CE_Form
                    ) {
                    continue;
                }
                $obj->loadFromXML($form_node);
                $this->addChildForm($obj);              
            }
        }
        return true;
    }


  /**
     * Get an XML representation of the data
     * @param boolean $as_node.  Defaults to true if true, then we return DOMNode otherwise we return string representation of the generate node
     * @param DOMNode $append_node.  If DOMNode then XMLData is appended onto the given node.
     * @param boolean $include_children.  Defaults to false
     * @param mixed $fields.  Defaults to false.  If it is an array, it is the list of the fields we should populate get the representation for.
     */
    public function getXMLRepresentation($as_node = true,$append_node =null, $fields =false,$include_children = false) {
        $cont_node = parent::getXMLRepresentation(true,$append_node,$fields);
        if ($this->parentIsSet()) {
            $cont_node->setAttribute('parent_form',$this->getParentForm());
            $cont_node->setAttribute('parent_id',$this->getParentID());
        }
        $cont_node->setAttribute('created',        $this->createdField->getDBValue());
        $cont_node->setAttribute('modified',        $this->lastModifiedField->getDBValue());
        $doc = $cont_node->ownerDocument;
        if ($include_children) {
            foreach ($this->getChildForms() as $child_form) {
                $this->populateChildren($child_form);
                foreach ($this->getChildren($child_form) as $obj) {
                    $obj->getXMLRepresentation(true,$cont_node,true);
                }
            }
        }
        if ($as_node) {
            return $cont_node;
        } else {        
            return $doc->saveXML($cont_node);
        }

    }

   
    
    /**
     * An array of children objects for this form.
     * 
     * It is an array of arrays.  The first being an associative array with the name of the form, the second
     * level is a simple array of the objects.
     * @var array
     */
    public $children;

    /**
     * Get the registered child forms for this form.
     * @returns array The list of child form names registered for this form.
     */
    public function getChildForms() {
        return self::getChildFormsByForm( $this->name );
    }

    /**
     * Get the registered child forms for the given form.
     * @param string $form The form to get the children of.
     * @return array The list of child form names registered for the form.
     */
    public static function getChildFormsByForm( $form ) {
        $childForms = array();
        I2CE::getConfig()->setIfIsSet($childForms,"/modules/forms/forms/$form/meta/child_forms",true);
        return $childForms;
    }
        
    /**
     * Get the added children for this form
     * @param string $form   Defaults to null, in which case we get all children.  
     *                       otherwise we get the popoluated children with the specified form
     * @returns array   If $form is null, it is an array with keys the form names and values array 
     *                 of the children of that form type.  If $form is
     *                 set, then it is an array of the forms of type $form
     */
    public function getChildren( $form = null ) {
        if ( is_string($form) && strlen($form) > 0 ) {
            if (array_key_exists($form,$this->children) && is_array($this->children[$form])) {
                return $this->children[$form];
            } else {
                return array();
            }
        } else {
            return $this->children;
        }
    }


    /**
     * @var protected array parent_forms. The array with keys form names and values which are arrays of
     * form names, the form names which the form's parent id can take values in
     */
    protected static $parent_forms  = array();




    /**
     * Gets the allowed parent forms for a given form
     * @param string $form
     * @param boolean $use_cache.  Defaults to true in which case we cached result of finding parent forms
     * @returns array of string, the form names.
     */
    public static function getAllowedParentForms($form, $use_cache = true) {
        if (!$use_cache || !array_key_exists($form,self::$parent_forms)) {
            $parent_forms = array();
            $formsConfig = I2CE::getConfig()->modules->forms->forms;
            foreach ($formsConfig as $pform=>$formConfig) {
                if (!$formConfig instanceof I2CE_MagicDataNode) {
                    continue;
                }
                $pforms = array();
                $formConfig->setIfIsSet($pforms,"meta/child_forms",true);
                if (!in_array($form,$pforms)) {
                    continue;
                }
                $parent_forms[] = $pform;
            }

            self::$parent_forms[$form] =$parent_forms;
        }
        return self::$parent_forms[$form];
    }


     /**
      * @var protected I2CE_FormField $parentField
      */
     protected $parentField = false;
     
     /**
      * @var protected I2CE_FormField_DATE_TIME $lastModifiedField
      */
     protected $lastModifiedField = false;

     /**
      * @var protected I2CE_FormField_DATE_TIME $created
      */
     protected $createdField = false;

     /**
      *Setup the parent field.  the use of string_line is considered temporary.
      * @param mixed $value.  The value to set it to.  Defaults to null in which case no value is set
      */
     protected function setupParentField() {
         $this->parentField = new I2CE_FormField_STRING_LINE( "parent", array( 'in_db' => false ) );
         $this->parentField->setContainer( $this );
         return true;
     }
     /**
      *Setup the last_modified field as a date time
      * @param mixed $value.  The value to set it to.  Defaults to null in which case no value is set
      */
     protected function setupCreatedField() {
         $this->createdField = new I2CE_FormField_DATE_TIME( "created", array( 'in_db' => false ) );
         $this->createdField->setFromDB( '0000-00-00 00:00:00' );
         $this->createdField->setContainer( $this );
         return true;
     }
     /**
      *Setup the created field as a DATE_TIME
      * @param mixed $value.  The value to set it to.  Defaults to null in which case no value is set
      */
     protected function setupLastModifiedField() {
         $this->lastModifiedField = new I2CE_FormField_DATE_TIME( "last_modified", array( 'in_db' => false ) );
         $this->lastModifiedField->setFromDB( '0000-00-00 00:00:00' );
         $this->lastModifiedField->setContainer( $this );
         return true;
     }


     /**
      * Pre process field args before creating the field object
      * @param string $field
      * @param array &$args
      */
     protected function preProcessFieldArgs( $field, &$args ) {
         parent::preProcessFieldArgs( $field, $args );
         if ( array_key_exists( 'meta', $args )
                 && array_key_exists( 'add_limit_module', $args['meta'] ) ) {
             $mod_factory = I2CE_ModuleFactory::instance();
             foreach( $args['meta']['add_limit_module'] as $module => $method ) {
                 if ( $mod_factory->isEnabled( $module ) ) {
                     $mod_class = $mod_factory->getClass( $module );
                     if (!$mod_class instanceof I2CE_Module) {
                         I2CE::raiseError("Invliad module class for $module");
                         continue;
                     }
                     if ( !$mod_class->_hasMethod($method)) {
                         I2CE::raiseError("Cannot access method $method of $module when " . $this->getName() . "." . $field . " is trying to add limits from it." );
                     } else {
                         $args['meta']['limits_add'][$module] = $mod_class->$method( $this, $args );
                         $args['meta']['enable_limits_add'][$module] = 1;
                     }                     
                 }
             }
         }
     }

    /**
     * Create a new instance of a I2CE_Form object.
     * @param I2CE_FieldContainer_Factory $factory
     * @param string $name The name of this form
     * @param integer $id
     */
    public function __construct( $factory, $name, $id='0' ) {
        $this->children = array();
        $this->setupParentField();
        $this->setupLastModifiedField();
        $this->setupCreatedField();
        I2CE_ModuleFactory::callHooks( 'form_pre_construct', 
                array( 'form' => $this, 'name' => $name, 'id' => $id ) );
        parent::__construct($factory,$name,$id);
        if ( I2CE_ModuleFactory::instance()->isEnabled( "forms-storage" ) ) {
            $this->setChangeType();
        }
        I2CE_ModuleFactory::callHooks( 'form_post_construct', 
                array( 'form' => $this, 'name' => $name, 'id' => $id ) );
    }
    



        
        
    /**
     * Clean up all the fields for this form.
     * 
     * This will unset all the fields associated with this form.  This will remove
     * all circular references to this form so it can be cleaned up by the garbage collector.
     * This should only be called when the form is no longer needed.  Trying to access it
     * after this may cause unexpected results or errors.
     */
    public function cleanup($remove_from_cache = true) {
        if($this->parentField instanceof I2CE_FormField) {
            $this->parentField->cleanup();
        }
        unset($this->parentField );
        if($this->lastModifiedField instanceof I2CE_FormField) {
            $this->lastModifiedField->cleanup();
        }
        if($this->createdField instanceof I2CE_FormField) {
            $this->createdField->cleanup();
        }
        unset($this->lastModifiedField );
        unset($this->createdField );
        I2CE_ModuleFactory::callHooks('form_cleanup',array('form'=>$this,'remove_from_cache'=>$remove_from_cache));
        parent::cleanup($remove_from_cache);
    }


    /**
     * Return the form ID for this form.
     * @return string
     * @deprecated
     */
    public function getFormID() {
        return $this->getNameId();
    }


    /**
     * Return the (db value of the) parent id for this record.
     * 
     * If there isn't a parent record set then return the id for this record.
     * @return mixed.  false if there is no parent id or string  a parent id of the form "$form|$id"
     */
    public function getParent( ) {
        return $this->parentField->getDBValue();
    }


    /**
     * Get the history of each field in this container.  May need to populate the history
     *
     * @param boolean $as_array.  Defaults to false in which case results are instances of I2CE_Entry, otherwise reusults are associative arrays
     * @returns array index by field names
     */
    public function getHistory($as_array =false) {
        $history = parent::getHistory($as_array);
        $parentHistory = $this->parentField->getHistory($as_array);
        if (is_array($parentHistory) && count($parentHistory) > 0) {
            $history['parent'] =$parentHistory;
        }
        return $history;
    }


    /**
     * Checks to see if the parent field has been set
     */
    public function parentIsSet() {
        $parent = $this->getParent();
        if (!is_string($parent) || strpos($parent,'|') === false) {
            return false;
        }
        list($p_form,$p_id) = explode('|',$parent,2);
        return $p_form && $p_id;
    }

    /**
     * Return the parent id for this record.
     * 
     * If there isn't a parent record set then return the id for this record.
     * @return mixed.  false if there is no parent id or string  a parent id 
     */
    public function getParentID( ) {
        $parent = $this->getParent();
        if (!is_string($parent) || strpos($parent,'|') === false) {
            return 0;
        } else {
            list($p_form,$p_id) = explode('|',$parent,2);
            if (strlen($p_id) == 0) {
                return 0;
            }
            return $p_id;
        }
    }

    /**
     * Return the name of the parent form for this record.
     * 
     * If there isn't a parent record set then return the id for this record.
     * @return mixed.  false if there is no parent   the parent form
     */
    public function getParentForm( ) {
        $parent = $this->getParent();
        if (!is_string($parent) || strpos($parent,'|') === false) {
            return 0;
        } else {
            list($p_form,$p_id) = explode('|',$parent,2);
            return $p_form;
        }
    }


        
    /**
     * Set the parent id for this record
     * @param mixed $parent string or I2CE_Form
     * @param boolean $set_id If the parent will only have one child object then you can set the id by setting this to true. 
     */
    public function setParent( $parent, $set_id = false ) {
        if ($parent instanceof I2CE_Form) {
            $this->parentField->setFromDB( $parent->getName() . '|' . $parent->getID());
        } else {
            if (!is_string($parent) || strlen($parent) == 0) {
                $this->parentField->setFromDB('|');
                return false;
            }
            if ((strpos($parent,'|'))=== false) {
                I2CE::raiseError("Bad parent id");
                $this->parentField->setFromDB('|');
                return false;
            }
            $this->parentField->setFromDB($parent);
        }
        if ( !$set_id || $this->id != '0'  || !I2CE_ModuleFactory::instance()->isEnabled('forms-storage')) {
            return true;
        }
        if (is_string($parent)) {
            $parent =explode('|',$parent,2);
            list($parent_form_name, $parent_id)  = $parent;
            $parent_form = I2CE_FormFactory::instance()->createContainer($parent_form_name.'|'.$parent_id);
            if (!$parent_form instanceof I2CE_Form) {
                I2CE::raiseError("Bad parent form $parent_form_name");
                return false;
            }
        } else {
            $parent_form = $parent; //this has to be an I2CE_Form
        }
        if ($parent_form->getID() == '0' ) {            
            return false;
        }
        $child_ids = $parent_form->getChildIds($this->name);                
        if (!is_array($child_ids)) {
            I2CE::raiseError("invalid child form {$this->name} for " . $parent_form->getName() . " with id " . $parent_form->getID());
            return false;
        } else if  ( count($child_ids) > 1) {
            I2CE::raiseError("No unique child form {$this->name} for " . $parent_form->getName() . " with id " . $parent_form->getID());
            return false;
        } else if (count($child_ids ) == 1) {
            reset($child_ids);
            $this->setId(current($child_ids));
        }
        return true;
    }

    /**
     * Set the last modified time for this record
     * @param string $last_mod
     */
    public function setLastModified( $last_mod ) {
        $this->lastModifiedField->setFromDB( $last_mod );
    }

    /**
     * Set the create time for this record
     * @param string $datetime
     */
    public function setCreated( $datetime ) {
        $this->createdField->setFromDB( $datetime );
    }

        
    /**
     * Set the form name for this form object.
     * @deprecated -- use {setName()}
     * @param string $name
     */
    final public function setForm( $name ) {
        $this->setName($form);
    }

    /**
     * Return the form name for this form.
     * @deprecated -- use {getName()}
     * @return string
     */
    public function form() {
        return $this->getName();
    }

    /**
     * Set the display name for this form object.
     * @param string $display
     */
    final public function setDisplayName( $display ) {
        $this->display_name = $display;
    }

    /**
     * Return the display name for this form object.
     * @return string
     */
    public function getDisplayName() {
        return $this->display_name;
    }


    /**
     * Display the field in the given node.
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @param array $args
     */
    public function displayField($node,$template,$args) {        
        $field = $this->getField($args[0]);
        if (  $field instanceof I2CE_FormField) {
            $processor = 'processDOM';
            if ($node->hasAttribute('display') && ($field->_hasMethod('processDOM_' . $node->getAttribute('display')))) {
                $processor='processDOM_' . $node->getAttribute('display');
            }
            $field->$processor($node,$template);            
        } 
    }


    /**
     * Process the DOM for this form.
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @param string $method
     * @param array $args
     */
    public function processDOM(&$node,&$template,$method,$args) {
        if (!is_callable(array($this,$method))) {
            I2CE::raiseError("Method $method not callable in {$this->getName()}", E_USER_NOTICE);
            //do nothing
            return;
        }
        if ( $node->hasAttribute("ifset") ) {
            $ifset = $node->getAttribute( "ifset" );
            if ( $ifset[0] == "!" ) {
                $not = true;
                $ifset = substr($ifset, 1);
            } else {
                $not = false;
            }
            if  (($ifset == "true" || $ifset=="dateblank") &&  ($method=='displayField')) {
                //we check a field on the current form.  the field name is the argument
                $if_field = $this->getField($args[0]);
            } else {
                $phpfunc = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
                if (preg_match("/^($phpfunc)\$/",$ifset)) {
                    //we are looking at field of this form
                    $if_field = $this->getField($ifset);
                } else {
                    //we need to check the validity of a field of a different form
                    $if_field = $template->getField($ifset,$node);
                }
            }
            $is_value = true;
            if ( $node->hasAttribute( "ifvalue" ) ) {
                $ifvalue = $node->getAttribute( "ifvalue" );
                if ( $ifvalue[0] == "!" ) {
                    $val_not = true;
                    $ifvalue = substr($ifvalue, 1);
                } else {
                    $val_not = false;
                }
                $is_value = $if_field instanceof I2CE_FormField && $if_field->getDBValue() == $ifvalue;
                if ( $val_not ) $is_value = !$is_value;
            }
            $is_valid = $if_field instanceof I2CE_FormField && $if_field->isValid();
            if ( $ifset == "dateblank" ) {
                $is_valid = $is_valid && $if_field instanceof I2CE_FormField && $if_field->getValue() instanceof I2CE_Date && $if_field->getValue()->isBlank();
            }
            if ( !( $is_valid  xor $not) || !$is_value )  {
                $node->parentNode->removeChild( $node );
                return;
            }
            $node->removeAttribute( "ifset" );
        } elseif ( $node->hasAttribute( "ifvalue" ) ) {
            $ifvalue = $node->getAttribute( "ifvalue" );
            $if_field = $this->getField($args[0]);
            if ( $ifvalue[0] == "!" ) {
                $val_not = true;
                $ifvalue = substr($ifvalue, 1);
            } else {
                $val_not = false;
            }
            $is_value = $if_field instanceof I2CE_FormField && $if_field->getDBValue() == $ifvalue;
            if ( $val_not ) $is_value = !$is_value;
            if ( !$is_value ) {
                $node->parentNode->removeChild( $node );
                return;
            } else {
                if ($node->hasAttribute('display')
                    && strlen(    $display = $node->getAttribute('display')) > 0) {
                    if ($display[0] == 'f' || !$display) {
                        $node->removeAttribute('type');
                        return;
                    }
                }
            }
            

        }
        $this->$method($node,$template,$args);
    }


    /**
     * Load the member variables from an array
     * The array can contain the keys 'id', 'parent', 'fields'.  The later of which
     * is an array indexed by field names and which contains the values of the field
     * 
     * @param array $post 
     */
    public function setFromPost($post, $populate_on_set_id = false) {
        parent::setFromPost($post,$populate_on_set_id);
        if (is_array($post) && array_key_exists('fields',$post) && is_array($post['fields']) && array_key_exists('parent',$post['fields'])) {
            $this->setParent($post['fields']['parent']);
        }
    }


    /**
     * Get the nested associative array that is used for post
     * @param array $field_names.  Array of string, the field names we wish to query.  Defaults to null in which we case we get all fields
     * @param boolean $skip_invalid. Defaults to true in which case we skip invalid values
     * @param boolean $include_id defaults to true
     * @return array
     */
    public function getPost($field_names = null, $skip_invalid = true, $include_id =true) {
        $post = parent::getPost($field_names,$skip_invalid, $include_id);
        if ( !$this->parentIsSet()) {
            $post['fields']['parent'] = $this->getParent();
        }
        return $post;
    }

    /**
     * Reset this object to its original state.
     *
     */
    public function reset() {
        $this->__construct( $this->getName() );
    }
        
    /**
     * Checks to see if the given field name is valid
     * @param string $fieldName
     * @returns boolean
     */
    protected function isValidFieldName($fieldName) {        
        switch($fieldName) {
        case 'parent':
        case 'last_modified':
        case 'created':
            return false;
        default:
            return parent::isValidFieldName($fieldName);
        }
    }
    
    /**
     * Check to see if the given form was added
     */
    public function childFormAdded($childform, $id) {
        if ($id == '0') {
            return false;
        }
        if (!array_key_exists($childform,$this->children) || !is_array($this->children[$childform])) {
            return false;
        }
        return (array_key_exists($id,$this->children[$childform]) && $this->children[$childform][$id] instanceof I2CE_Form);
        
    }


     /**
      * Add a child form object to this forms list of children.
      * @param I2CE_Form $child_form The child form
      * @param boolean $replace Overwrite the child object if it already exists.
      */
     public function addChildForm( $child_form,  $replace = false ) {
         if (!$child_form instanceof I2CE_Form) {
             return false;
         }
         $form_name = $child_form->getName();
         $id = $child_form->getId();
         if (!array_key_exists($form_name,$this->children)) {
             $this->children[$form_name] = array();
         }
         if (  array_key_exists( $id, $this->children[$form_name]) && $this->children[$form_name][$id] instanceof I2CE_Form && !$replace) {
             return;
         }
         $this->children[$form_name][$id] = $child_form;
         $child_form->setParent($this);
     }



    /**
     * Return the I2CE_FormField for the given field name.
     * @param string $field the field name or a form name:field name
     * @return I2CE_FormField
     */
    public function getField( $field ) {
        if (strpos($field,':') !== false) {
            list($name,$field) = explode(':', $field,2);
            if ($name !== $this->name) {
                I2CE::raiseError("Using wrong reference to container name  $name != {$this->name}");
                return null;
            }
        }        
        if ($field == 'parent') {
            return $this->parentField;
        } elseif ( $field == 'last_modified' ) {
            return $this->lastModifiedField;
        } elseif ( $field == 'created' ) {
            return $this->createdField;
        } else {
            return parent::getField($field);
        }

    }


    /**
     * Lookup a given id in the given array.
     * @param integer $id The id to lookup.
     * @param array $arr The array to search through.
     * @return string
     */
    static protected function lookupArray( $id, $arr ) {
        if (!is_array($arr)) {
            I2CE::raiseError("Expected array but not received");
            return "";
        }
        if ( array_key_exists( $id, $arr ) ) {
            return $arr[ $id ];
        } else {
            return "";
        }
    }

        





    /**
     * @param string $form  The form name.
      *@param mixed $where_data. Either I2CE_MagicDataNode or array. contains the  where clause information about this form or a nested
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit. Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.  
     * @returns mixed an array of matching form ids.  However, ff $limit_one is true or 1 or 
     * array ($offset,1) then then we return either the id or false,  if none found or there was an error.
     */
    public  static function search ( $form, $where_data=array(), $ordering = array(), $limit_one = false) {        
        if (is_string($ordering)) {
            $ordering = array($ordering);
        }
        if (!is_array($ordering)) {
            $ordering = array();
        }        
        if (I2CE_ModuleFactory::instance()->isEnabled('forms-storage')) {
            return  I2CE_FormStorage::search($form,$where_data, $ordering, $limit_one);
        } else {
            $limit = false;
            if (is_array($limit_one)) {
                if (count($limit_one) == 2) {
                    end($limit_one);
                    if (current($limit_one) == 1) {
                        $limit = true;
                    }
                }
            } else {
                $limit = (($limit_one === true ) || (is_numeric($limit_one) && $limit_one == 1 ));
            }
            if ($limit_one) {
                return false;
            } else {
                return array();
            }
        }
    }


    /**
     * @param string $form The form name
     * @param array $fields of string. The fields we want returned
      *@param mixed $where_data. Either I2CE_MagicDataNode or array. contains the  where clause information about this form or a nested
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.
     * @returns mixed an array with key id's and value and array of values.  the array of values has as keys the fields with their corresponding value.
     */
    public  static function listFields($form, $fields, $where_data=array(), $ordering=array(), $limit = false) {                                                      
        if (is_string($ordering)) {
            $ordering = array($ordering);
        }
        if (!is_array($ordering)) {
            $ordering = array();
        }        
        if (I2CE_ModuleFactory::instance()->isEnabled('forms-storage')) {
            return  I2CE_FormStorage::listFields($form,$fields, false, $where_data, $ordering, $limit);
        } else {
            return array();
        }
    }




}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
