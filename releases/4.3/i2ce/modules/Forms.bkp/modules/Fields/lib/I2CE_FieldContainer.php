<?php
/**
* © Copyright 2010 IntraHealth International, Inc.
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
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.6
* @since v4.0.6
* @filesource 
*/ 
/** 
* Class I2CE_FieldContainer
* 
* @access public
*/


abstract class I2CE_FieldContainer  extends I2CE_Fuzzy implements Iterator {

    /**
     * @param mixed $node DOMNode of XML containing XML source
     * @param mixed $fields.  Defaults to false.  If it is an array, it is the list of the fields we should populate.
     */
    public function loadFromXML($node,$fields = false) {
        if( is_string($node)) {
            $doc = new DOMDocument();
            if (! ($doc->loadXML($node))) {
                return false;
            }
            $node = $doc->documentRoot;
        } else if ($node instanceof DOMDocument) {
            $node = $node->documentElement;
        }        
        if ( !($type = $this->getContainerType() )) {
            return false;
        }
        if ($node->tagName != $type) {
            I2CE::raiseError("Invalid element tag: " . $node->tagName . ' != ' . $type);
            return false;
        }
        if (!$node instanceof DOMElement
            || !$node->hasAttribute('name') 
            || $node->getAttribute('name') != $this->name
            //|| !$node->hasAttribute('id')
            ) {
            //I2CE::raiseError("no $type attribute found with value  {$this->name} or missing id");
            I2CE::raiseError("no $type attribute found with value  {$this->name} ");
            return false;
        }
        $this->setId($node->getAttribute('id'));
        if (($field_nodes = $node->getElementsByTagName('field')) instanceof DOMNodeList) {
            foreach ($field_nodes as $field_node) {
                if (!$field_node instanceof DOMElement
                    || !$field_node->hasAttribute('name')
                    || ! ($name = $field_node->getAttribute('name'))
                    || (is_array($fields) && !in_array($name,$fields)) 
                    || ! ($fieldObj = $this->getField( $name )) instanceof I2CE_FormField
                    ) {
                    continue;
                }
                $fieldObj->loadFromXML($field_node);
            }
        }
        return true;
    }



    /**
     * Get an XML representation of the data
     * @param boolean $as_node.  Defaults to true if true, then we return DOMNode otherwise we return string representation of the generate node
     * @param DOMNode $append_node.  If DOMNode then XMLData is appended onto the given node.
     * @param mixed $fields.  Defaults to false.  If it is an array, it is the list of the fields we should populate get the representation for.
     */
    public function getXMLRepresentation($as_node = true,$append_node =null,$fields= false) {
        if ( !($type = $this->getContainerType() )) {
            I2CE::raiseError("no type set");
            return false;
        }
        if ($append_node instanceof DOMNode) {
            $doc = $append_node->ownerDocument;
            $cont_node = $doc->createElement($type);
            $append_node->appendChild($cont_node);            
        } else {
            $doc  = new DOMDocument();
            $doc->loadXML("<$type/>");
            $append_node = $doc->documentElement;
            $cont_node = $doc->documentElement;            
        }
        $cont_node->setAttribute('id',$this->id);
        $cont_node->setAttribute('name',$this->name);
        foreach ($this->getFieldNames() as $field) {                    
            if ((is_array($fields) &&  !in_array($field,$fields))
                || ! ($fieldObj = $this->getField($field)) instanceof I2CE_FormField
                || !$fieldObj->isValid()
                || !$fieldObj->isInDB() 
                ) {
                continue;
            }
            $fieldObj->getXMLRepresentation(true,$cont_node);
        }
        if ($as_node) {
            return $cont_node;
        } else {        
            return $doc->saveXML($cont_node);
        }
    }

    public function resetDefaultValues() {
        foreach ($this->getFieldNames() as $field) {            
            if ( !($fieldObj = $this->getField($field)) instanceof I2CE_FormField
                ){
                continue;
            }
            $fieldObj->resetDefaultValue();
        }
    }


    /**
     * @var string The name of this field container
     */
    protected $name;

    /**
     * @var string The text name of this field container for display.
     */
    protected $display_name;

