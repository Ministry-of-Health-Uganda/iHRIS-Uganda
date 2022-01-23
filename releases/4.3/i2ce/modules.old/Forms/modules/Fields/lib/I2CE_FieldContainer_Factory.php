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
 * This factory is used to create instances of I2CE_Form objects from the form name.
 * @package I2CE 
 * @access public
 */
abstract class I2CE_FieldContainer_Factory extends I2CE_Fuzzy {

    /**
     * @var I2CE_FormFactory The single instance of this class.
     */
    static protected $instance;
        
    /**
     * Return the instance of this factory and create it if it doesn't exist.
     */
    static public function instance() {
        //for php 5.3 this can just be static::$instance, but for 5.2 we need to repeat this function in every sub-class b/c there is no LSB
        if ( ! self::$instance instanceof I2CE_FieldContainer_Factory ) {
            self::$instance = new I2CE_FieldContainer_Factory();
        }
        return self::$instance;
    }

    
    /**
     * Checks to see if the given field container name has been registered.
     * @param string $name The name of the field container
     * @return boolean
     */
    abstract public function exists( $name );

    /**
     * get the available names for this field container
     *@returns array with values the field container  names
     */
    abstract public function getNames() ;

    /**
     * Return  type of  container this factory makes
     * @return string 
     */
    abstract public function getContainerType();

    /**
     * Constructor
     */
    protected function __construct() {
    }




    /**
     * Set a meta attribtue for the named field container.
     * @param string $name  The name of the container
     * @param string $key
     * @param mixed $value
     */
    public function setMetaAttribute( $name,$key, $value ) {
        if (!is_string($key)) {
            return;
        }
        $key = explode('/',$key);
        if (count($key) == 0) {
            return;
        }
        if (!array_key_exists($name,$this->meta) || !is_array($this->meta[$name])) {
            return;
        }
        $attrs = &$this->meta[$name];
        while (count($key) > 1) {
            $k = array_shift($key);
            if (!array_key_exists($k,$attrs)) {
                $attrs[$k] = array();
            }
            if ( !is_array($attrs[$k])) {
                return;
            }
            $attrs = &$attrs[$k];
        }
        reset($key);
        $key = current($key);
        $attrs[$key] = $value;
    }

    /**
     * Return the meta value for a given meta attribute of the named field container
     * @param string $name  The name of the container
     * @param string $key
     * @return mixed
     */
    public function getMetaAttribute( $name,$key ) {
        if (!is_string($key)) {
            return null;
        }
        $key = explode('/',$key);
        if (count($key) == 0) {
            return null;
        }
        if (!array_key_exists($name,$this->meta) || !is_array($this->meta[$name])) {
            return null;
        }
        $attrs = $this->meta[$name];
        while (count($key) > 1) {
            $k = array_shift($key);
            if (!array_key_exists($k,$attrs) || !is_array($attrs[$k])) {
                return null;
            }
            $attrs = $attrs[$k];
        }
        reset($key);
        $key = current($key);
        if (!array_key_exists( $key, $attrs)) {
            return null;
        }
        return $attrs[$key];
    }

    /**
     * Return true if a given meta attribute exists for the named field container.
     * @param string $name  The name of the container
     * @param string $key
     * @return boolean;
     */
    public function hasMetaAttribute( $name ,$key ) {
        if (!is_string($key)) {
            return false;
        }
        $key = explode('/',$key);
        if (count($key) == 0) {
            return false;
        }
        if (!array_key_exists($name,$this->meta) || !is_array($this->meta[$name])) {
            return false;
        }
        $attrs = $this->meta[$name];
        while (count($key) > 1) {
            $k = array_shift($key);
            if (!array_key_exists($k,$attrs) || !is_array($attrs[$k])) {
                return false;
            }
            $attrs = $attrs[$k];
        }
        reset($key);
        $key = current($key);
        return array_key_exists( $key, $attrs);
    }


    /**
     * @var protected array $meta Array of meta attributes indexed by named container
     */ 
    protected $meta = array();


    /**
     * @var protected array $fieldData Array of meta attributes indexed by named container
     */ 
    protected $fieldData = array();

    /**
     * Worker method et data needed to create all the fields in this field container
     * @param string $name  The name of the container
     * @returns array.
     */
    abstract protected function _getFieldData($name);


    /**
     * Get data needed to create all the fields in this field container
     * @param string $name  The name of the container
     * @returns array.
     */
    public function getFieldData($name) {
        if (!array_key_exists($name,$this->fieldData)) {
            $this->fieldData[$name] = $this->_getFieldData($name);
        }
        return $this->fieldData[$name];
    }


