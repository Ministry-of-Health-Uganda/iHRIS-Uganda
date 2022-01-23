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
class I2CE_FormFactory extends I2CE_FieldContainer_Factory {


 /**
     * Return  type of  container this factory makes
     * @return string
     */
    public function getContainerType() {
        return 'form';
    }
    
    /**
     * @var I2CE_FormFactory The single instance of this class. //maybe not needed when updating to php 5.3
     */
    static protected $instance;

     /**
      * Return the instance of this factory and create it if it doesn't exist.
      */
     static public function instance() {
         //function not needed when updating to php 5.3
         if ( ! self::$instance instanceof I2CE_FormFactory ) {
             self::$instance = new I2CE_FormFactory();
         }
         return self::$instance;
     }

     /**
      * @var array The classes that have been registered with the factory.
      */
     protected $classes;


     public function __construct() {
         I2CE_ModuleFactory::callHooks( 'formfactory_pre_construct');
         $this->classes = I2CE::getConfig()->modules->forms->forms;
         $this->classHierarchy= array();
         parent::__construct();
         I2CE_ModuleFactory::callHooks( 'formfactory_post_construct');
     }

     /**
      * Checks to see if the given form has been registered.
      * @param string $form The name of the form.
      * @return boolean
      */
     public function exists( $form ) {
         return isset($this->classes->$form);
     }

     /**
      * get the available forms.
      *@returns array with values the form name
      */
     public function getNames() {
         return $this->classes->getKeys();
     }


     /**
      * get the available forms.
      *@returns array with values the form name
      */
     public function getForms() {
         return $this->getNames();
     }





     /**
      * Get the class hierarchy associated to a class.
      * @returns array The keys of the array range from 0 to N.  The value of key 0 is the class associated to the form.  The last key is has value 'I2CE_Form'.  
      * Returns null in error.
      */
     public  function getClassHierarchy($form) {
         if ( !$this->exists( $form ) ) {
             //I2CE::raiseError( "Unknown form: $form");
             return null;
         }
         if (!array_key_exists($form,$this->classHierarchy) || !is_array($this->classHierarchy[$form])) {
             $this->classHierarchy[$form] = array();
             $class = null;
             $this->classes->setIfIsSet($class,$form . '/class');
             $formClass = I2CE::getConfig()->modules->forms->formClasses;
             while ((!empty($class)) && ($class != 'I2CE_Fuzzy')) {
                 if (!isset($formClass->$class)) {
                     I2CE::raiseError("Could not find the form $form's class $class information in it's class hierarchy");
                     return null;
                 }
                 $this->classHierarchy[$form][] = $class;
                 if (!isset($formClass->$class->extends)) {
                     I2CE::raiseError("Class extension information is absent for $class");
                     return null;
                 }
                 $class = $formClass->$class->extends;
             }
         }
         return $this->classHierarchy[$form];
     }



     public function getClassName($form) {
         if (I2CE::getConfig()->is_scalar("/modules/forms/forms/$form/class")) {
             return I2CE::getConfig()->traverse("/modules/forms/forms/$form/class");
         } else {
             return false;
         }
     }

     public function getDisplayName($form) {
         $disp = $form;
         I2CE::getConfig()->setIfIsSet($disp,"/modules/forms/forms/$form/display");
         return $disp;
     }





    /**
     * Get data needed to create all the fields in this field container
     * @returns array.
     */
    protected function _getFieldData($name) {
        $data = array();
        if   ( ($class =$this->getClassName($name))) {
            $this->getFormFieldsData( $class, $data );
        }
        return $data;
    }


    /**
     * Recursive method to rraverse the class hierarchy to get the field data from magic data
     * @param string $class the form class
     * @param array &$data  array indexed by field names where data on the fields are stored
     */
    protected function getFormFieldsData($class, &$data) {
        if (!empty($class) &&  $class != 'I2CE_Form') {
            //suppose this form has inheritince: I2CE_FormB extends I2CE_FormA extends I2CE_Form
            //calling recusrively at the beginning means that for a field, $field
            //we start at I2CE_Form and look for field data for $field
            //next we look  at I2CE_FormA and look for field data for $field and overwrite anything from I2CE_Form with merge recrusive
            //next we look  at I2CE_FormB and look for field data for $field and overwrite anything from I2CE_Form and I2CE_FormA with merge recrusive
            $this->getFormFieldsData( get_parent_class($class),$data );
        }
        $fieldConfigs = I2CE::getConfig()->traverse("/modules/forms/formClasses/$class/fields",false);
        if ( !$fieldConfigs instanceof I2CE_MagicDataNode) {            
            return;
        }
        foreach ($fieldConfigs  as $field=>$fieldConfig) {
            if (! $fieldConfig instanceof I2CE_MagicDataNode) {
                I2CE::raiseError("Expected non-scalar magic data node at: " . $fieldConfigs->getPath(false) . "/" . $field . " was not found");
                continue;
            }
            if(array_key_exists($field,$data) && is_array($data[$field])) {
                I2CE_Util::merge_recursive($data[$field], $fieldConfig->getAsArray());                
            }  else {
                $data[$field] = $fieldConfig->getAsArray();
            }
        }
    } 


   
    /**
     * Returns the array of attributes for this container
     * @param string $name
     * @returns array
     */
    protected function _loadMetaAttributes($name) {
        $attr =  array();
        if   ( ($class =$this->getClassName($name))) {
            $this->getAttributeData( $class, $attr );
        }
        return $attr;
    }

