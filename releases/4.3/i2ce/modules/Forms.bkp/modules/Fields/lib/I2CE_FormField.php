<?php
/*
 * © Copyright 2007, 2008, 2009 IntraHealth International, Inc.
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
    * @since v2.0.0
    * @version v2.0.0
    */
/**
 * Class for defining all the database fields used by a {@link I2CE_Form} object.
 * @package I2CE
 * @access public
 */
abstract class I2CE_FormField extends I2CE_Fuzzy{


    public function loadFromXML($node) {
        if (!$node instanceof DOMNode) {
            return;
        }
        $this->setValue($node->textContent);
    }

    /**
     * Get an XML representation of the data
     * @param boolean $as_node.  Defaults to true if true, then we return DOMNode otherwise we return string representation of the generate node
     * @param DOMNode $append_node.  If DOMNode then XMLData is appended onto the given node.
     */
    public function getXMLRepresentation($as_node = true,$append_node =null) {
        if ($append_node instanceof DOMNode) {
            $doc = $append_node->ownerDocument;
            $field_node = $doc->createElement("field");
            $append_node->appendChild($field_node);            
        } else {
            $doc  = new DOMDocument();
            $doc->loadXML('<field/>');
            $append_node = $doc->documentElement;
            $field_node = $doc->documentElement;            
        }
        $field_node->setAttribute('type',$this->formfield);
        $field_node->setAttribute('name',$this->name);
        $this->appendXMLRepresentation($field_node);
        if ($as_node) {
            return $field_node;
        } else {        
            return $doc->saveXML($field_node);
        }
    }
    /**
     * Appends an XML representation of the field data onto the current node
     * @param DOMNode $field_node the node we are appending the representation onto
     */
    protected function appendXMLRepresentation($field_node) {
        $doc = $field_node->ownerDocument;
        $val_node = $doc->createTextNode($this->getDBValue());
        $field_node->appendChild($val_node);
    }
    

    /**
     * Cleanup to remove circular references to container to field to container to field to container to field to cotainer to field
     */
    public function cleanup() {
        $this->container = null;
        $this->clearMethodCache();
    }


    /**
     * A string type for the field 
     */
    const FIELD_TYPE_I2CE = "string";
    /**
     * The database type for the field
     */
    const FIELD_TYPE_DB = "varchar( 255 ) COLLATE utf8_bin default NULL";


    /**
     * @var string The name of this field.
     */
    protected $name;
    /**
     * @var array An associative array with all the options for this form field.
     */
    protected $options;
    /**
     * @var mixed The value of this field.
     */
    protected $value;
    /**
     * @var array A list of headers for this form field.
     */
    protected $headers;
    /**
     * @var string A URL to be used as a link for this field when displaying the value.
     */
    protected $href;
    /**
     * @var I2CE_FieldContainer A reference to the field conatiner object this field is a part of.
     */
    protected $container;
    /**
     * @var array A list of {@link I2CE_Entry} objects for all entries for this field.
     */
    protected $history;
    /**
     * @var integer The index for cycling through all historical entries.
     */
    protected $history_idx;
    /**
     * @var array A list of attributes for this FormField instance.
     */
    protected $attributes;

    /**
     * @var array A list of static attributes for this FormField.
     */
    protected static $static_attrs = array();


    public $formfield = false;
    /**
     * Instantiantiate a field given its short class name
     * @param string $formfield the short name of a form field (e.g. STRING_LINE or DATE_YMD)
     * @param string $name The name of the field
     * @param array $args.  Defaults to empty array.  The field arguments passed to the constructor
     * @returns mixed I2CE_FormField  or false on failure
     */
    public static function createField($formfield, $name, $args = array()) {
        $i2ce_config = I2CE::getConfig()->modules->forms;
        if (!$i2ce_config->is_scalar("FORMFIELD/$formfield")) {
            I2CE::raiseError("Form field type ($formfield) has not been registered");
            return false;
        }
        $classname = $i2ce_config->FORMFIELD->$formfield;
        if (!class_exists($classname)) {
            I2CE::raiseError("$formfield is assoicated to class $classname which cannot be found");
            return false;
        }
        @$field = new $classname( $name, $args );
        if (! $field instanceof I2CE_FormField) {
            I2CE::raiserError("$formfield is not associated to an instance of I2CE_FormField");
            return false;  
        }
        $field->formfield = $formfield;
        return $field;
    }