    /**
     * @var array The list of fields with all the information about each field.
     */
    protected $fields = array();


    /**
     * @var array A list of attributes for this field container.
     */
    protected $attributes =array();

    /**
     * @var array A list of static attributes for this field container.
     */
    protected static $static_attrs =array();


    /**
     * @var mixed The record id for this entry.
     */
    protected $id;

    /**
     * Returns the name and id for this record.
     * @return string of the form "$name|$id"
     */
    public function getNameId() {
        return $this->name . '|' . $this->id;
    }

    /**
     * Returns the id for this record.
     * @return string
     */
    public function getId() {
        return $this->id;
    }


    /**
     * Set the id for this record.
     * @param mixed $id
     */
    public function setId( $id ) {
        if ($id === null || (is_string($id) && strlen($id) == 0)) {
            $id = '0';
        }
        if ( ( strpos( $id, '|' ) ) === false ) {
            $this->id = $id;
        } else {
            list( $this->name, $this->id ) = explode( '|', $id, 2 );
            if (strlen($this->id) == 0) {
                $this->id = '0';
            }
        }
    }

    /**
     * Set an attribute for this field container.
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute( $key, $value ) {
        $this->attributes[$key] = $value;
    }

    /**
     * Return the attribute value for a given attribute.
     * @param string $key
     * @return mixed
     */
    public function getAttribute( $key ) {
        if ( array_key_exists( $key, $this->attributes ) ) {
            return $this->attributes[$key];
        } else {
            return null;
        }
    }

    /**
     * Return true if a given attribute exists for this field container.
     * @param string $key
     * @return boolean;
     */
    public function hasAttribute( $key ) {
        return array_key_exists( $key, $this->attributes );
    }

    /**
     * Set a static attribute for this field container.
     * @param string $key
     * @param mixed $value
     */
    public function setStaticAttribute( $key, $value ) {
        if ( !array_key_exists( $this->name, self::$static_attrs ) ) {
            self::$static_attrs[$this->name] = array();
        }
        self::$static_attrs[$this->name][$key] = $value;
    }

    /**
     * Return the static attribute value for a given attribute.
     * @param string $key
     * @return mixed
     */
    public function getStaticAttribute( $key ) {
        if ( array_key_exists( $this->name, self::$static_attrs ) 
                && array_key_exists( $key, self::$static_attrs[$this->name] ) ) {
            return self::$static_attrs[$this->name][$key];
        } else {
            return null;
        }
    }

    /**
     * Return true if a given static attribute exists for this field container.
     * @param string $key
     * @return boolean;
     */
    public function hasStaticAttribute( $key ) {
        return ( array_key_exists( $this->name, self::$static_attrs ) 
                && array_key_exists( $key, self::$static_attrs[$this->name] ) );
    }


    /**
     * Set a meta attribute for this field container.
     * @param string $key
     * @param mixed $value
     */
    public function setMeta( $key, $value ) {
        $this->factory->setMetaAttribute($this->name,$key, $value);
    }

    /**
     * Return the meta attribute value for a given meta.
     * @param string $key
     * @return mixed
     */
    public function getMeta( $key ) {
        return $this->factory->getMetaAttribute($this->name,$key);
    }

    /**
     * Return true if a given meta attribute exists for this field container.
     * @param string $key
     * @return boolean;
     */
    public function hasMeta( $key ) {
        return $this->factory->hasMetaAttribute($this->name,$key);
    }


    
    /**
     * Set the name for this field container
     * @param string $name
     */
    public function setName( $name ) {
        $this->name = $name;
    }


    /**
     * Return the name for this field container
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Return the value for the type of this container
     * @return string or null on failure
     */
    public function getContainerType() {
        if (!$this->factory instanceof I2CE_FieldContainer_Factory) {
            return null;
        }
        return $this->factory->getContainerType();
    }


    /**
     * Get the container's index.  Either of the form "$containerName:$id" or "$containerName:0:$counter"
     * @returns string or null on failure
     */
    public function getContainerIndex()  {
        if (!$this->factory instanceof I2CE_FieldContainer_Factory) {
            return null;
        }
        return $this->factory->getContainerIndex($this);
    }



