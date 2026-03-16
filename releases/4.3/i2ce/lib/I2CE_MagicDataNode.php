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
 * @package I2CE
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @since v2.0.0
 * @version v2.0.0
 */
if (!class_exists('I2CE_MagicDataNode',false)) {
    /**
     * Deps
     */
    require_once 'I2CE_Validate.php';
    require_once 'I2CE_Locales.php';

/**
 * Configuration class to lookup and save configuration options.
 * @package I2CE 
 */
    class I2CE_MagicDataNode implements RecursiveIterator,
        SeekableIterator, ArrayAccess, Countable {
        /**
         * Constant type value when this data set hasn't tried to
         * populate from storage yet.
         */
        const TYPE_NOT_POPULATED = -2;
        /**
         * Constant type value when this data set hasn't been defined.
         */
        const TYPE_INDETERMINATE = -1;
        /**
         * Constant type value when this data set is a parent node.
         */
        const TYPE_PARENT = 0;
        /**
         * Constant type value when this data set is a string value.
         */
        const TYPE_STRING_VALUE = 1;
        
        /**
         * The type of this configuration setting.  Possible values
         * are -1 = unset, 0 = parent object, 1 = scalar/value.
         *
         * @var integer
         */
        protected $type;
        /**
         * @var string The name of this configuration setting.
         */
        protected $name;
        /**
         * @var string The value of this configuration setting.  This
         * is only valid when type = 1.
         */
        protected $value;
        /**
         * @var array All children configuration objects for this configuration group.  This is only valid when type = 0
         */
        protected $children;
        /**
         * @var I2CE_MagicDataNode The parent of this configuration.
         */
        protected $parent;
        protected $parentPath;
        protected $parentPathTop;
        /**
         * @var I2CE_MagicDataNode The top level of this configuration.
         */
        protected $top;

        /**
         * @var boolean $volatile.  True if the data is volatile.  Defaults to false
         */
        protected $volatile;


        private function isScalar() {
            return  $this->type != self::TYPE_PARENT;
        }

        /**
         * Check/set whether this magic data is volatile or not
         *
         * @param boolean $volatile.  If set, we set the volatility of
         * this node (does not apply to subnodes).  If not set we
         * return the volatility state of this node.  Defaults to not
         * set (null).  Recursively sets all sub-nodes to volatile.
         *
         * @returns mixed 
         */
        public function volatile($volatile=null) {
            if ($volatile === null) {
                return $this->volatile;
            } else {
                $this->volatile = $volatile;
                $this->type = self::TYPE_NOT_POPULATED;
                foreach ( $this->children as $child) {
                    $child->volatile($volatile);
                }
            }
        }



        /**
         * Check to make sure a given key is valid. It can be any combination of
         * _-+.0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
         * @param string $key
         * @return boolean
         */
        public static function checkKey( $key ) {            
            if (is_numeric($key)) {
                $key = '' .$key; //make it a string
            } else if (!is_string($key)) {
                return false;
            }
            if (strlen($key) == 0 || $key[0] == '=' ) {
                return false;
            }
            if (($pos = strpos('/',$key)) !== false)  {
                return false;
            }
            return true;
        }
        
        /**
         * Return the path divider for this MagicDataNode object
         * @return string
         */
        public function pathDivider() { return "/"; }
        /**
         * Return the full path to this configuration setting.
         *
         * @param boolean $show_top defaults to true if we are to show the parent
         * @return string
         */
        public function getPath( $show_top  = true) { 
//             if ($this->type == self::TYPE_NOT_POPULATED) {
//                 if (!$this->populate()) {
//                     I2CE::raiseError("Could not populate at " . $this->getPath(false));
//                     return null;
//                 }
//             }            
            if ( $this->parent instanceof I2CE_MagicDataNode ) {
                if ($show_top) {
                    if ($this->parentPathTop === false) {
                        $this->parentPathTop = $this->parent->getPath(true) . $this->parent->pathDivider() . $this->name;
                    }
                    return $this->parentPathTop;
                } else {
                    if ($this->parentPath === false) {
                        $this->parentPath = $this->parent->getPath(false) . $this->pathDivider() . $this->name;
                    }
                    return $this->parentPath;
                }

            } else {
                return $this->name;
            }
        }
        /**
         * Return the name of this configuration setting.
         *
         * @return string
         */
        public function getName() {
            return $this->name;
        }
    
        /**
         * Construct a new configuration value or grouping.
         * @param string $name The name of this configuration setting.
         * @param I2CE_MagicDataNode $parent The parent of this configuration setting.
         * @param boolean $check_key. Defaults to true in which case we check that the key is valid.
         */
        protected function __construct( $name = null, $parent = null, $check_key = true) {
            $this->children = array();
            $this->attributes = array();
            if ($name === null) {
                if ( $parent !== null ) {
                    self::raiseError( "Invalid blank MagicData name.", E_USER_ERROR );
                    return;
                }
            } else {
                if ($check_key && (! self::checkKey( $name ) )) {
                    self::raiseError( "Invalid name for magic data: [$name]", E_USER_ERROR );
                    return;
                }
                $this->name = $name;
            }
            $this->parent = $parent;
            if ( $parent instanceof I2CE_MagicDataNode) {
                $this->top = $parent->top;
                $this->volatile = $parent->volatile;
            } else {
                $this->top = $this;
                $this->parent = $this;
                $this->volatile = false;
            }
            $this->parentPathTop = false;
            $this->parentPath = false;
            $this->type =self::TYPE_NOT_POPULATED;
        }


        public function unpopulate($deep = true, $cleanup = false) {
            if ($this->type === self::TYPE_NOT_POPULATED) {
                return;
            }
            if (!$this->top instanceof I2CE_MagicData) {
                return;
            }
            if (count($this->top->storage) == 0) { 
                //no storage mechanisms, so let us not unpopulate
                return;
            } 
            if ($deep) {
                foreach ($this->children as $key => $child) {
                    $child->unpopulate($deep, $cleanup);
                    if ( $cleanup ) {
                        $child = null;
                        $this->children[$key] = null;
                        unset( $this->children[$key] );
                    }
                }
                foreach ($this->attributes as $prefix=>$attrs) {
                    foreach ($attrs as $key => $attr) {                        
                        $attr->unpopulate($deep, $cleanup);
                        if ( $cleanup ) {
                            $attr = null;
                            $this->attributes[$prefix][$key] = null;
                            unset( $this->attributes[$prefix][$key] );
                        }
                    }
                    if ( $cleanup ) {
                        $this->attributes[$prefix] = null;
                        unset( $this->attributes[$prefix] );
                    }
                }
                    
            }
            $this->value = null;
            $this->parentPath = false;
            $this->parentPathTop = false;
            $this->type  = self::TYPE_NOT_POPULATED;
        }


        /**
         * Populate any stored data based on the storage objects set in the top node.
         * @returns  true on success. 
         */
        protected function populate() {
            if ($this->type !== self::TYPE_NOT_POPULATED) {
                return true;
            }
            if ( !$this->top instanceof I2CE_MagicData ) {
                I2CE::raiseError("Could not populate at " . $this->getPath(FALSE).
                                 ": Not an I2CE_MagicData object");
                return FALSE;
            }

            $data = $this->top->retrieve( $this );
            if ( is_array( $data['data'] ) && count( $data["data"] ) > 0 ) {
                $this->type = $data["data"]["type"];
                if ( $this->type > 0) {
                    $this->value = $data["data"]["value"];
                    if (array_key_exists('children', $data["data"]) &&
                        is_array($data['data']['children']) &&
                        count($data['data']['children']) > 0)  {
                        foreach( $data["data"]["children"] as $kid ) {
                            if (is_string($kid) && strlen($kid) >= 3 && $kid[0] == '=' && $kid[2] == ':') {
                                //protected function newAttribute( $key,  $save , $prefix   ) {
                                if (!$this->newAttribute(substr($kid,3),FALSE, $kid[1])) {
                                    self::raiseError("Could not set attribute ".
                                                     "{$matches[1]} from $kid at "
                                                     . $this->getPath(false));
                                }
                            }
                            // if (preg_match('/^=(.?):(.+)$/',$kid,$matches)) {
                            //     if (!$this->newAttribute($matches[2],FALSE,
                            //                              $matches[1])) {
                            //         self::raiseError("Could not set attribute ".
                            //                          "{$matches[1]} from $kid at "
                            //                          . $this->getPath(false));
                            //     }
                            // }
                        }
                    }
                } elseif ( $this->type == self::TYPE_PARENT) {
                    $kids = $data["data"]["children"];
                    if ( is_array( $kids ) && count( $kids ) > 0 ) {                        
                        foreach( $kids as $kid ) {
                            if (preg_match('/^=(.?):(.+)$/',$kid,$matches)) {
                                if (!$this->newAttribute($matches[2],false,$matches[1])) {
                                    self::raiseError("Could not set attribute {$matches[1]} from $kid at " . $this->getPath(false));
                                }
                            } else {
                                if (! $this->newChild( $kid, false ) instanceof I2CE_MagicDataNode) {
                                    self::raiseError("Could not create child $kid at " . $this->getPath());
                                }
                            }
                        }
                    }
                } else{
                    self::raiseError( "Don't know how to handle type (" .
                                      $this->type .  ") for populating magic data "
                                      . $this->getPath() ."\nGot data: ".
                                      gettype($data) );
                    $this->type = self::TYPE_INDETERMINATE;
                    return FALSE;
                }
                if ( $data['storage_idx'] >0 && ! $this->top->store( $this, $data["storage_idx"]-1 )) {
                    return false;
                }
            } else {
                /* $segment = $this->getPath(); */
                /* if(FALSE !== strpos($segment, ":")) { */
                /*   $segment = substr($segment, */
                /*                     strpos($segment, ":")+1); */
                /* } */
                
                /* if (substr($segment,0,6) === 'config') { */
                /*     I2CE::raiseError("Setting " . $this->getPath() . */
                /*                      " to indeterminate as nothing was". */
                /*                      " retrieved while populating"); */
                /* } */
                $this->type = self::TYPE_INDETERMINATE;
            }
            return TRUE;
        }
        
        
        /**
         * Return the value of this configuration object if the type == 1 and the current
         * object if type is anything else.
         * @return mixed
         */
        public function getValue() {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return null;
                }
            }
            if ( $this->type > 0) {
                $encoding = '';
                if ($this->hasAttribute('binary') && $this->getAttribute('binary')  && $this->hasAttribute('encoding')) {
                    $encoding = $this->getAttribute('encoding');
                } 
                $ret = $this->value;
                if ($this->is_translatable() && $this->top instanceof I2CE_MagicData) {
                    //we should see if we want to overide the default value with a translation
                    $found = false;
                    foreach ($this->top->locales as $locale) {
                        if ($this->is_translated($locale)) {  //I2CE_Locales::DEFAULT_LOCALE always returns translated
                            $trans = $this->getTranslation($locale,false); //don't resolve
                            if (!$trans) {
                                continue;
                            }
                            $found = true;
                            $ret =  $trans;
                            break;
                        }
                    }
                    //now fallback to see if we can get someother translations
                    if (!$found) {
                        foreach ($this->getTranslations() as $trans) {
                            if ($trans) {
                                $ret = $trans;
                                break;
                            }
                        }
                    }

                }
                switch ($encoding) {
                case 'base64':
                    return base64_decode($ret);
                default:
                    return $ret;
                }
            }
            else if($this->type === self::TYPE_PARENT) {
                return $this;
            }

            return $this;
        }

        /**
         * Checked to see if the node referenced is translatable
         * @param string $path.  Defaults to null, meaning the current node.
         * @returns boolean.
         */
        public function is_translatable($path = null) {
            if ($path !== null) {
                $data = $this->traverse($path,false,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return false;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                $data = $this;
            }
            if ($data->type  < 1) {
                I2CE::raiseError("Not scalar" . $data->type . ' at ' . $data->getPath(false));
                return false; //not a scalar type
            }
            if (!$data->hasAttribute('translatable')) {
                return false;
            }
            return ($data->getAttribute('translatable') ==  1);
        }


        /**
         * Get the translations for the current node
         * @param boolean $locales_only. Defaults to false.  If true returns only the loclaes for which a translation exists
         * If true returns associative array of locale=>translation pairs
         * @param boolean $include_default_locale.   Defaults to true in which case we include the nodes values under the default locale
         * @return array
         */
        public function getTranslations($locales_only = false, $include_default_locale = true) {
            if ($this->type <= 0) {
                return false;
            }
            if (!$this->is_translatable()) {
                return false;
            }
            $trans = $this->_getAttributes($locales_only,'T');
            if (!$locales_only && $include_default_locale && !array_key_exists(I2CE_Locales::DEFAULT_LOCALE,$trans) && (strlen((string)$this->value)>0)) {
                $trans[I2CE_Locales::DEFAULT_LOCALE] = $this->value;
            }
            return $trans;
        }

        /**
         * Checked to see if the node referenced is translated ito the given locale
         * @param string $locale.
         * @param string $path.  Defaults to null, meaning the current node.
         * @returns boolean.
         */
        public function is_translated($locale, $path = null) {
            if ($path !== null) {
                $data = $this->traverse($path,false,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return false;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                $data = $this;
            }            
            if (!$data->is_translatable()) {
                return false;
            }
            if ($locale == I2CE_Locales::DEFAULT_LOCALE) {
                return true; 
            } else {
                return $data->_hasAttribute($locale, 'T');
            }
        }

        /**
         * Sets the translatable attribute for the referenced node
         * @param string $path.. Defaults to null, meaning the current node
         * @param boolean $transtable. Defaults to true
         * @returns true on success, false on failure
         */
        public function setTranslatable($path = null, $translatable = true) {
            if ($path !== null) {
                $data = $this->traverse($path,false,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return false;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                $data = $this;
            }            
            if ($data->type == self::TYPE_INDETERMINATE) {
                $data->type = self::TYPE_STRING_VALUE;
                if (!$data->save()) {
                    return false;
                }
            } elseif ($data->type  < 1) {
                return false; //not a scalar type
            }
            return $data->setAttribute('translatable' , $translatable ? 1: 0);
        }

        public function setTranslation($locale,$translation, $path = null) {
            if ($path !== null) {
                $data = $this->traverse($path,false,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return false;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                $data = $this;
            }            
            if ($data->type <= 0) {
                return false;
            }
            if (!$data->is_translatable()) {
                I2CE::raiseError("Trying to set translation for non-tranlsatable node " . $data->getPath(false));
                return false;
            }
            if ($locale == I2CE_Locales::DEFAULT_LOCALE) {
                if  ($data->value !== $translation && strlen((string)$data->value) == 0) {
                    $data->value = $translation;
                    $data->save();
                }
            }
            return $data->_setAttribute($locale,$translation,'T');

        }

        public function getTranslation($locale, $resolve = true, $path= null) {            
            if ($path !== null) {
                $data = $this->traverse($path,false,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return false;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                $data = $this;
            }          
            if (!$data->type > 0 ) {
                return false;
            }
            if (!$data->is_translatable()) {
                $resolution = array(I2CE_Locales::DEFAULT_LOCALE);
            } else  if ($resolve) {
                if ($this->top instanceof I2CE_MagicData) {
                    $resolution = $this->top->locales;
                } else {
                    $resolution = I2CE_Locales::getLocaleResolution($locale);
                }
            } else {
                $resolution = array($locale);
            }
            foreach ($resolution as $locale) {
                if ($locale == I2CE_Locales::DEFAULT_LOCALE) {
                    if ( $data->_hasAttribute($locale, 'T')) {
                        $val = $data->_getAttribute($locale,'T');
                        if (strlen($val) > 0) {
                            return $val;
                        }
                    }
                    return $data->value;
                } else {
                    if ( $data->_hasAttribute($locale, 'T')) {
                        return $data->_getAttribute($locale,'T');
                    }
                }
            }
            return  $data->value;
        }




        /**
         * Set the scalar value for this object.
         * @param mixed $value
         * @param string $locale.  The locale we wish to set the value for.  Defaults to null meaning we are not setting tranlsated values.
         * @param boolean $set_default_locale.  Defautls to true.  When setting values, make sure the default locale is set.
         * @returns true on sucess
         */
        public function setValue( $value, $locale = null, $set_default_locale = false) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    self::raiseError("Could not populate at " . $this->getPath(false));
                    return FALSE;
                }
            }
            $save = true;
            if ($value instanceof I2CE_MagicDataNode) {
                if (!  $value->populate()) {
                    self::raiseError("Could not populate at " . $value->getPath());
                    return false;
                }
            }
            if ( is_array( $value )) {
                if ( $this->isScalar() && $this->value != NULL ) {
                    self::raiseError( "Non-scalar type passed to already set scalar magic data value at: ". $this->getPath(false));
                }
                $this->type =  self::TYPE_PARENT;
                foreach( $value as $key => $val ) {  
                    $data = $this->traverse($key,true,false);                        
                    if (!$data instanceof I2CE_MagicDataNode) {
                        self::raiseError("Invalid key $key at " . $this->getPath() . " when setting magic data value");
                        return false;
                    }
                    $data->setValue($val,$locale, $set_default_locale);
                }
            } else if  ($value instanceof I2CE_MagicDataNode && $value->type ==   self::TYPE_PARENT) {
                if ( !($this->type ==  self::TYPE_PARENT || $this->type  == self::TYPE_INDETERMINATE)) {
                    self::raiseError( "Non scalar type passed to already set scalar magic data value at: " . $this->getPath(false));
                }
                $this->type =  self::TYPE_PARENT;
                foreach( $value->children as $key => $child ) {  
                    $data = $this->traverse($key,true,false);                        
                    if (!$data instanceof I2CE_MagicDataNode) {
                        self::raiseError("Invalid key $key at " . $this->getPath() . " when setting magic data value");
                        return false;
                    }
                    $data->setValue($child, $locale);
                } 
            } else if (is_scalar($value) || $value === null || ($value instanceof I2CE_MagicDataNode && $value->type >0)) {
                $translations = false;
                if ($value instanceof I2CE_MagicDataNode) {
                    if ($locale) {
                        if( $value->is_translated($locale)) {
                            $translations = array($locale=>getTranslation($locale));                            
                            if ($set_default_locale && count($translations) > 0 && !array_key_exists(I2CE_Locales::DEFAULT_LOCALE, $translations) && strlen((string)$translations[I2CE_Locales::DEFAULT_LOCALE]) > 0) {
                                reset($translations);
                                $translations[I2CE_Locales::DEFAULT_LOCALE] = current($translations);
                            }
                        }  else {
                            $translations = array($locale=>$value->value);                            
                            if ($set_default_locale && strlen((string)$value->value)) {
                                $translations[I2CE_Locales::DEFAULT_LOCALE] =$value->value;
                            }
                        }
                    } else if ($value->is_translatable() ) {
                        $translations = $value->getTranslations(false,true);
                    } else {
                        $t_value = $value->value;
                    }
                } else {
                    if ($locale) {
                        $t_value = $value;
                        $translations = array(
                            $locale=>(string)$value,
                            );
                        if ($set_default_locale && strlen((string)$value)) {
                            $translations[I2CE_Locales::DEFAULT_LOCALE] = (string) $value;                                                        
                        }
                    } else {
                        $t_value = (string)  $value;
                    }
                }
                if ( $this->type ==  self::TYPE_PARENT) {
                    // This is a parent being passed a non array.  We just append it to the children array.
                    if (is_array($translations)) {
                        self::raiseError("Trying to localize while appending value at " . $this->getPath(false));
                        return false;
                    } else {
                        $this->push($t_value);
                    }
                } else  if (is_array($translations)) {
                    if (!$this->setTranslatable()) {                        
                        self::raiseError("Could not set " . $this->getPath() . " as translatable");
                        return false;
                    }
                    $save = false;
                    foreach ($translations as $locale=>$translation) {
                        if ( strlen((string) $translation) > 0) {
                            $this->setTranslation($locale,$translation);
                            // Set the top level value if this is the default locale
                            if ( $locale == I2CE_Locales::DEFAULT_LOCALE && $this->value != $translation ) {
                                $this->value = $translation;
                                $save = true;
                            }
                        }
                    }
                } else {
                    if ($this->type > 0) {
                        if ($this->value === $t_value) {
                            $save = false;
                        } else {
                            $this->value = $t_value;
                        }
                    } elseif ($this->type == self::TYPE_INDETERMINATE) {
                        $this->type = self::TYPE_STRING_VALUE;                        
                        $this->value = $t_value;
                    } else {
                        self::raiseError("Internal error while trying to set scalar value at " .  $this->getPath() );                    
                        return false;
                    }
                }
            } else if ($value instanceof I2CE_MagicDataNode && $value->type == self::TYPE_INDETERMINATE) {
                //do nothing                    
                
            } else { 
                self::raiseError("Trying to set value at " .  $this->getPath() . " to something invalid");
                return false;
            }
            $ret = true;
            if ($save ) {
                 $ret = $this->save();
            }
            if ($value instanceof I2CE_MagicDataNode) {
                //now copy over any attributes
                foreach ($value->attributes as $prefix=>$attributes) {                    
                    foreach ($attributes as $k=>$attr) {
                        $ret &= $this->_setAttribute($k,$attr->value,$prefix);
                    }
                }
            }
            return $ret;
        }
        
        
        
        /**
         * Set the type for this object.  The nosave flag should be set to true if the calling function
         * is going to save so that it doesn't get saved twice.
         * @param integer $type
         * @returns boolean true on success
         */
        protected function setType( $type) {
            if ( $this->is_populated() && ($this instanceof I2CE_MagicData) && ($type > 0)) {
                self::raiseError("Trying to set root node to have scalar value");
                return false;
            }
            $this->type = $type;
            return true;
        }
        
        /**
         * Return the type of this node.
         * @return int or false on failure
         */
        public function getType() {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }
            }
            return $this->type;
        }

        /**
         * Check to see if the value at the specified path is set and is a scalar.
         * @param strign $key. A key or path in the magic data.  If null (default) then we are checking on the node itself
         * @returns boolean.  True if it is a scalar value
         */
        public function is_scalar($path = null) {
            if ($path !== null) {
                $data = $this->traverse($path,false,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return false;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                $data = $this;
            }            
            return $data->type > 0;
        }


        /**
         * Set the magic data node at the specified  path to be scalar if is indeterminate
         * @param strign $key. A key or path in the magic data.  If null (default) then we are checking on the node itself
         * @param boolean $create.  Create the path if it does not exist.  Defaults to false
         * @returns boolean.  True on success
         */
        public function set_scalar($path = null, $create= false) {
            if ($path !== null) {
                $data = $this->traverse($path,$create,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return false;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                $data = $this;
            }            
            if ($data->type !=  self::TYPE_INDETERMINATE) {
                return false;
            }
            $data->type = self::TYPE_STRING_VALUE;
            return $data->save();
        }


        /**
         * Set the magic data node at the specified  path to be a parent node if is indeterminate
         * @param strign $key. A key or path in the magic data.  If null (default) then we are checking on the node itself
         * @param boolean $create.  Create the path if it does not exist.  Defaults to false
         * @returns boolean.  True on success
         */
        public function set_parent($path = null, $create = false) {
            if ($path !== null) {
                $data = $this->traverse($path,$create,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return false;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                $data = $this;
            }            
            if ($data->type !=  self::TYPE_INDETERMINATE) {
                return false;
            }
            $data->type = self::TYPE_PARENT;
            return $data->save();
        }

        /**
         * Check to see if the value at the specified path is populated 
         * @param strign $key. A key or path in the magic data.  If null (default) then we are checking on the node itself
         * @returns boolean.  True if it is a scalar value
         */
        protected function is_populated($path = null) {
            if ($path !== null) {
                $data = $this->traverse($path,false,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return false;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                $data = $this;
            }            
            return $data->type !== self::TYPE_NOT_POPULATED;
        }


        public function is_root($path = null) {
            if ($path !== null) {
                $data = $this->traverse($path,false,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return false;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                $data = $this;
            }            
            return $data instanceof I2CE_MagicData;
            //return $data === $data->top;
        }



        /**
         * Check to see if the value at the specified path is indeterminate
         * @param strign $key. A key or path in the magic data.  If null (default) then we are checking on the node itself
         * @returns boolean.  True if it is a scalar value
         */
        public function is_indeterminate($path = null) {
            if ($path !== null) {
                $data = $this->traverse($path,false,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return true;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return true;
                    }
                }
                $data = $this;
            }            
            return $data->type === self::TYPE_INDETERMINATE;
        }



        /**
         * Check to see if the value at the specified path is set and is a parent node.
         * @param strign $key. A key or path in the magic data.  If null (default) then we are checking on the node itself
         * @returns boolean.  True if it is a scalar value
         */
        public function is_parent($path = null) {
            if ($path !== null) {
                $data = $this->traverse($path,false,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return false;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                $data = $this;
            }            
            return $data->type == 0;
        }




        /**
         * Return the direct scalar value for this node.
         * @return string
         */
        public function getSaveValue() {
            return $this->value;
        }


        
        /**
         * Make a new child for this object with the given key.  The
         * $nosave flag should only be set when populating a parent
         * node from storage.
         *
         * @param scalar $key.  If a boolean and true, we add to the
         * end of the array.  Otherwise we use the given key as a key.
         *
         * @param boolean $save Set to true to not save the parent
         * after creating this child.
         *
         * @returns I2CE_MagicDataNode The child that was created or
         * null on failure
         */
        protected function newChild( $key, $save = TRUE ) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " .
                                     $this->getPath(FALSE));
                    return NULL;
                }
            }
            if ($this->type > 0) {
                self::raiseError("Trying to add a child, $key, to a scalar ".
                                 "value node at " . $this->getPath());
                return NULL;
            }           

            // pushing on to the end.
            if (is_bool($key) && $key) {
                $this->children[] = NULL;
                end($this->children);
                $key = key($this->children);
                unset($this->children[$key]);
            } else  if (array_key_exists( $key, $this->children ) ) {
                return $this->children[$key];
            }

            $this->type = self::TYPE_PARENT;
            $child = new I2CE_MagicDataNode( $key, $this );
            $this->children[$key] = $child;
            if ($this->volatile) {
                // set the child to have the same volatility as the parent
                $child->volatile(true);
            }
            if (  $save ) {
                if (!$this->save()) {
                    self::raiseError("Could not save new child $key at " .
                                     $this->getPath());
                }
            }
            return $this->children[$key];
        }

        /**
         * Set the value for a child configuration option.
         *
         * @param mixed $path   {@see traverse()}
         * @param mixed $value
         */
        public  function __set( $path, $value ) {
            if ($path !== NULL) {
                $data = $this->traverse($path,TRUE,FALSE);
                if (!$data instanceof I2CE_MagicDataNode) {
                    self::raiseError("Invalid path $path at " .
                                     $this->getPath() .
                                     " when setting magic data value");
                    return FALSE;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " .
                                         $this->getPath(FALSE));
                        return FALSE;
                    }
                }
                $data = $this;
            }            
            if (!$data->setValue($value)) {
                I2CE::raiseError("Unable to set Value at " .
                                 $data->getPath(FALSE));
            }
        }

        /**
         * Get the value for a child configuration option.
         *
         * @return mixed.  Null if the path was
         * invalid. I2CE_MagicDataNode or string on success
         */
        public function __get( $path ) {
            return  $this->traverse($path,TRUE);
        }


        /**
         * Traverse magic data by a given path
         *
         * @param array $path.  A string which is a path e.g. '/some/magic/data/path' or an array of path components
         * e.g. array('some','magic','data','path).
         * There are three special path components, '', '.' and  '..' The first two mean don't go anywhere, while the third
         * means go to the parent node.
         * @param boolean $create.  Defaults to false.  If true, it will create the magic data path as it goes.  If false, it will
         * return null if the path is not already set.
         * @param boolean $return_value Defaults to true.  If true returns the value of of a leaf node rather than the node itself
         * @returns mixed. String, if the path is valid and refers to a leaf node. 
         * I2CE_MagicDataNode  if the path is valid but not a leaf node, or  null on ffailure (invalid path/path not set)
         */
        public function traverse($path,$create = false,$return_value = true) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }
            }
            $data = $this;
            if ($path === null) {
                $path = '';
            }
            if (is_string($path )) {                
                while (strlen($path) > 0 && $path[0] == '/') {
                    $data = $data->top;
                    $path = substr($path,1);
                }
                $path = explode('/',$path);
            } else  if (is_int($path)) {
                $path = array('' . $path);
            } else if (!is_array($path)) {
                self::raiseError("Unrecognized path:" . print_r($path,true) . " to traverse at " . $this->getPath());
                return null;
            }        
            foreach ($path as $component) {
                switch ($component) {
                case '':
                case '.':
                    break;
                case '..':
                    $data = $data->parent;
                    break;
                default:
                    if (!self::checkKey( $component ) ) {
                        self::raiseError("Invalid path compoenent $component");
                        return NULL;                        
                    } else if ( is_array($data->children) &&
                                !array_key_exists( $component, $data->children )) {
                        if ($create) {
                            $data = $data->newChild($component,TRUE);
                        } else {
                            return NULL;
                        }
                    } else {
                        $data = $data->children[$component];
                    }
                    break;
                }

                if  (!$data instanceof I2CE_MagicDataNode) {
                    self::raiseError("Trying to traverse invalid magic data path",
                                     E_USER_NOTICE);
                    return NULL;
                }
                if ($data->type == self::TYPE_NOT_POPULATED) {
                    if (!$data->populate()) {
                        I2CE::raiseError("Could not populate at " .
                                         $this->getPath(false));
                        return NULL; 
                    }
                }
            }
            if ($return_value) {
                return $data->getValue();
            } else {
                return $data;   
            }
        }

        /**
         * Get the parent magic data node
         * @returns I2CE_MagicDataNode
         */
        public function getParent() {
            return $this->parent;
        }

        /**
         * Check to see if a given key or path exists and the value is set
         *
         * @param mixed $path   {@see traverse()}
         * returns mixed.  Booolean true or false if the magic data referenced by the given path is set.  null on failure (invalid path)
         */
        public function __isset($path) {
            if ($path !== null) {
                return ($this->traverse($path,false) !== null);
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                if ($this->type > 0) {
                    return $this->value !== null;
                } else if ($this->type == 0) {
                    return true;
                }
                return false;
            }            
        }


        /**
         * Check to see if a given key  key or path exists
         *
         * @param mixed $path   {@see traverse()}
         * returns mixed.  Booolean true or false if the magic data referenced by the given path is set.  null on failure (invalid path)
         */
        public function pathExists($path) {
            if ($path !== null) {
                $data = $this->traverse($path,false,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return false;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                $data = $this;
            }            
            return ($data instanceof I2CE_MagicDataNode);
        }


        /**
         * If the data referenced by that path is set, we set the value of $data to that data
         * @param mixed &$data  where you wish to store the magic data if it is set
         * @param mixed $path   {@see traverse()}.  Defaults to null which means we get at this node
         * @param boolean $as_array defaults to false.   If true, will set $data to be the result of calling getAsArray() on the 
         * magic data referenced by $path.  If false then we only set $data if the magic data node refrenced by $path 
         * is a leaf node/value
         * @returns boolean.  true if the value was set
         */
        public function setIfIsSet(&$data,$path,$as_array=false ) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }
            }
            if ($path !== null) {
                $magic_data = $this->traverse($path,false,false);
                if (!$magic_data instanceof I2CE_MagicDataNode) {
                    return false;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return false;
                    }
                }
                $magic_data = $this;
            }            
            if ($as_array) {
                $data = $magic_data->getAsArray();
            } else  if ($magic_data->type > 0) {
                $data = $magic_data->getValue();
            }
            return true;
        }


        

        
        /**
         * Unset a given key in the children array.
         *
         * @param string $key
         */
        public final function __unset( $key ) {
            $data = $this->traverse($key,false,false);
            if (!$data  instanceof I2CE_MagicDataNode) {
                return;
            }
            if ($data->type != self::TYPE_NOT_POPULATED) {
                $data->erase();
            }
        } 


        /**
         * Erases all children of the current node and all of its
         * subnodes from the storage mechanism.
         *
         * @param string $locale.  The locale we wish to erase the
         * values for.  Defaults to null meaning we erase all
         * values/locales
         *
         * @returns boolean true on success
         */
        public function eraseChildren($locale = null) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " .
                                     $this->getPath(false));
                    return false;
                }
            }
            foreach ($this->children as $i=>$child) {
                if ($child->erase($locale) && $locale === null) {
                    unset($this->children[$i]);
                }
            }
            if ($locale === null) {
                foreach ($this->attributes as $prefix=>&$attrs) {
                    foreach ($attrs as $i=>$attr) {
                        if ($attr->erase()) {
                            unset($attrs[$prefix][$i]);
                        }
                    }
                    if (count($attrs) == 0) {
                        unset($this->attributes[$prefix]);
                    }
                }
                return ( (count($this->children) + count($this->attributes)) == 0);
            } else {
                return true;
            }
        }


        public function removeTranslation($locale) {
            if ($this->type <= 0) {
                return true;
            }
            if ($this->is_translated($locale)) {
                return $this->_removeAttribute('T',$locale);
            }
            return true;
        }


        /**
         *Renames a child node
         *@param string $old
         *@param string $new
         *@returns boolean.  True on success, false on failure
         */
        public function renameChild($old,$new) {
            if (!$this->top instanceof I2CE_MagicData) {
                I2CE::raiseError("Cannont rename when there is no top");
                return false;
            }
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }               
            }     
            if (!$this->is_parent()) {
                I2CE::raiseError("Cannot rename a child of a non-parent  node");
                return false;
            }
            return $this->top->moveChild($this,$old,$new);
            
        }

        /**
         * Erases the current node and all of its subnodes from the storage mechanism.
         * @returns boolean true on success
         * @param string $locale.  The locale we wish to erase the values for.  Defaults to null meaning we erase all values/locales
         */
        public function erase($locale=null) {
            $sucess  =$this->eraseChildren($locale); //this will populate
            if ($locale !== null ) {
                return $this->removeTranslation($locale);
            }
            if (!$this->top instanceof I2CE_MagicData) {
                return $sucess;
            }
            if (!$this->top->destroy( $this )) {
                self::raiseError("Could not destroy " . $this->getPath(false));
                $sucess = false;
            }
            if (!$this instanceof I2CE_MagicData) { //not the top node.
                //not the root node
                if ($this->parent instanceof I2CE_MagicDataNode) {
                    if ($this->parent->type == self::TYPE_NOT_POPULATED) {
                        if (!$this->parent->populate()) {
                            I2CE::raiseError("Could not populate parent at " . $this->getPath(false));
                            return false;
                        }               
                    }     
                    unset( $this->parent->children[$this->name] );
                    if (!$this->parent->save()) {
                        I2CE::raiseError("Could not save erase state at  " . $this->getPath(false));
                        $sucess = false;
                    }
                } else {
                    I2CE::raiseError("At " . $this->getPath() . " parent is not a magic data node");
                }
            }
            $this->parent = null;
            $this->top = null;
            $this->parentPath = false;
            $this->parentPathTop = false;
            return $sucess;
        }
        

        /**
         * Return an array of all child keys for this object.
         * @param mixed $path   {@see traverse()}
         * @return array
         */
        public function getKeys($path = null, $attrs = false) {
            if ($path !== null) {
                $data = $this->traverse($path,false,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return array();
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return array();
                    }
                }
                $data = $this;
            }        
            $ret = array_keys( $data->children );
            if ($attrs) {
                foreach ($this->attributes as $prefix=>$attrs) {
                    foreach ($attrs as $key=>$val) {
                        $ret[] = '=' . $prefix . ':' . $key;
                    }
                }
            } 
            return $ret;
        }




        /**
         * Return an array of all the child values for this object.
         * @param mixed $path   {@see traverse()}.  Defaults to null which means we get at this node
         * @param string $locale.  The locale we wish to get the values for.  Defaults to null meaning we are not getting tranlsated values.
         * @return array
         */
        public function getAsArray($path = null, $locale = null) {
            if ($path !== null) {
                $data = $this->traverse($path,false,false);
                if (!$data instanceof I2CE_MagicDataNode) {
                    return null;
                }
            } else {
                if ($this->type == self::TYPE_NOT_POPULATED) {
                    if (!$this->populate()) {
                        I2CE::raiseError("Could not populate at " . $this->getPath(false));
                        return null;
                    }
                }
                $data = $this;
            }            
            return $data->_getAsArray($locale);
        }
        /**
         * @param string $locale.  The locale we wish to get the values for.  Defaults to null meaning we are not getting tranlsated values.
         */
        protected function _getAsArray($locale = null) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }
            }
            if ($this->type  > 0) {
                if ($locale && $this->is_translated($locale)) {
                    $ret = $this->getTranslation($locale);
                } else {
                    $ret = $this->getValue();
                }
            } else {
                $ret = array();
                foreach ( $this->children as $key => $conf ) {
                    $ret[$key] = $conf->_getAsArray($locale);
                }
            }
            $this->unpopulate();
            return $ret;
        }
        
        public function __toString() { 
            if ($this->type > 0) {
                return $this->getPath(false) . ' = ' . $this->value;
            }
            return "";
        }

        /**
         * Call all the storage methods associated with this configuration.
         * @param boolean $recurse.  Defaults to false.  If true, and we were able to save this node, we recrusively save all the populated children
         * of this node as well.
         */
        protected function save($recurse = false) {
            if ($this->type < 0) {
                self::raiseError("Trying to save an indeterminate node " . $this->getPath());
                return false;
            }
            if (!$this->top instanceof I2CE_MagicData) {
                return false;
            }
            $sucess = $this->top->store( $this);
            if (!$sucess) {
                self::raiseError("Could not store " . $this->getPath());
            }
            if ($sucess && $recurse) {
                foreach ($this->children as $child) {
                    if ($child->type != self::TYPE_NOT_POPULATED) {
                        $sucess &= $child->save(true);
                    }
                }
                foreach ($this->attributes as $prefix=>$attrs) {
                    foreach ($attrs as $attr) {                        
                        $sucess &= $attr->save(false);
                    }
                }

            }
            return $sucess;
        }
    
        /**********************************
         *                                *
         *   Push/pop                     *
         *                                *
         *********************************/


        /**
         * Push a value onto the end of a parent/indeterminate node.
         * @param mixed $val;
         * @returns int the number of children the current node has.  null on failure
         */
        public function push($val) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return null;
                }
            }
            if ($this->type > 0) {
                self::raiseError("Trying to push a value onto a scalar node");
                return null;
            }
            $child  = $this->newChild(true,true); //create a new child at the end
            $child->setValue($val);
            return count($this);
        }


        /**
         * Pop a value off the end of a parent/indeterminate node.
         * @param mixed.  Scalar value if the node is scalar, an array if it is a parent, otherwise null.  
         */
        public function pop() {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return null;
                }
            }
            if ($this->type != self::TYPE_PARENT) {
                return null;
            }
            end($this->children);
            $key = key($this->children);
            if ($key === null) {
                return null;
            }
            $val =  $this->children[$key];
            if (!$val->populate()) {
                I2CE::raiseError("Could not populate at " . $val->getPath(false));
            }
            if ($val->type > 0) {
                $ret =  $val->getValue();
            } else if ($val->type == self::TYPE_PARENT) {
                $ret = $val->getAsArray();
            } else {
                $ret = null;
            }
            $val->erase();
            return $ret;
        }
        


        /**********************************
         *                                *
         *   Array Access Interface       *
         *                                *
         *********************************/


        public function offsetExists($key) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }
            }
            if ($key === null || !is_scalar($key) ) {
                return false;
            }
            if ( !array_key_exists( $key, $this->children ) ) {
                return false;
            }
            if ( !$this->children[$key] instanceof I2CE_MagicDataNode ) {
                return false;
            }
            return true;            
        }





        public function offsetGet($key) {
            if ( !self::checkKey( $key ) ) {
                self::raiseError("Invliad path component $key");
                return null;
            }
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return null;
                }
            }
            $valNode = $this->traverse($key,false,false);
            if (!$valNode instanceof I2CE_MagicDataNode ) {
                return null;
            }
            if  ( $valNode->type > 0) {
                return $valNode->getValue();
            } else if ($valNode->type == 0) {
                return $valNode->getAsArray();
            } else {
                return null;
            }
        }    



        public function offsetSet($key,$val) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return null;
                }
            }
            if ($key === null) {
                $this->push($val);
            } else {
                if ( !self::checkKey( $key ) ) {
                    self::raiseError("Invalid path component $key");
                    return null;
                }
                if ( (!is_array($val)) && (!is_scalar($val))) {
                    return;
                }
                $this->__set($key,$val);
            }
        }
            

        public function offsetUnset($key) {
            if ( !self::checkKey( $key ) ) {
                self::raiseError("Invalid path component $key");
                return false;
            }
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }
            }
            $this->__unset($key);
        }






        /**********************************
         *                                *
         *   CountableInterface           *
         *                                *
         *********************************/
        public function count() {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return 0;
                }
            }
            return count($this->children);
        }


        /**********************************
         *                                *
         *   Iterator Interface           *
         *                                *
         *********************************/
        public function current() {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return null;
                }
            }
            $current = current($this->children);
            if (!$current instanceof I2CE_MagicDataNode) {
                return null;
            }
            return $current->getValue();
        }

        public function key() {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return null;
                }
            }
            return key($this->children);
        }
        public function next() {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return null;
                }
            }
            $current = current($this->children);
            if ($current instanceof I2CE_MagicDataNode) {
                $current->unpopulate();
            }
            next($this->children);
        }
        public function rewind() {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }
            }
            reset($this->children); 
            return true;
        }
        public function valid() {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }
            }
            return (key($this->children) !== null);
        }


        
        /**********************************
         *                                *
         *   Seekable  Interface          *
         *                                *
         *********************************/

        function seek($key) {
            if (!self::checkKey($key)) {
                self::raiseError("Invalid path component $key");
                return;
            }
            if ($this->key() == $key) {
                return;
            }
            $this->rewind();  //calls populate.
            $position = $this->key();            
            while($position !== $key && $this->valid()) {
                $this->next(); 
            } 
        } 



        /**********************************
         *                                *
         *   RecursiveIterator Interface  *
         *                                *
         *********************************/
        

        /**
         * whether the current key has children or not.
         * this is _not_ whether this has a children or not
         */
        public function hasChildren() {
            $child = $this->traverse($this->key(),false,false);
            if ($child instanceof I2CE_MagicDataNode and $child->type == 0) {
                return true;
            } else {
                return false;
            }
        }


        public function getChildren() {
            return $this->traverse($this->key(),false,false);
        }



        /***********************************
         *                                 *
         *         Attributes              *
         *                                 *
         ***********************************/

        protected $attributes;

        
        /**
         * Checks to see if an attribute is present
         * @param string $key
         * @returns mixed boolean.  true if key exists, false if it does not, null if an invalid key
         */
        public function hasAttribute($key) {
            return $this->_hasAttribute($key,'A');
        }


        protected function _hasAttribute($key,$prefix) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return null;
                }
            }
            if ( !self::checkKey( $key ) ) {
                self::raiseError("Invalid path component $key");
                return null;
            }
            if (!array_key_exists($prefix, $this->attributes) || !is_array($this->attributes[$prefix])) {
                return false;
            }
            return array_key_exists($key,$this->attributes[$prefix]);
        }

        /**
         * Gets an attribute
         * @param string $key
         * @returns mixed string if the attribute value. null if it does not exist.
         */
        public function getAttribute($key) {
            return $this->_getAttribute($key,'A');
        }


        protected function _getAttribute($key,$prefix) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return null;
                }
            }
            if (!array_key_exists($prefix, $this->attributes) || !is_array($this->attributes[$prefix])) {
                return null;
            }
            if (!array_key_exists($key,$this->attributes[$prefix])) {
                return null;
            }
            if ($this->attributes[$prefix][$key]->type == self::TYPE_NOT_POPULATED) {
                if (!$this->attributes[$prefix][$key]->populate()) {
                    I2CE::raiseError("Could not populate $key at " . $this->getPath(false));
                }
            }
            return $this->attributes[$prefix][$key]->value;
        }

        /**
         * Removes an attribute
         *@param string $key
         *@returns true if the attribute was removed.  false if the attribute did not exist
         */
        public function removeAttribute($key) {
            return $this->_removeAttribute('A',$key);
        }


        protected function _removeAttribute($prefix,$key) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }
            }
            if (!array_key_exists($prefix, $this->attributes) || !is_array($this->attributes[$prefix])) {
                return false;
            }
            if (!array_key_exists($key,$this->attributes[$prefix])) {
                return false;
            }
            return $this->attributes[$prefix][$key]->erase();
        }

        /**
         * Get the attributes
         * @param boolean $keys_only. Defaults to false.  If true returns only the attribute names that are set.
         * If true returns associative array of attribute=>value pairs
         * @return array
         */
        public function getAttributes($keys_only = false) {
            return $this->_getAttributes($keys_only,'A');
        }

        protected function _getAttributes($keys_only,$prefix) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return array();
                }
            }
            if (!array_key_exists($prefix, $this->attributes) || !is_array($this->attributes[$prefix])) {
                return array();
            }
            if ($keys_only) {
                return array_keys($this->attributes[$prefix]);
            } else {
                $ret = array();
                foreach ($this->attributes[$prefix] as $name=>$node) {
                    if ($node->type == self::TYPE_NOT_POPULATED) {
                        if (!$node->populate()) {
                            I2CE::raiseError("Could not populate at " . $node->getPath(false));
                            continue;
                        }
                    }       
                    $ret[$name] = $node->value;
                }
                return $ret;
            }
        }


        /***
         * Set an  attribute
         * @param string $key
         * @param string $val
         * @returns boolean true on success. false on failure
         */
        public function setAttribute($key,$val) {
            if (!is_scalar($val)) {
                self::raiseError("Trying to set incorrect value for attribute $key at " . $this->getPath(false));
                return false;
            }
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }
            }
            return $this->_setAttribute($key,$val, 'A');
        }


        protected function _setAttribute($key,$val,$prefix) {
            $this_save = false;
            if (!array_key_exists($prefix, $this->attributes) || ! is_array($this->attributes[$prefix])) {
                $this_save = true;
                $this->attributes[$prefix] = array();
            }
            if (array_key_exists($key,$this->attributes[$prefix])) {
                $attr = $this->attributes[$prefix][$key];
                if ($attr->type == self::TYPE_NOT_POPULATED) {
                    if (!$attr->populate()) {
                        I2CE::raiseError("Could not populate at " . $attr->getPath(false));
                        return false;
                    }
                }                       
                if ( ($attr->type != self::TYPE_INDETERMINATE) && ($attr->type != self::TYPE_STRING_VALUE)) {
                    self::raiseError("Invalid type " . $attr->type . " for attribute $key at " . $this->getPath(false));
                    return false;
                }
            } else {
                $this_save = true;
                $attr = $this->newAttribute($key, false, $prefix);
                if ($attr === false) {
                    self::raiseError("Could not create  attribute<$prefix> for $key at " . $this->getPath(false));
                    return false;
                }
            }
            $success = true;
            if ($attr->type == self::TYPE_INDETERMINATE || $attr->value !== $val) {
                $attr->type = self::TYPE_STRING_VALUE;
                $attr->value = $val;
                $success &= $attr->save();
            }
            if ($this_save) {
                $success &= $this->save();
            }
            return $success;
        }


        /**
         * Make a new attribute this object with the given key.  The $nosave flag should only be set when
         * populating a parent node from storage.
         * @param scalar $key. the key 
         * @param boolean $save Set to true to not save the parent and attribute
         * @returns mixed.  false on failure, the attribute created on succes
         */
        protected function newAttribute( $key,  $save , $prefix   ) {
            if ( !self::checkKey( $key ) ) {
                self::raiseError("Invalid path component $key");
                return false;
            }
            $attr = new I2CE_MagicDataNode( '=' . $prefix  .':' . $key, $this , false );
            $attr->populate();
            $attr->type = self::TYPE_STRING_VALUE; //just to be safe
            $this->attributes[$prefix][$key] = $attr;
            if ($save) {
                if (!$attr->save()) {
                    return false;
                }
                if (!$this->save()) {
                    return false;
                }
            }
            return $attr;
        }


        


        /******************************
         *                            *
         *      Sorting methods       *
         *                            *
         *****************************/



        public function ksort($sort_flags = SORT_REGULAR, $recurse = false) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if(!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }
            }
            if (!ksort($this->children,$sort_flags)) {
                return false;
            }
            if (!$this->save()) {
                return false;
            }
            if (!$recurse) {
                return true;
            }
            foreach ($this->children as $child) {
                if (!$child->populate()) {
                    I2CE::raiseError("Could not populate at " . $child->getPath(false));
                    return false;
                }
                if ($child->type == self::TYPE_PARENT) {
                    if (!$child->ksort($sort_flags,$recurse)) {
                        return false;
                    }
                }
            }
            return true;
        }

        public function krsort($sort_flags = SORT_REGULAR, $recurse = false) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }
            }
            if (!krsort($this->children,$sort_flags)) {
                return false;
            }
            if (!$this->save()) {
                return false;
            }
            if (!$recurse) {
                return;
            }
            foreach ($this->children as $child) {
                if (!$child->populate()) {
                    I2CE::raiseError("Could not populate at " . $child->getPath(false));
                    return false;
                }
                if ($child->type == self::TYPE_PARENT) {
                    if (!$child->krsort($sort_flags,$recurse)) {
                        return false;
                    }
                }
            }           
            return true;
        }
        
        public function uksort($cmp_function, $recurse = false) {
            if ($this->type == self::TYPE_NOT_POPULATED) {
                if (!$this->populate()) {
                    I2CE::raiseError("Could not populate at " . $this->getPath(false));
                    return false;
                }
            }
            if (!uksort($this->children,$cmp_function)) {
                return false;
            }
            if (! $this->save()) {
                return false;
            }
            if (!$recurse) {
                return true;
            }
            foreach ($this->children as $child) {
                if (!$child->populate()) {
                    I2CE::raiseError("Could not populate at " . $child->getPath(false));
                    return false;
                }
                if ($child->type == self::TYPE_PARENT) {
                    if (! $child->uksort($cmp_function,$recurse)) {
                        return false;
                    }
                }
            }            
            return true;
        }
        
        



        /**
         * Raise an error and redirect the user for any critical errors.
         * 
         * The default redirect will go to the home page for the site.
         * @param string/mixed $message The error message.
         * @param integer $type The error type.
         * @param string $redirect The page to redirect to for critical errors.
         * @global array
         */
        static public function raiseError( $message, $type=E_USER_NOTICE,
                                           $redirect="" ) {
            $debug = debug_backtrace();
            if (is_array($debug)) {
                $message .="\n";
                for ($i=1; $i < count($debug); $i++) {
                    $message .= "Called from ";
                    if(isset($debug[$i]['class'])) {
                        $message .= $debug[$i]['class'] . $debug[$i]['type'];
                    }
                    $message .= $debug[$i]['function'] . "()";

                    if(isset($debug[$i]['line'])) {
                        $message .= " at line " .
                            $debug[$i]['line'] ." of " . $debug[$i]['file']  . "\n";
                    }
                    else {
                        $message .= " in eval\n";
                    }
                }
      
            }
            I2CE::raiseError($message, $type,$redirect);
        }

    }
}

# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