    /**
     * Add or replace an entry in the {@link invalid} array.
     * 
     * The format for the $extra parameter is:
     * array( "URL" => array( 'id' => 'display' ) );
     * The id value will be appended to the URL and the display part will be displayed as the link text.
     * @param string $message The error message to display.
     * @param array $extra Any extra information to be formatted and displayed.
     */
    public function setInvalid(  $message, $extra = null) {        
        if (  $extra  ) {
            $this->invalid = array( 'message' => $message, 'extra' => $extra );             
        } else {
            if (!is_string ($this->invalid)) {
                $this->invalid = '';
            }
            $this->invalid .= $message;
        }
    }

    /**
     * Sets an invalid message on a form field
     * @param string $messageName
     * @param array $extra Any extra information to be formatted and displayed.
     * @param string $additional_message Any additional message to be appended.
     */
    public function setInvalidMessage($messageName,$extra = null,$additional_message='') {
        if ( ! ($formObj = $this->getContainer()) instanceof I2CE_FieldContainer
            || !I2CE_MagicDataNode::checkKey($fieldName = $this->getName())
            || !I2CE_MagicDataNode::checkKey($formName = $formObj->getName())
            || !I2CE_MagicDataNode::checkKey($messageName)) {
            I2CE::raiseError("Invalid call");
            return false;
        }
        $msg = '';
        
        $msg_paths  = array(
            "/modules/forms/formfield_messages/" .$formName . '/' . $fieldName . '/' . $messageName,
            "/modules/forms/field_messages/" . $fieldName . '/' . $messageName,
            "/modules/forms/invalid_field_messages/" . $messageName
            );
        foreach ($msg_paths as $msg_path) {
            if (I2CE::getConfig()->setIfIsSet($msg,$msg_path) && $msg) {
                $found = true;
                break;
            }
        } 
        if ( !$msg) {
            I2CE::raiseError("No message at any of:\n" . implode("\n",$msg_paths));
            $msg =  $messageName;
        }
        $this->setInvalid($msg.$additional_message, $extra);
    }


    /**
     * Checks to see if there are any entries in the {@link invalid} array.
     * @returns boolean
     */
    public function hasInvalid() {
        if (is_string($this->invalid) && strlen($this->invalid) > 0) {
            return true;
        }
        if (is_array($this->invalid) ) {
            return count($this->invalid) > 0;
        }
        return false;
    }
                

    /**
     * Return the invalid information.
     * @return array
     */
    public function getInvalid() {
        return $this->invalid;
    }

    /**
     * error messages to be displayed when a form is invalid.
     * @var mixed.  string or array of "extra" {@link setInvalid}
     */
    protected $invalid;
 



   /**
     * Create a new instance of a I2CE_FormField
     * @param string $name
     * @param array $options A list of options for this form field.  The keys used are:  in_db, required, 
     *  unique and unique_field
     */
    public function __construct( $name, $options=array() ) { 
        $this->name = $name;
        $this->options = $options;
        if ( !array_key_exists( 'in_db', $this->options ) ) {
            $this->options['in_db'] = true;
        }
        if ( !array_key_exists( 'required', $this->options ) ) {
            $this->options['required'] = false;
        }
        if ( !array_key_exists( 'unique', $this->options ) ) {
            $this->options['unique'] = false;
        }
        $this->attributes = array();
        $this->history = array();
        $this->history_idx = 0;
        $this->href = false;
        $this->postfix = '';
        $this->headers = array();
    }

    /**
     * Sets the default options, headers etc. from the field data
     * @param arary $fieldData
     */
    public function setDefaultOptions($fieldData) {
        if (!is_array($fieldData)) {
            I2CE::raiseError("Bad field data");
            return;
        }
        if (array_key_exists('headers',$fieldData) && is_array($fieldData['headers'])) {
            $this->setHeaders( $fieldData['headers']);
        }
        $ret = $this->getDefaultValue($fieldData);
        if (!is_array($ret) || count($ret) != 2) {
            I2CE::raiseError("Invalid data returned when getting default value");
        } else {
            list($value_set,$this->default_value) = $ret;
            if ($value_set) {
                $this->setValue($this->default_value);
            }
        }
    }
    protected $default_value;


    public function resetDefaultValue() {
        $this->setValue($this->default_value);
    }