    /**
     * Return the value for the name attribute to be used for this form to be used as part of a request variable
     * @return string
     */
    public function getHTMLName() {
        if (!$this->factory instanceof I2CE_FieldContainer_Factory) {
            return null;
        }
        return $this->factory->getHTMLName($this);
    }



    /**
     * Check to see if the given field is in this container
     * @param string $field
     * @returns boolean
     */
    public function hasField($field) {
        return (is_string($field) && array_key_exists($field,$this->fields));
    }


    /*
     * Get the history of each field in this container.  May need to populate the history
     *
     * @param boolean $as_array.  Defaults to false in which case results are instances of I2CE_Entry, otherwise reusults are associative arrays
     * @returns array index by field names
     */
    public function getHistory($as_array =false) {
        $fields= $this->getFieldNames();
        $history = array();
        foreach ($fields as $field) {
            $fieldObj = $this->getField($field);
            if (!$fieldObj instanceof I2CE_FormField) {
                continue;
            }
            $fieldHistory = $fieldObj->getHistory($as_array);
            if (is_array($fieldHistory) && count($fieldHistory) > 0) {
                $history[$field] = $fieldHistory;
            } 
        }
        return $history;
    }

    /**
     * Get the names of all the fields added in this field container
     * @returns array of string, the field names.
     */
    public function getFieldNames() {
        return array_keys($this->fields);
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
        if ( array_key_exists( $field, $this->fields ) ) {
            return $this->fields[ $field ];
        } elseif ( $field == "id" ) {
            $form_field = new I2CE_FormField_STRING_LINE( "id", array( 'in_db' => false ) );
            $form_field->setValue( $this->getNameId() );
            $form_field->setContainer( $this );
            return $form_field;
        } else {
            return null;
        }
    }


    /**
     * Rewind the internal pointer for the {@link $fields} array for iterating through the  fields.
     */
    public function rewind() {
        reset( $this->fields );
    }
    /**
     * Return the current value for the {@link $fields} array for iterating through the  fields.
     * @return mixed
     */
    public function current() {
        return current( $this->fields );
    }
    /**
     * Return the current key for the {@link $fields} array for iterating through the  fields.
     * @return mixed
     */
    public function key() {
        return key( $this->fields );
    }
    /**
     * Advance the internal pointer for the {@link $fields} array for iterating through the fields.
     */
    public function next() {
        next( $this->fields );
    }
    /**
     * Check to see if the current internal pointer for the {@link $fields} array is valid.
     * @return boolean
     */
    public function valid() {
        return array_key_exists( key( $this->fields ), $this->fields );
    }


    /**
     * Create a new instance of a I2CE_FieldContainer object.
     * @param I2CE_FieldContainer_Factory $factory
     * @param string $name The name of this field container
     * @param integer $id
     */
    public function __construct($factory, $name, $id='0' ) {
        $this->factory = $factory;
        $this->fields = array();
        if (! is_array($this->attributes =$this->factory->loadMetaAttributes($name))) {
            $this->attributes = array();
        }        
        $this->setId($id);
        if ( !$name || $name == "" ) {
            I2CE::raiseError( "Blank name when creating a new " . get_class($this), E_USER_ERROR );
            return;
        }
        $this->setName( $name );
        $this->addFields($this->factory->getFieldData($name));
    }

    /**
     * Returns the array of attributes for this container
     * @returns array
     */
    protected function getAttributes() {
        return array();
    }

    /**
     * @var protected I2CE_FieldContainer_Factory $factory
     */
    protected $factory  = null;

    
    /**
     * Checks to see if the given field name is valid
     * @param string $fieldName
     * @returns boolean
     */
    protected function isValidFieldName($fieldName) {        
        return ($fieldName && $fieldName != 'id');
    }
    