    /**
     * Recursive method to rraverse the class hierarchy to get the meta data for the form magic data
     * @param string $class the form class
     * @param array &$data  array 
     */
    protected function getAttributeData($class, &$attr) {
        if (!empty($class) &&  $class != 'I2CE_Form') {
            //suppose this form has inheritince: I2CE_FormB extends I2CE_FormA extends I2CE_Form
            //calling recusrively at the beginning means that for a field, $field
            //we start at I2CE_Form and look for field data for $field
            //next we look  at I2CE_FormA and look for field data for $field and overwrite anything from I2CE_Form with merge recrusive
            //next we look  at I2CE_FormB and look for field data for $field and overwrite anything from I2CE_Form and I2CE_FormA with merge recrusive
            $this->getAttributeData( get_parent_class($class),$attr );
        }
        $classAttr = array();
        if ( I2CE::getConfig()->setIfIsSet($classAttr,"/modules/forms/formClasses/$class/meta",true)) {    
            I2CE_Util::merge_recursive($attr, $classAttr);
        }
    }


  
    /**
     * Return an instance of a I2CE_Form from the factory.
     * @param mixed $nameId The a string which is either the name of the field container, the name and id in the form of "$name|$id" or an array with two elements, the first is a name and the second is an id
     * @param boolean $no_cache Defaults to false.  If true then we don't check the cache when the id is non-zero
     * @return I2CE_From or null on failure
     */
    public function createForm($nameId,$no_cache = false) {
        return $this->createContainer($nameId, $no_cache);
    }


    /**
     * Worker method to create an instance of an I2CE_Form from the factory.
     * @param I2CE_FieldContainer_Factory $factory
     * @param string $form The form  of the field container
     * @param string $id The id of the field container.  Defaults to null
     * @return I2CE_FieldContainer or null on failure
     */     
    protected function _createContainer($factory,$form,$id = '0') {
        $class = $this->getClassName($form);
        if ($class === false) {
            I2CE::raiseError("Form $form has not class");
            return null;
        }
        if (!class_exists($class)) {
            I2CE::raiseError("Class $class is not defined");
            return null;
        }        
        $reflClass     = new ReflectionClass($class);
        if ($reflClass->isAbstract()) {
            I2CE::raiseError("Class $class is abstract");
            return null;
        }        
        $obj = new $class( $this , $form, $id );
        if (!($obj instanceof I2CE_Form)) {
            I2CE::raiseError( "$form is not a subclass of I2CE_Form");
            return null;
        }
        if (!empty($this->classes->$form->display)) {
            $obj->setDisplayName( $this->classes->$form->display );
        } else {
            $obj->setDisplayName( $this->classes->$form->class );
        }
        return $obj;
    }


        

    /**
     * Call a static function in the form's class object.
     * @param string $form The name of the form.
     * @param string $func The function to be called.
     * @param array $args The arguments to pass to the function.
     */
    public function callStatic( $form, $func, $args=array() ) {
        $class = $this->getClassName($form);
        if ($class !== false) {
            if ($args !== null) {
                return call_user_func_array( array( $class, $func ), $args );                 
            } else {
                return call_user_func( array( $class, $func ) );                 
            }
        }
        //I2CE::raiseError( "callStatic: Unknown form: $form");
        return null;
    } 
        
    /**
     * Return a static variable from the form's class object.
     * @param string $form The name of the form.
     * @param string $var The name of the variable.
     * @param boolean $const Set to true to get a constant instead of a variable
     */
    public function getStatic( $form, $var, $const=false ) {
        $class = $this->getClassName($form);
        if ($class === false) {
            return null;
        }
        $val = null;
        eval( '$val = ' . $class . '::' . ( !$const ? '$' : '' ) . $var . ';' );
        return $val;
    }

    
    /**
     * Shortcut to retrieving a constant value from a form's class object.
     * @param string $form The name of the form.
     * @param string $var The name of the constant.
     */
    public function getConst( $form, $var ) {
        return $this->getStatic( $form, $var, true );
    }
    

        

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