    /**
     * Gets the default value from the field's data
     * @param array $fieldData
     * @returns array where the first element is boolean (true if the has been a default value set, false otherwise) and the 
     * second element is the default value to be set.
     */
    protected function getDefaultValue($fieldData) {
        if (array_key_exists('default_eval',$fieldData) && is_string($fieldData['default_eval']) && strlen($fieldData['default_eval']) > 0) {
            $default_value = null;       
            $default_eval = $fieldData['default_eval'];
            if (false === eval ( '$default_value = ' . $default_eval . ";" )) {
                I2CE::raiseError("When adding field " . $this->getHTMLName() . " could not evaluate {$default_eval}");                
                return array(false,null);
            }
            return array(true,$default_value);
        }
        if ( array_key_exists('default_value',$fieldData) && is_scalar($fieldData['default_value'])) {
            return array(true,$this->getFromDB($fieldData['default_value']));
        }
        return array(false,null);
    }


    /**
     * Return the string name for a given type.
     * @return string
     */
    public function getTypeString() {
        return eval( 'return ' . get_class( $this ) . '::FIELD_TYPE_I2CE;' );
    }

    /**
     * Return the DB field type for this type.
     * @return string
     */
    public  function getDBType() {
        return eval( 'return ' . get_class( $this ) . '::FIELD_TYPE_DB;' );
    }
        
    /**
     * Return the value of this field from the database format for the given type
     * @param mixed $value
     */
    public function getFromDB( $value ) {
        return $value;
    }

    /**
     * Returns the field name of this field.
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    /**
     * Return the type string for this field.
     * @return string
     */
    public function getString() {
        return $this->getTypeString();
    }
    /**
     * Checks to see if this field is saved in the database.
     * @return boolean
     */
    public function isInDB() {
        return $this->options['in_db'];
    }
    
    /**
     * Check if a named option exists
     * @param string $option
     * @returns boolean
     **/
    public function hasOption($option) {
        return (array_key_exists($option,$this->options) && $this->options[$option] !== null);
    }
    /**
     * set a named option exists
     * @param string $option
     * @param mixed $val
     **/
    public function setOption($option,$val) {
        if (is_scalar($option)) {
            $option = array($option);
        }
        if (!is_array($option)) {
            return;
        }
        $options =  &$this->options;
        foreach ($option as $key) {
            if (!is_array($options)) {
                $options = array();
            }
            if (!array_key_exists($key,$options)) {
                $options[$key] = null;
            }
            $options = &$options[$key];
        }
        $options = $val;
    }
    /**
     * Get the value stored at a named option exists
     * @param string $option
     * @returns mixed
     **/
    public function getOption($option) {
        if (is_scalar($option)) {
            $option = array($option);
        }
        if (!is_array($option)) {
            return;
        }
        $options = $this->options;
        foreach ($option as $key) {
            if (!is_array($options) || !array_key_exists($key,$options)) {
                return null;
            }
            $options = $options[$key];
        }
        return $options;
    }



    /**
     * Sets the field container object for this field.
     * @param I2CE_FieldContainer $container
     */
    public function setContainer( $container ) {
        if (!$container instanceof I2CE_FieldContainer) {
            return;
        }
        $this->container = $container;
    }


    public function optionsHasPath($path) {
        $options = $this->options;
        if (is_string($path)) {
            $path = explode('/',$path);
        } 
        if (!is_array($path)) {
            return false;
        }
        while(count($path) > 0) {
            if (!is_array($options)) {
                return false;
            }
            $p = array_shift($path);
            if (!array_key_exists($p,$options)) {
                return false;
            }
            $options = $options[$p];
        }
        return true;
    }

    public function getOptionsByPath($path) {
        $options = $this->options;
        $path = explode('/',$path);
        while(count($path) > 0) {
            if (!is_array($options)) {
                return null;
            }
            $p = array_shift($path);
            if (!array_key_exists($p,$options)) {
                return null;
            }
            $options = $options[$p];
        }
        return $options;
    }



    /**
     * Checks to see if the given display type is registed for this form field.
     * @param string $type.  The display type.    'default' always returns true
     * @returns boolean
     */
    public function hasDisplay($type) {
        if ($type == 'default') {
            return true;
        }
        if (!is_string($type) || strlen($type) == 0) {
            return false;
        }
        return $this->optionsHasPath("meta/display/$type");
    }


    /**
     * Get the display styles registered for this form field.  Will always include 'default'
     * @returns array
     */
    public function getDisplays() {
        $displays =  array('default');
        if (!$this->optionsHasPath("meta/display")) {
            return $displays;
        }
        $disps = $this->getOptionsByPath("meta/display");
        if (!is_array($disps)) {
            return $displays;
        }
        return array_keys($disps);
    }


    /**
     * Returns the form object for this field.
     */
    public function getContainer() {
        return $this->container;
    }