    /**
     * Clear out the loaded field data because it may be out of date.
     */
    public function clearFieldData( $name ) {
        if ( array_key_exists( $name, $this->fieldData ) ) {
            unset( $this->fieldData[$name] );
        }
    }


    /**
     * Worker method et data needed to create all the fields in this field container
     * @param string $name  The name of the container
     * @returns array.
     */
    abstract protected function _loadMetaAttributes($name);


    /**
     * Get data needed to create all the fields in this field container
     * @param string $name  The name of the container
     * @returns array.
     */
    public function loadMetaAttributes($name) {
        if (!array_key_exists($name,$this->meta)) {
            $this->meta[$name] = $this->_loadMetaAttributes($name);
        }
        return $this->meta[$name];
    }
    
    

    /**
     * Return an instance of a I2CE_FieldContainer from the factory.
     * @param mixed $nameId The a string which is either the name of the field container, the name and id in the form of "$name|$id" or an array with two elements, the first is a name and the second is an id
     * @param boolean $no_cache Defaults to false.  If true then we don't check the cache when the id is non-zero
     * @return I2CE_FieldContianer or null on failure
     */
    public function createContainer($nameId,$no_cache = false) {
        if (is_array($nameId) && count($nameId) == 2) {
            list($name,$id) = $nameId;
        } else if (is_string($nameId)) {
            if (strpos($nameId,'|') === false) { //no id
                $name = $nameId;
                $id = '0';
            } else {
                list($name,$id) = explode('|',$nameId,2);
                if (strlen($id) == 0) {
                    $id = '0';
                }
            }
        } else {
            return null;
        }
        if (!$this->exists($name)) {
            return null;
        }
        if (!array_key_exists($name,$this->containerCache) || !is_array($this->containerCache[$name])) {
            $this->containerCache[$name] = array();
        }        
        if ( ($id === 0 || is_null( $id ) || $id == "" || $id === '0')) {
            $id = '0';
        }
        if (!$no_cache   && $id != '0' && array_key_exists($id,$this->containerCache[$name]) ) {
            return $this->containerCache[$name][$id];
        }
        $containerObj = $this->_createContainer($this,$name,$id);
        if (!$no_cache ) {
            if ($id == '0') {
                if (!array_key_exists('0',$this->containerCache[$name])) {
                    $this->containerCache[$name]['0'] = array();
                }
                $this->containerCache[$name]['0'][] = $containerObj;
            } else {
                $this->containerCache[$name][$id] = $containerObj;
            }
        }
        return $containerObj;
    }


    /**
     * Get the HTMLName for the container obj.  Either of the form "$id" or "0:$counter"
     * @var I2CE_FieldContianer $containerObj
     * @returns string or null on failure
     */
    public function getHTMLName($containerObj)  {
        if (!$containerObj instanceof I2CE_FieldContainer) {
            return null;
        }
        $index = $this->getContainerIndex($containerObj);
        if ($index === null) {
            return null;
        }
        return $this->getContainerType() . $index;
    }

    /**
     * Get the container's index.  Either of the form "$id" or "0:$counter"
     * @var I2CE_FieldContianer $containerObj
     * @returns string or null on failure
     */
    public function getContainerIndex($containerObj)  {
        if (!$containerObj instanceof I2CE_FieldContainer) {
            return null;
        }
        $id = $containerObj->getId();
        if ( ($id === 0 || is_null( $id ) || $id == "" || $id === '0')) {
            $id = '0';
        }
        $name = $containerObj->getName();
        if ($id != '0') {
            return '[' . $name . '][' . $id . ']';
        } else {
            if (!array_key_exists('0',$this->containerCache[$name])) {
                return null;
            }
            foreach  ($this->containerCache[$name]['0'] as $counter=>$cObj) {
                if ($cObj === $containerObj) { //these are the same objects
                    return '[' . $name . '][' . $id . '][' . $counter . ']';
                }
            }
        }
        return null;
    }