    /**
     * Adds fields to the field container as indicated by the data arra
     * @param array $data.  An array with keys the name of a field and values an array of defining data.
     */
    protected function addFields($data) {
        //first we create all the fields and add them to ourself
        foreach ($data as $field=>$fieldData) {
            if (!$this->isValidFieldName($field)) {
                continue;
            }
            $this->addField( $field, $fieldData );
            if (!array_key_exists($field,$this->fields) || !$this->fields[$field] instanceof I2CE_FormField) {
                I2CE::raiseError("Could not add field $field to {$this->name}:" . implode(",",array_keys($this->fields)));
                unset($this->fields[$field]);
                continue;
            }
        }
        //now that we have created the fields we can set them up and refer to them
        foreach ($data as $field=>$fieldData) {
            if (!$this->isValidFieldName($field)) {
                continue;
            }
            if (!array_key_exists($field,$this->fields) || ! $this->fields[$field] instanceof I2CE_FormField) {
                I2CE::raiseError("Could not get field $field");
                continue;
            }
            if (!is_array($fieldData )) {
                $fieldData = array();
            }
            $this->fields[$field]->setDefaultOptions($fieldData);
        }
    }

    /**
     * Pre process the field args before setting them on the field
     * object.
     * @param string $field
     * @param array &$args
     */
    protected function preProcessFieldArgs( $field, &$args ) {
    }
    
    
    /**
     * Adds a field to this form.
     * @param string $name 
     * @param array $args The arguments for this field
     * @returns mixed I2CE_FormField or false on failure
     */
    public function addField( $name, $args ) { 
        if ( !array_key_exists( 'formfield', $args ) || !is_scalar($args['formfield']) ) {
            I2CE::raiseError("No formfield passed to addField in container {$this->name} with field {$name}\n" . print_r($args,true), E_USER_WARNING);
            return false;
        }        
        $this->preProcessFieldArgs( $name, $args );
        $field = I2CE_FormField::createField($args['formfield'], $name, $args);
        if (!$field instanceof I2CE_FormField) {
            I2CE::raiseError("Could not create field $name in {$this->name} in container {$this->name}");
            return false;
        }
        $name = $field->getName();
        if ( !$name || $name == "" ) {
            I2CE::raiseError( "Invalid field name for I2CE_Form::addField.", E_USER_NOTICE );
            return false;
        }
        $field->setContainer( $this );
        $this->fields[$name] = $field;
        return $field;
    }

    

    /**
     * Removes a field from this field container.
     * @param string $name 
     * @param array $args The arguments for this field
     */
    public function removeField($name) {
        if (is_scalar($name) && array_key_exists($name,$this->fields)) {
            unset($this->fields[$name]);
        }
    }
        


    



    /**
     * Return the {@link I2CE_FormField  field} value from the {@link $fields} array.
     * @param string $key
     * @return mixed
     */ 
    public function __get( $key ) {
        if ( array_key_exists( $key, $this->fields ) ) {
            return $this->fields[$key]->getValue();
        } else {
            return null;
        }
    }

    /**
     * Set the {@link I2CE_FormField  field} value in the {@link $fields} array.
     * @param string $key
     * @param mixed $value
     */
    public function __set( $key, $value ) {
        if (!is_array($this->fields)) {
            I2CE::raiseError("Internal error -- fields is not array");
            return false;
        }
        if ( array_key_exists( $key, $this->fields ) ) {
            $this->fields[$key]->setValue( $value );
        }
    }

    /**
     * Check to see if a {@link I2CE_FormField  field} value has been set.
     * @param string $key
     * @return boolean
     */
    public function __isset( $key ) {
        if ( array_key_exists( $key, $this->fields ) ) {
            return $this->fields[$key]->issetValue();
        } else {
            return false;
        }
    }

    /**
     * Unset a {@link I2CE_FormField field}.
     * @param string $key
     */
    public function __unset( $key ) {
        if ( array_key_exists( $key, $this->fields ) ) {
            $this->fields[$key]->unsetValue();
        }
    }

          

    /**
     * Clean up all the fields for this field container.
     * 
     * This will unset all the fields associated with this field container.  This will remove
     * all circular references to this field container so it can be cleaned up by the garbage collector.
     * This should only be called when the field container is no longer needed.  Trying to access it
     * after this may cause unexpected results or errors.
     * @param boolean $remove_from_cache.  Defaults to true in which case we remove it from the factory's field container cache as well
     */
    public function cleanup($remove_from_cache = true) {
        foreach( $this->fields as $key => $field ) {
            $field->cleanup();
            $field = null;
            unset( $this->fields[$key] );
        }   
        
        if ($this->id !== '0' && $remove_from_cache && $this->factory instanceof I2CE_FieldContainer_Factory) {
            $this->factory->removeFromCache($this);
        }
        $this->factory = null;
        $this->clearMethodCache();
    }