    /**
     * @var string The HTML Name for this form field.
     */
    protected $HTMLName;

    /**
     * Set the HTML Name for this form field.
     * @param string $name
     * @param string $prefix
     */
    public function setHTMLName( $name = '', $prefix='fields' ) {
        if ( !$this->HTMLName || !is_array( $this->HTMLName ) ) {
            $this->HTMLName = array();
        }
        if ( $name == '' ) {
            if ( !array_key_exists( $prefix, $this->HTMLName ) ) {
                if ($this->container instanceof I2CE_FieldContainer) {
                    $this->HTMLName[$prefix] = $this->container->getHTMLName() .  "[$prefix][" .$this->name . ']'; 
                } else {
                    $this->HTMLName[$prefix] = "[$prefix][" . $this->name . ']';
                }
            }
        } else {
            $this->HTMLName[$prefix] = $name;
        }
    }
        
    /**
     * Return the value for the name attribute to be used for this field in a form.
     * @return string
     */
    public function getHTMLName($prefix = 'fields') {
        if ( !$this->HTMLName || !is_array( $this->HTMLName ) || !array_key_exists( $prefix, $this->HTMLName ) ) {
            $this->setHTMLName( '', $prefix );
        }
        return $this->HTMLName[$prefix];
        /*
        if ($this->container instanceof I2CE_FieldContainer) {
            return $this->container->getHTMLName() .  "[$prefix][" .$this->name . ']'; 
        } else {
            return '[fields][' . $this->name . ']';
        }
        */
    }



    /**
     * Sets the value of this field.
     * @param mixed $value
     */
    public function setValue( $value ) {
        $this->value = $value;
    }
    /**
     * Sets the value of this field from the database format.
     * @param mixed $value
     */
    public function setFromDB( $value ) {
        $this->value = $this->getFromDB( $value );
    }

    /** 
     * Sets the value of this field from the posted form.
     * @param mixed $post.  
     */
    public function setFromPost( $post) {
        $this->setFromDB($post);
    }
        
    /**
     * Returns the value of this field.
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }
        
    /** 
     * Returns the value of this field ready to be stored in the database.
     * @return mixed
     */
    public function getDBValue() {
        return $this->getValue();
    }

    /**
     * Returns the value of this field as a human readable format.
     * @param I2CE_Entry $entry If a I2CE_Entry object has been passed to this method then it will return the value for that entry assuming it's an 
     * entry for this field.
     * @return mixed
     */
    public function getDisplayValue( $entry=false,$style='default' ) {
        if ( $entry instanceof I2CE_Entry ) {
            $value = $entry->getValue();
        } else {
            $value = $this->getValue();
        }
        return $value;
    }
        
    /**
     * Check to see if the given DB value is equivalent to this value.
     * @param mixed $db_value Either a DB Value or an I2CE_FormField
     * @return boolean
     */
    public function isSameValue( $db_value ) {
        if ($db_value instanceof I2CE_FormField) {
            $db_value = $db_value->getDBValue();
        }
        if ( $db_value == $this->getDBValue() ) {
            return true;
        } else {
            return false;
        }
    }



    /**
     * Compares this form field agains the given form field.
     * @param mixed $db_value Either a DB Value or an I2CE_FormField
     * @returns -1,0,1
     */
    public function compare($db_value) {
        if ($db_value instanceof I2CE_FormField) {
            $db_value = $db_value->getDBValue();
        }
        if (!is_string($db_value)) {
            return 0;
        }
        //by default treat things as string based on their DB Value
        return strcmp($this->getDBValue(),$db_value);
    }
    
    
    /**
     * Checks to see if the current value for this is set and valid.
     * @return boolean
     */
    abstract public function isValid();


    /**
     * Checks to see if the value has been set.
     * @return boolean
     */
    public function issetValue() {
        return isset( $this->value );
    }
    /**
     * Unsets the value of this field.
     */
    public function unsetValue() {
        //unset( $this->value );
        $this->value = null;
    }
        
    /**
     * Set the headers for this form field.
     * @param array $headers
     */
    public function setHeaders( $headers ) {
        foreach( $headers as $type => $header ) {
            if (is_scalar($header)) {
                $this->headers[$type] = $header;
            }
        }
    }
    /**
     * Check to see if the given header type exists
     * @param string $type
     */
    public function hasHeader( $type="default" ) {
        return array_key_exists( $type, $this->headers );
    }
    /**
     * Return the given header by type
     * @param string $type
     */
    public function getHeader( $type="default" ) {
        if ($this->hasHeader($type)) {
            return $this->headers[$type];
        } else {
            return null;
        }
    }

        
    /**
     * Add a history entry to this field.
     * @param I2CE_Entry $entry The historical entry
     */
    public function addHistory( $entry ) {
        $this->history_idx = 0;
        $this->history[] = $entry;
        usort($this->history,array('I2CE_FormField','compareEntryDate')); //make sure it is stays sorted 
    }
    