    /**
     * Create all containers of the given name from the post variables
     * @param array $post
     * @param string $name
     * @param boolean $populate_on_set_id.  Defaults to false.
     * @returns array of I2CE_FieldContainer
     */
    public function createContainersFromPost($post, $name, $populate_on_set_id = false, $skip_ids = array()) {
        $type = $this->getContainerType();
        if (!is_array($post) || !is_string($name) || strlen($name) == 0 || ! array_key_exists($type,$post) || !is_array($post[$type]) || !array_key_exists($name,$post[$type]) || !is_array($post[$type][$name])) {
            return array();
        }
        $containers = array();
        foreach ($post[$type][$name] as $id=>$data) {
            if ($id == '0') {
                foreach ($data as $anon_data) {
                    $containerObj = $this->createContainer($name );
                    if (!$containerObj instanceof I2CE_FieldContainer) {
                        continue;
                    }
                    $containerObj->setFromPost($anon_data, false);
                    $containers[] = $containerObj;                                    
                }
            } else {
                if (in_array($id,$skip_ids) || !is_array($data)) {
                    continue;
                }
                $containerObj = $this->createContainer($name .'|' , $id);
                if (!$containerObj instanceof I2CE_FieldContainer) {
                    continue;
                }
                $containerObj->setFromPost($data, $populate_on_set_id);
                $containers[] = $containerObj;                
            }

        }
        return $containers;
    }


    /**
     * @var protected array $conatinerCache.  An doubly indexed array of of {I2CE_FieldContainer}s with the  first index being the name of the container and the second being the id
     */
    protected $containerCache = array();
    /**
     * Cleanup any field containers
     */
    public function cleanup() {
        foreach ($this->containerCache as &$containers) {
            foreach ($containers as $id=>&$container) {
                if (is_array($container)) { //should only happen for id  = '0'
                    foreach ($container as $i=>$cont) {
                        $cont->cleanup(false);
                        unset($container[$i]);
                    }
                    continue;
                }
                if (!$container instanceof I2CE_FieldContainer) {
                    continue;
                }
                $container->cleanup(false);
                unset($containers[$id]);
            }
        }
    }
    
    /**
     * Remvoe a given I2CE_FieldContainer from the cache
     * @param I2CE_FieldContainer $containerObj
     */
    public function removeFromCache($containerObj) {
        if (!$containerObj instanceof I2CE_FieldContainer) {
            return;
        }
        if ( ($id = $containerObj->getId())  === '0') {
            return;
        }
        $name = $containerObj->getName();
        if (!array_key_exists($name,$this->containerCache) || !is_array($this->containerCache[$name])) {
            return ;
        }
        if ($id == '0') {
            if (!array_key_exists('0',$this->containerCache[$name])) {
                return;
            }
            foreach  ($this->containerCache[$name]['0'] as $i=>$cObj) {
                if ($cObj === $containerObj) { //these are the same objects
                    unset($this->containerCache[$name]['0'][$i]);
                    break;
                }
            }
        } else {
            if (!array_key_exists($id,$this->containerCache[$name]) && array_key_exists('0',$this->containerCache[$name]) && is_array($this->containerCache[$name]['0'])) {
                foreach  ($this->containerCache[$name]['0'] as $i=>$cObj) {
                    if ($cObj === $containerObj) { //these are the same objects.  started as anonymous then got saved
                        unset($this->containerCache[$name]['0'][$i]);
                        break;
                    }
                }
                return;
            }
            unset($this->containerCache[$name][$id]);
        }
    }
    


    /**
     * Worker method to create an instance of am I2CE_FieldContainer from the factory.
     * @param I2CE_FieldContainer_Factory $factory
     * @param string $name The name  of the field container
     * @param string $id The id of the field container.  Defaults to '0'
     * @return I2CE_FieldContainer or null on failure
     */
    abstract protected function _createContainer($factory,$name,$id = '0');


    /**
     * Get the fields for the named container
     * @param string $name the name of the form
     * @param mixed  $restict. Defaults to the empty array. If non-empty is data to restrict the field names returns.
     * @returns associative array of string.  
     */
    public function getFieldNames($name,$restrictions = array()) {
        $containerObj = $this->createContainer($name);
        if (!$containerObj instanceof I2CE_FieldContainer) {
            return array();
        }
        $fieldNames = $containerObj->getFieldNames();        
        foreach ($restrictions as $restriction=>$val) {
            foreach ($fieldNames as $i=>$fieldName) {
                $fieldObj = $containerObj->getField($fieldName);
                if (!$fieldObj instanceof I2CE_FormField) {
                    unset($fieldNames[$i]);
                    continue;
                }
                if (!$fieldObj->hasOption($restriction) || $fieldObj->getOption($restriction) != $val) {
                    unset($fieldNames[$i]);
                    continue;
                }
            }
        }
        $containerObj->cleanup();
        return $fieldNames;
    }
   


    
    

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