    /**
     * Remove fields from the container
     * 
     * This is to be used when only certain fields of the container are being dealt with so
     * the others can be removed to save processing.
     * @param array $fields The fields being worked with.
     * @param boolean $keep A flag to determine if the given fields should be removed or all except the given fields.
     */
    public function clearFields( $fields, $keep=true ) {
        if ( $keep ) {
            foreach( $this->fields as $key => $field ) {
                if ( !in_array( $key, $fields ) ) {
                    unset( $this->fields[$key] );
                }
            }
        } else {
            foreach( $fields as $key ) {
                unset( $this->fields[$key] );
            }
        }
    }


    
    /**
     * Load the member variables from an array
     * The array can contain the keys 'id', 'parent', 'fields'.  The later of which
     * is an array indexed by field names and which contains the values of the field
     * 
     * @param array $post 
     */
    public function setFromPost($post, $populate_on_set_id = false) {
        if (!is_array($post)) {
            return;
        }
        if (!array_key_exists('fields',$post) || !is_array($post['fields'])) {
            return;
        }
        if (array_key_exists('id',$post['fields']) && is_string($post['fields']['id'])) {
            if (strpos($post['fields']['id'], '|') === false) {
                I2CE::raiseError("Deprecated use of id");
                $id = $post['fields']['id'];
            } else {
                list($name,$id) = explode('|', $post['fields']['id'],2);
                if ($name !== $this->name) {
                    I2CE::raiseError("Name mismatch:" . $name . " != " . $this->name);
                    return false;
                }
            }
            if (strlen($id) == 0) {
                $id = '0';
            }
            if ($id !== '0') {
                $this->setID($id);
                if ($populate_on_set_id) {
                    $this->populate();
                }
            }
        }
        foreach ($this->fields as $field=>$fieldObj) {
            if (!array_key_exists($field,$post['fields'])) {
                continue;
            }
            $fieldObj->setFromPost($post['fields'][$field]);
        }
    }

    /**
     * Get the nested associative array that is used for post
     * @param array $field_names.  Array of string, the field names we wish to query.  Defaults to null in which we case we get all fields
     * @param boolean $skip_invalid. Defaults to true in which case we skip invalid values
     * @param boolean $include_id defaults to true
     * @return array
     */
    public function getPost($field_names = null, $skip_invalid = true, $include_id = true) {
        $post = array('fields'=>$this->getQueryFields($field_names,$skip_invalid,false));
        if ($include_id && $this->id !== '0') {
            $post['fields']['id'] = $this->getNameId();
        }
        return $post;
    }






    /**
     * Get the request variable in the format expected from {load()}
     */
    public function getLoad() {
        $indices = explode(':',$this->getHTMLName());
        $ret = array();
        $t_ret = &$ret;
        foreach ($indices as $index) {
            $t_ret[$index] = array();
            $t_ret = &$t_ret[$index];
        }
        $t_ret = $this->getPost();
        return $ret;
    }