    /**
     *Comparission function on  the dates of two entrys
     * @param I2CE_Entry $entry1
     * @param I2CE_Entry $entry2
     * @returns int -1,0,1
     */
    public static function compareEntryDate($entry1,$entry2) {
        if (!$entry1 instanceof I2CE_Entry || !$entry1->date instanceof I2CE_Date) {
            if (!$entry2 instanceof I2CE_Entry ||  !$entry2->date instanceof I2CE_Date) {
                return 0;
            }
            return -1;
        }
        if (!$entry2 instanceof I2CE_Entry  || !$entry2->date instanceof I2CE_Date) {
            return 1;
        }
        return $entry1->date->compare($entry2->date);
    }
        
    /**
     * Check to see if there are remaining DBEntry elements in {@link history} array.
     * @return boolean
     */
    public function hasNextHistory() {
        if ( $this->history_idx < count( $this->history ) ) {
            return true;
        } else {
            return false;
        }
    }
        
    /**
     * Reset the {@link history_idx history index} and return the first element
     * @return DBEntry
     */
    public function firstHistory() {
        $this->history_idx = 0;
        return $this->nextHistory();
    }
        
    /**
     * Return the next DBEntry element from the {@link history} array.
     * @return DBEntry
     */
    public function nextHistory() {
        return $this->history[ $this->history_idx++ ];
    }

    /**
     * Get the full history for this field.  You may need to call populateHistory() before using this.
     * @param boolean $as_array.  If true, return an associative array  the history.  Defaults to false in which
     * case we return an array of entry.
     * @returns mixed.
     */
    public function getHistory($as_array = false) {
        if (!$as_array) {
            return $this->history;
        } 
        $ret = array();            
        $current_value = $this->value;
        foreach ($this->history as $entry) {
            if (!$entry instanceof I2CE_Entry) {
                continue;
            }
            $data =  $entry->getAsArray();
            $this->value = $data['value'];
            $data['dbvalue'] = $this->getDBValue();
            unset($data['value']);
            $ret[] = $data;
        }
        $this->value = $current_value;
        return $ret;
    }
        
    
    
    /**
     * Set the URL for this field
     * @param string $href
     */
    public function setHref( $href ) {
        $this->href = $href;
    }
        
    /**
     * Return the URL to be used as a link for this field for display.
     * @return string
     */
    public function getHref() {
        if ( $this->href ) {
            return $this->href . $this->getDBValue();
        }  else {
            return false;
        }
    }

    /**
     * Return the display value of this form field as a DOM Node.
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @return DOMNode
     */
    public function getDisplayNode( $node,$template ) {
        $text_node = $template->createTextNode( $this->getDisplayValue() );
        if ( ($href = $this->getHref()) ) {
            $link_node = $template->createElement( "a", array( "href" => $href ) );
            $link_node->appendChild( $text_node );
            return $link_node;
        } else {
            return $text_node;
        }
    }


    protected function withinForm($node) {
        if (!$node instanceof DOMNode) {
            return false;
        }
        do {
            if ($node instanceof DOMElement && $node->tagName == 'form') {
                return true;
            }
        } while (($node = $node->parentNode) instanceof DOMNode);
        return false;
    }


    /**
     * Process this form field as a DOM element to place it in the template at the given node.
     *
     * @param DOMNode $node
     * @param I2CE_Template $template
     */
    public function processDOM($node,$template) {
        $i2ce_config = I2CE::getConfig()->modules->forms; 
        $ele_name = $this->getHTMLName();
        $showForm = $this->withinForm($node);
        if ( $node->hasAttribute( "href") ) {
            //This is a link
            if ($node->hasAttribute("no_value")) {
                $href_link = $node->getAttribute( "href" );
            } else {
                $href_link = $node->getAttribute( "href" ) . $this->getDBValue();
            }
            if ( $node->hasAttribute( "parent" ) && $this->getContainer() instanceof I2CE_Form) { //needs to be handled better
                $href_link .= "&parent=" . $this->getContainer()->getParent();
            }
            $href = $template->createElement( "a", array( "href" => $href_link ) );
            if ($node->attributes instanceof DOMNamedNodeMap) {
                foreach ($node->attributes as $name=>$attrNode) {
                    if ($name == 'href' || !$attrNode instanceof DOMAttr) {
                        continue;
                    }
                    $href->setAttribute($name,$attrNode->value);
                }
            }
            $node->parentNode->replaceChild( $href, $node );
            while ($node->firstChild instanceof DOMNode) {
                $href->appendChild($node->firstChild);
            }
            $node = $href;
        } elseif ( $node->hasAttribute( "head" ) || $node->hasAttribute( "showhead" ) ) {
            if ( $node->hasAttribute( "head" ) ) {
                $head = $node->getAttribute( "head" );
            } else {
                $head_type = $node->getAttribute( "showhead" );
                if ( $this->hasHeader( $head_type )) {
                    $head = $this->getHeader($head_type);
                } elseif ( $this->hasHeader() ) {
                    $head = $this->getHeader();
                } else {
                    $head = ucfirst( $this->getName() );
                }
            }
            $display = 'default';
            if ( $node->hasAttribute( "display" ))  {
                $t_display = $node->getAttribute('display');
                if (strtolower($t_display) != 'true' && strtolower($t_display) != 'false') {
                    if (isset($i2ce_config->template_displays->$t_display)) {
                        $display = $t_display;
                    }
                } else {
                    $display = strtolower( $t_display );
                }
            }
            if ( $showForm ) {
                $template_file = 'form_field.html';
                $template_file_root = 'p';
                $i2ce_config->setIfIsSet($template_file,"template_displays/default/showForm/field");
                $i2ce_config->setIfIsSet($template_file,"template_displays/$display/showForm/field");
                if ($display !== 'default') {
                    $i2ce_config->setIfIsSet($template_file_root,"template_displays/default/showForm/field_root");
                    $i2ce_config->setIfIsSet($template_file_root,"template_displays/$display/showForm/field_root");
                }
            } else {
                $template_file = 'display_field.html';
                $template_file_root = 'tr';
                $i2ce_config->setIfIsSet($template_file,"template_displays/default/displayed/field");
                $i2ce_config->setIfIsSet($template_file,"template_displays/$display/displayed/field");
                if ($display !== 'default') {
                    $i2ce_config->setIfIsSet($template_file_root,"template_displays/default/displayed/field_root");
                    $i2ce_config->setIfIsSet($template_file_root,"template_displays/$display/displayed/field_root");
                }
            }
            $form_node = $template->loadFile($template_file,$template_file_root);
            $node->parentNode->replaceChild( $form_node, $node );
            foreach (array('class','auto_link') as $a) { //I hate having an explicit reference to auto_link here :-(
                if ( $node->hasAttribute( $a ) ) {
                    $form_node->setAttribute($a, $node->getAttribute($a) );
                }
            }
            $title_attr = null;
            if ( $node->hasAttribute( "title" ) ) {
                $title_attr = $node->getAttribute("title");
            } elseif ( $node->hasAttribute("showtitle") ) {
                $title_type = $node->getAttribute("showtitle");
                if ( $this->hasHeader($title_type) ) {
                    $title_attr = $this->getHeader( $title_type );
                } elseif ( $this->hasHeader("title") ) {
                    $title_attr = $this->getHeader( "title" );
                }
            }
            if ( $title_attr !== null ) {
                $form_node->setAttribute( "title", $title_attr );
            }
            $field_head_class = 'field_head';
            $form_field_class = 'form_field';
            $i2ce_config->setIfIsSet($field_head_class,"template_displays/default/field_head_class");
            $i2ce_config->setIfIsSet($form_field_class,"template_displays/default/form_field_class");            
            if ($display !== 'default') {
                $i2ce_config->setIfIsSet($field_head_class,"template_displays/$display/field_head_class");
                $i2ce_config->setIfIsSet($form_field_class,"template_displays/$display/form_field_class");            
            }
            $head_node = $template->query("descendant::*[@class='"  . 
                                          $field_head_class . "']"
                                          , $form_node);
            if ($head_node->length > 0) {
                $head_node = $head_node->item(0);
            } else {
                $head_node = false;
            }

            $field_node = $template->query("descendant::*[@class='"  . 
                                           $form_field_class . "']", 
                                           $form_node);
            if ($field_node->length >0 ) {
                $field_node = $field_node->item(0);
            } else {
                $field_node = false;
            }
            if ($field_node) {
                $field_name = $this->getContainer()->getName() . '_'  . $this->getName();
                $template->addClass($field_node,$field_name);
                while ($node->hasChildNodes()) {
                    $field_node->appendChild($node->childNodes->item(0));
                }
                foreach ($node->attributes as $name=>$attrNode) {
                    if ( in_array( $name, array( 'display', 'title', 'showtitle', 'head', 'showhead' ) ) ) {
                        continue;
                    }
                    if (!$field_node->hasAttribute($name)) {
                        $field_node->setAttribute($name,$attrNode->value);
                    }
                }

            }
            if ($head_node) {
                $head_node->appendChild( $template->createElement( "label", array( "for" => $ele_name ), $head ) );
            }
            if ( $head_node && $node->hasAttribute("showhelp") ) {
                $title_type = $node->getAttribute("showhelp");
                if ( $this->hasHeader($title_type) ) {
                    $title_attr = $this->getHeader( $title_type );
                    $head_node->appendChild($template->createElement("span",array('title'=>$title_attr,'class'=>'fieldhelp')," (?) "));
                }
            }
            if ( !$showForm) {
                if ($field_node) {
                    $field_node->appendChild( $this->getDisplayNode( $node,$template ) );
                }
            } elseif ( $display == 'true') {
                if ($field_node) {
                    $field_node->appendChild( $this->getDisplayNode( $node,$template ) );
                }
            } elseif ( $template->isReview() 
                       || ( $node->hasAttribute( "noedit" ) &&$node->getAttribute('noedit')&& $this->issetValue() ) 
                       || ($node->hasAttribute('noedit') && $node->getAttribute('noedit')=='strict')) {
                if ($field_node) {
                    $processorNotEditable = 'processDOMNotEditable';
                    if ($node->hasAttribute('display') && ($this->_hasMethod('processDOMNotEditable_' . $node->getAttribute('display')))) {
                        $processorNotEditable='processDOMNotEditable_' . $node->getAttribute('display');
                    }
                    if ($node->hasAttribute('display')) {
                        $field_node->setAttribute('display',$node->getAttribute('display'));
                    }
                    $this->$processorNotEditable( $field_node, $template, $node );
                }
            } else {
                $this->setElement(false);
                $this->processHeaderEditable($template,$node,$head_node);
                if ($field_node) {
                    if ($this->hasInvalid()) {
                        $this->displayInvalid($template,$field_node);
                    }
                    $processorEditable = 'processDOMEditable';
                    if ($node->hasAttribute('display') && ($this->_hasMethod('processDOMEditable_' . $node->getAttribute('display')))) {
                        $processorEditable='processDOMEditable_' . $node->getAttribute('display');
                    }
                    $postprocessorEditable = 'postprocessDOMEditable';
                    if ($node->hasAttribute('display') && ($this->_hasMethod('postprocessDOMEditable_' . $node->getAttribute('display')))) {
                        $postprocessorEditable='postprocessDOMEditable_' . $node->getAttribute('display');
                    }
                    $this->$processorEditable( $field_node, $template,  $node );                
                    $this->$postprocessorEditable( $field_node, $template,  $node );                
                }
                $element = $this->getElement();
                if ($element) {
                    if ( $this->hasInvalid()) {
                        $class = "error";
                        if ( $element->hasAttribute( "class" ) ) {
                            $class .= " " . $element->getAttribute( "class" );
                        }
                        $element->setAttribute( "class", $class );
                    }
                    if ( $node->hasAttribute( "onchange" ) ) {
                        $element->setAttribute( "onchange", $node->getAttribute( "onchange" ) );
                    }
                    if ( $node->hasAttribute( "onfocus" ) ) {
                        $element->setAttribute( "onfocus", $node->getAttribute( "onfocus" ) );
                    }

                }
            }
        } else { 
            if ( (!$showForm || $node->hasAttribute("noedit") )) {
                // Just replacing the node text with the value in the database. 
                $node->appendChild( $this->getDisplayNode( $node,$template ) );
            } else {
                $options =  array( "name" => $ele_name,
                                   "type" => "hidden", 
                                   "value" => $this->getDBValue() 
                    );
                if ($node->hasAttribute('alt_name')) {
                    $options['name'] = $node->getAttribute('alt_name');
                }
                $hidden = $template->createElement( "input", $options);
                $node->parentNode->replaceChild( $hidden, $node );
            }                   
        }
    }                                                   