    /**
     * Load the member variables from an array
     * 
     * @param array $post The Post vairables.  Usually from an http request.
     * @param boolean $loadID.  Defaults to true.  If true, and there is
     * exactly one of  entry in the array $post[<<NAME>>][$this->name], we set the values 
     * of the containerss field, id and parent from that entry.  If false, we set the
     * values from $post[<<TYPE>>][$this->name][$this->getID()]  if it is present
     */
    public function load($post, $loadID = true, $populate_on_set_id = true) {        
        $type = $this->getContainerType();
        if (!is_array($post)
            ||!array_key_exists($type,$post)
            ||!is_array($post[$type])
            ||!array_key_exists($this->name,$post[$type])
            ||!is_array($post[$type][$this->name])
            || count($post[$type][$this->name]) == 0){ 
            return;
        }
        if ($loadID) {
            $id = $this->getID();
            if ( ($id ===0 || is_null( $id ) || $id == "" || $id === '0')) {
                $id = '0';
            }
            if ($id === '0') {
                if (array_key_exists('0',$post[$type][$this->name])  && is_array($post[$type][$this->name]['0']) && count($post[$type][$this->name]['0']) > 0) {
                    if (count($post[$type][$this->name]['0']) > 1) {
                        I2CE::raiseError("Ambiguous form reference when setting anonymouse from post with loadid");
                        return;
                    } 
                    reset($post[$type][$this->name]['0']);
                    $this->setFromPost(current($post[$type][$this->name]['0']), $populate_on_set_id);
                }else{
                    if (count($post[$type][$this->name]) > 1) {
                        I2CE::raiseError("Ambiguous form reference when setting from post");
                    }
                    //$this->setId(key($post[$type][$this->name]));               
                    reset($post[$type][$this->name]);
                    $id = key($post[$type][$this->name]);
                    $vars = current($post[$type][$this->name]);
                    if (!array_key_exists('fields',$vars)) {
                        $vars['fields'] =array();
                    }
                    if (!array_key_exists('id',$vars['fields'])) {
                        $vars['fields']['id'] = $this->name . '|' . $id;
                    }
                    $this->setFromPost($vars, $populate_on_set_id);
                }
            } else {
                if (array_key_exists('0',$post[$type][$this->name])) {
                    $has_anon = 1; 
                } else {
                    $has_anon = 0;
                }
                if (count($post[$type][$this->name]) > 1 + $has_anon) {
                    I2CE::raiseError("Ambiguous form reference when setting from post");
                    return;
                } 
                $found = false;
                foreach ($post[$type][$this->name] as $id=>$vars) {
                    if ($id == '0') {
                        continue;
                    }
                    $found = true;
                    break;
                }
                if (!$found) {
                    I2CE::raiseError("Could not get non-anonymouse id");
                    return;
                }
                if (!array_key_exists('fields',$vars)) {
                    $vars['fields'] =array();
                }
                if (!array_key_exists('id',$vars['fields'])) {
                    $vars['fields']['id'] = $this->name . '|' . $id;
                } elseif ( $vars['fields']['id'] != $this->name . '|' . $id ) {
                    I2CE::raiseError( "Form ID mismatch!!" );
                    return;
                }
                $this->setFromPost($vars, $populate_on_set_id);
            }
        } else {
            $id = $this->getID();
            if ( ($id ===0 || is_null( $id ) || $id == "" || $id === '0')) {
                $id = '0';
            }
            if ($id === '0') {
                if (!array_key_exists('0',$post[$type][$this->name])
                    ||!is_array($post[$type][$this->name]['0'])) {
                    return;
                }            
                if (count($post[$type][$this->name]['0']) == 0) {
                    return;
                }
                if (count($post[$type][$this->name]['0']) > 1) {
                    I2CE::raiseError("Ambiguous form reference when setting anonymously from post");
                    return;
                }
                reset($post[$type][$this->name]['0']);
                $this->setFromPost(current($post[$type][$this->name]['0']),false);               
            }else {
                if (!array_key_exists($this->getID(),$post[$type][$this->name])
                    ||!is_array($post[$type][$this->name][$this->getID()])) {
                    return;
                }            
                $this->setFromPost($post[$type][$this->name][$this->getID()],$populate_on_set_id);
            }
        }
    }





    /**
     * Return all the fields as a query string to be sent to a URL
     * @param array $field_names.  Array of string, the field names we wish to query.  Defaults to null in which we case we get all fields
     * @param boolean $skip_invalid. Defaults to true in which case we skip invalid values
     * @return string
     */
    public function getQueryString($field_names = null, $skip_invalid = true) {
        $query_str = "";
        if (!is_array($field_names)) {
            $field_names = array_keys($this->fields);
        }
        foreach( $field_names as $field_name) {
            if (!array_key_exists($field_name,$this->fields) || !( $fieldObj = $this->fields[$field_name]) instanceof I2CE_FormField) {
                continue;
            }
            $valid = $fieldObj->isValid();
            if ( $skip_invalid && ! $valid) {
                continue;
            }
            if ($valid) {
                $val = urlencode( $fieldObj->getDBValue() );
            } else {
                $val = '';
            }
            $query_str .= "&" . $fieldObj->getHTMLName() . "=" . $val;
        }
        return $query_str;
    }