    /**
     * Process the header of an editable node
     * @param I2CE_Template $template
     * @param DOMNode $node
     * @param DOMNode $head_node
     */
    protected function processHeaderEditable($template,$node,$head_node) {
        if ( $this->getOption('required') ) {
            $asterisk = $template->createElement( "span" );
            $asterisk->appendChild( $template->createTextNode( "*" ) );
            $asterisk->setAttribute( "class", "required_field_notice" );
            $head_node->appendChild( $asterisk );
            $head_node->setAttribute( "class", $head_node->getAttribute("class") . " required_field" );
        }
    }


    /**
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @param DOMNode $form_node
     */
    public function processDOMNotEditable( $node, $template, $form_node) {
        $ele_name = $this->getHTMLName();
        $node->appendChild(
            $template->createElement( "input", array( "name" => $ele_name, "type" => "hidden", "value" => $this->getDBValue() ))
            );
        $node->appendChild($this->getDisplayNode($node, $template ));
    }




    /**
     * Displays invalid/error messages on the given node
     * @var I2CE_Template $template
     * @var DOMNode $node
     */
    protected function displayInvalid($template,$node) {
        $ele_name = $this->getHTMLName();
        $err = $this->getInvalid();
        if ( is_array( $err ) ) {
            $err_msg = $template->createElement( "span", array( "class" => "error" ), $err['message'] );
            $node->appendChild( $err_msg) ;
            $label = $template->createElement( "label", array( "for" => $this->getHTMLName('ignore') ) );
            $check_box = $template->createElement( "input", 
                                                   array( "type" => "checkbox", "name" => $this->getHTMLName('ignore'), "id" => $this->getHTMLName('ignore'), "value" => 1 ) );
            $label->appendChild( $check_box );
            $label->appendChild( $template->createTextNode( "Ignore this error." ) );
            if (array_key_exists('extra',$err)) {
                if (is_scalar($err['extra'])) {
                    $node->appendChild( $template->createTextNode( $err['extra'] ) );                    
                } else if (is_array($err['extra'])) {
                    $ul = $template->createElement( "ul", array( "class" => "error" ) );
                    foreach( $err['extra'] as $link => $extra_data ) {
                        foreach( $extra_data as $id => $display ) {
                            $check_link = $template->createElement( "a", array( "href" => $link . $id, "target" => "_new" ), $display );
                            $li = $template->createElement( "li" );
                            $li->appendChild( $check_link );
                            $ul->appendChild( $li );
                        }
                    }
                    $node->appendChild( $ul) ;
                }                
            }
            $node->appendChild( $label) ;
            $node->appendChild( $template->createElement( "br" ) );
        } else {
            $err_msg = $template->createElement( "span", array( "class" => "error" ), $err );
            $node->appendChild($err_msg );
        }
    }


    public function getElement () {
        return $this->element;
    }
        
    public function setElement($element) {
        $this->element  = $element;
    }

    /**
     * @returns array of DOMNode
     */
    abstract public function processDOMEditable( $node, $template, $form_node );


    public function postprocessDOMEditable( $node, $template, $form_node ) {
        return true;
    }

          
    /**
     * Set an attribute for this form.
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
     * Return true if a given attribute exists for this form.
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
        $cname = ( $this->getContainer() instanceof I2CE_FieldContainer ? $this->getContainer()->getName() : "" );
        if ( !array_key_exists( $cname, self::$static_attrs ) ) {
            self::$static_attrs[$cname] = array();
        }
        if ( !array_key_exists( $this->name, self::$static_attrs[$cname] ) ) {
            self::$static_attrs[$cname][$this->name] = array();
        }
        self::$static_attrs[$cname][$this->name][$key] = $value;
    }

    /**
     * Return the static attribute value for a given attribute.
     * @param string $key
     * @return mixed
     */
    public function getStaticAttribute( $key ) {
        $cname = ( $this->getContainer() instanceof I2CE_FieldContainer ? $this->getContainer()->getName() : "" );
        if ( array_key_exists( $cname, self::$static_attrs ) 
                && array_key_exists( $this->name, self::$static_attrs[$cname] )
                && array_key_exists( $key, self::$static_attrs[$cname][$this->name] ) ) {
            return self::$static_attrs[$cname][$this->name][$key];
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
        $cname = ( $this->getContainer() instanceof I2CE_FieldContainer ? $this->getContainer()->getName() : "" );
        return ( array_key_exists( $cname, self::$static_attrs )
                && array_key_exists( $this->name, self::$static_attrs[$cname] )
                && array_key_exists( $key, self::$static_attrs[$cname][$this->name] ) ); 
    }




}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