    /**
     * Return all the fields as an associative array
     * @param array $field_names.  Array of string, the field names we wish to query.  Defaults to null in which we case we get all fields
     * @param boolean $skip_invalid. Defaults to true in which case we skip invalid values
     * @param boolean $html_name.  Use the html name as teh key in the returned array.  Defaults to true.  If false we use the field name
     * @return array
     */
     public function getQueryFields($field_names = null, $skip_invalid = true, $html_name = true) {
        $query = array();
        if (!is_array($field_names)) {
            $field_names = array_keys($this->fields);
        }
        foreach( $field_names as $field_name) {
            if (!array_key_exists($field_name,$this->fields) || !( $fieldObj = $this->fields[$field_name]) instanceof I2CE_FormField) {
                continue;
            }
            $valid = $fieldObj->isValid();
            if ( $skip_invalid && ! $valid) {
                continue;
            }
            if ($valid) {
                $val = $fieldObj->getDBValue() ;
            } else {
                $val = '';
            }
            if ($html_name) {
                $query[$fieldObj->getHTMLName()] = $val;
            } else {
                $query[$field_name] = $val;
            }
        }
        return $query;
    }
    /**
     * Return the values of all the fields that are set.
     * @param array $field_names.  Array of string, the field names we wish to query.  Defaults to null in which we case we get all fields
     * @param boolean $skip_invalid. Defaults to true in which case we skip invalid values
     * @return string
     */
    public function getQueryDisplay($field_names = null, $skip_invalid =true) {
        $display = array();
        if (!is_array($field_names)) {
            $field_names = array_keys($this->fields);
        }
        foreach( $field_names as $field_name) {
            if (!array_key_exists($field_name,$this->fields) || !( $fieldObj = $this->fields[$field_name]) instanceof I2CE_FormField) {
                continue;
            }
            $valid = $fieldObj->isValid();
            if ($skip_invalid &&  ( !$fieldObj->isInDB() || !$valid) ) {
                continue;
            }
            if ($valid) {
                $val = $field->getDisplayValue();
            } else {
                $val = '';
            }
            $display[] = $val;
        }
        if ( sizeof( $display ) > 0 ) {
            return implode( ", ", $display );
        } else {
            return "All";
        }       
    }
    


    /**
     * Validate all fields that are marked as required or unique.
     *
     * This will check all the fields in this form and if they're required or unique it
     * will perform the required checks 
     */
    public function validate() {
        I2CE_ModuleFactory::callHooks( "validate_form", $this );
        I2CE_ModuleFactory::callHooks( "validate_form_" . $this->getName(), $this );
        foreach( $this->fields as $field_name => $field_obj ) {
            I2CE_ModuleFactory::callHooks( "validate_formfield",  $field_obj );
            I2CE_ModuleFactory::callHooks( "validate_form_" . $this->getName() . "_field_" . $field_name, $field_obj );

        }
        

    }
        


    /**
     * Sets an invlalid message on the named field of a form
     * @param string $fieldName
     * @param array $extra Any extra information to be formatted and displayed.
     *
     */
    public function setInvalidMessage($fieldName,$messageName,$extra = null) {
        if (! ($fieldObj = $this->getField($fieldName)) instanceof I2CE_FormField) {
            I2CE::raiseError("Invalid call");
            return false;
        }
        $fieldObj->setInvalidMessage($messageName,$extra);
    }
    
        
    /**
     *Checks to see if any fields of form has in invalid message
     * @returns boolean
     */
    public function hasInvalid() {
        $invalid = false;
        foreach ($this->fields as $field_obj) {
            $invalid |= $field_obj->hasInvalid();
        }
        return $invalid;
    }
    
    
    
    /**
     * Check to see if the named field is valid
     * @param string $field
     * @return boolean;
     */
    public function isValid($field) {
        return (array_key_exists($field,$this->fields) && $this->fields[$field] instanceof I2CE_FormField && $this->fields[$field]->isValid());
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
