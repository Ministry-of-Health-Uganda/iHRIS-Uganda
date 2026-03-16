<?php 
/**
 * © Copyright 2008, 2009 IntraHealth International, Inc.
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
 *  I2CE_Module_FormStorage
 * @package I2CE
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software Foundation; either 
 * version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
 * that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
 * received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
 * @version 3.2
 * @since 3.2
 * @access public
 */


class I2CE_FormStorage extends I2CE_Module {



    /**
     * Method called when the module is enabled for the first time.
     * @param boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        I2CE::raiseError("Initializing Form Storage Mechanism");
        if (!I2CE_Util::runSQLScript('initialize_form_store.sql')) {
            I2CE::raiseError("Could not initialize I2CE form history storage tables");
            return false;
        }
        return true;
    }


    /**
     * Upgrade module method
     * @param string $old_vers
     * @param string $new_vers
     */
    public function upgrade($old_vers,$new_vers) {
        if (I2CE_Validate::checkVersion($old_vers,'<','4.0.11.1')) {            
            if (!I2CE_Util::runSQLScript('initialize_form_store.sql')) {
                I2CE::raiseError("Could not initialize I2CE form history storage tables");
                return false;
            }
        }
        if (I2CE_Validate::checkVersion($old_vers,'<','4.1.4.1') && !$this->upgradeFormStore()) {
            I2CE::raiseError("Could not upgrade I2CE form history storage tables");
            return false;
        }
        return true;
    }


    protected function hasColumn($col, $table) {        
        if ($table[0] != '`') {
            $table = '`'  . $table . '`';
        }
        $db = I2CE::PDO();
        $database = I2CE_PDO::details('dbname');
        if ($database[0] != '`') {
            $database = '`'  . $database . '`';
        }
        $d_t = $database . '.' . $table;
        try {
            $res = $db->query("SHOW COLUMNS IN $table FROM $database");
            $cols = array();
            while ($row = $res->fetch()) {
                if (!isset($row->field)) {
                    continue;
                }
                $cols[] =  $row->field;
            }
            unset( $res );
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Could determine columns on $d_t");
            return false;
        }
        return in_array($col,$cols);
    }

    /**
     *upgrade the fom history storage table to add version
     * @returns boolean.  true on success
     */
    protected function upgradeFormStore() {
        //first check to see if ther version column is there as it seemed to be on some installs.
        if ($this->hasColumn('version','form_history')) {
            I2CE::raiseError("version column for form_history is already present");
            return true;
        }
        $qry = 'ALTER TABLE `form_history` ADD `version` TINYINT UNSIGNED NOT NULL DEFAULT \'0\'';
        $db = I2CE::PDO();
        try {
            $db->exec($qry);
        } catch ( PDOException $e ) {
            I2CE::pdoError($e, "Could not add version column to form_history table");
            return false;
        }
        return true;
    }

    /**
     * @var array A list of forms with the last count returned for listDisplayFields
     */
    protected static $lastListCount;



    public static function getMethods() {
        return array(
            //form fuuzy methods
            'I2CE_Form->isComponentized' => 'isComponentizedForm',
            //form read fuzzy methods
            'I2CE_Form->addChild' => 'addChild',
            'I2CE_Form->getChildIds' => 'getChildIds',
            'I2CE_Form->getStorage' => 'getStorage',
            'I2CE_Form->isWritable' => 'isWritable',
            'I2CE_Form->populate' => 'populate',
            'I2CE_Form->duplicate' => 'duplicate',
            'I2CE_Form->storeHistory' => 'storeHistory',
            'I2CE_Form->populateChild' => 'populateChild',
            'I2CE_Form->populateChildren' => 'populateChildren',
            'I2CE_Form->populateFirst' => 'populateFirst',
            'I2CE_Form->populateHistory' => 'populateHistory',
            'I2CE_Form->populateLast' => 'populateLast',
            //form write fuzzy methods
            'I2CE_Form->delete' => 'delete',
            'I2CE_Form->updateTimeStamp' => 'updateTimeStamp',
            'I2CE_Form->save' => 'save',
            'I2CE_Form->setChangeType' => 'setChangeType',
            'I2CE_Form->changeID' => 'changeID',

            //form field fuzzy methods
            'I2CE_FormField->save' => 'FF_save',
            'I2CE_FormField->globalFieldUpdate' => 'globalFieldUpdate',
            'I2CE_FormField_INT_GENERATE->save' => 'FF_IG_save',
            'I2CE_FormField_STRING_PASS->save' => 'FF_SP_save',
            'I2CE_FormField->populateHistory' => 'FF_populateHistory',
            'I2CE_FormField_INT_GENERATE->setSequence' => 'FF_IG_setSequence',

            //form factory fuzzy methods
            'I2CE_FormFactory->getRecords' => 'getRecords',
            'I2CE_FormFactory->hasRecord' => 'hasRecord',
            );
    }



    public static function getHooks() {
        return array(

            'validate_formfield'=> 'validate_formfield',
            'validate_form'=> 'validate_form',
            'form_cleanup'=> 'form_cleanup',
            'form_post_changeid'=>'globalChangeParent_hook'
            );         
    }




    /**
     * Hooked method update all all parent fields refering to a given id
     */
    public function globalChangeParent_hook($args) {
        if (!is_array($args)   ||  !array_key_exists('form',$args) || !array_key_exists('oldid',$args) || !array_key_exists('newid',$args)) {
            return;
        }
        return $this->globalChangeParent($args['form'],$args['oldid'],$args['newid']);
    }


    /**
     * method to remap a given id
     * @param string $form
     * @param string $oldid
     * @param string $newid
     */
    public function globalChangeParent($form,$oldid,$newid) {
        $ff = I2CE_FormFactory::instance();
        $where = array(
            'operator'=>'FIELD_LIMIT',
            'style'=>'equals',
            'field'=>'parent',
            'data'=>array('value'=>"$form|$oldid")
            );        //will get all ids where parent=$form|$oldid
        $set_sql = I2CE::PDO()->quote($form .'|'  . $newid);
        $set_func = function($val) use($form,$newid) { return $form.'|'.$newid; };
        if (!is_callable($set_func)) {
            I2CE::raiseError("Could not create parent update funciton");
            return false;
        }
        $succ = true;
        I2CE::raiseError("Changing parent from $form|$oldid to $form|$newid on all forms");
        $allForms = $ff->getForms();
        foreach ($allForms as $f){
            $obj = $ff->createContainer($f);
            $parentField = $obj->getField('parent');
            $succ &= $this->globalFieldUpdate($parentField,$where,$set_func,$set_sql);
        }
        return $succ;
    }





    /**
     *  method to remap all instances of given field on a given form and field
     * @param $fieldObj I2CE_FormField
     * @param array $where Array of where data
     * @param callable $set_func the php used to update the field
     * @param string $set_sql Optional. sql used to update the field.  Defaults to false.  If present  
     * used by DB like form storage mechansims to do a global update rather than a record by record
     */
    public function globalFieldUpdate($fieldObj, $where,$set_func,$set_sql= false) {
        if (!$fieldObj instanceof I2CE_FormField) {
            I2CE::raiseError("Calling global field object not on a field object");
            return false;
        }
        if ($fieldObj->getName() == 'id') {
            I2CE::raiseError("Cannot update the id field using this mechanism");
            return false;
        }
        $container = $fieldObj->getContainer();
        if (!$container instanceof I2CE_Form) {
            I2CE::raiseError("Container object is not a form");
            return false;
        }
        $storage = self::getStorageMechanism($container->getName());
        if (!$storage instanceof I2CE_FormStorage_Mechanism) {
            I2CE::raiseError("form " . $container->getName() . ' does not have valid form storage mechanism');
            return false;
        }
        if (!self::isWritable($container->getName())) {
            return false;
        }
        I2CE_ModuleFactory::callHooks( "form_pre_global_update", array( 'form' => $container));
        $succ = true;
        if ($storage instanceof I2CE_FormStorage_DB  && $set_sql && $storage->hasGlobalFieldUpdateBySQL()) {
            $succ = $storage->globalFieldUpdateBySQL($fieldObj,$where,$set_sql);
        } else if (is_callable($set_func)) {
            $succ = $storage->globalFieldUpdateByFunction($fieldObj,$where,$set_func);
        } else {
            I2CE::raiseError("No valid data given to update field " . $container->getName() . '+' . $fieldObj->getName());
            $succ = false;
        }
        I2CE_ModuleFactory::callHooks( "form_post_global_update", array( 'form' => $container));
        return $succ;
    }


    /**
     * Hooked Function to check if the set of values of a form are unique
     * @param I2CE_Form $formobj
     * @returns boolean
     */
    public function validate_form($form_obj) {
        if (!$form_obj instanceof I2CE_Form) {
            return false;
        }

        $parent_form = $form_obj->getParentForm();
        $parent_id = $form_obj->getParentID();

        $only_child = 0;

        if ( $parent_form ) {
            I2CE::getConfig()->setIfIsSet( $only_child, "/modules/forms/forms/$parent_form/meta/child_forms_limit/" . $form_obj->getName() );
            if ( $only_child > 0 ) {
                $parent_form_id = $parent_form . '|' . $parent_id;
                $found = I2CE_FormStorage::search( $form_obj->getName(), $parent_form_id );
                if ( count($found) >= $only_child && !in_array( $form_obj->getId(), $found ) ) {
                    foreach ($form_obj->getFieldNames() as $field_name) {
                        if ( ! ($field_obj = $form_obj->getField($field_name)) instanceof I2CE_FormField) {
                            continue;
                        }
                        $field_obj->setInvalidMessage("only_child", null, " ($only_child)");
                    }
                }
            }
        }

        if (!$form_obj->hasMeta('unique') || !$form_obj->getMeta('unique')) {
            return true;
        }
        $where = array();
        foreach ($form_obj->getFieldNames() as $field_name) {
            if ( ! ($field_obj = $form_obj->getField($field_name)) instanceof I2CE_FormField) {
                continue;
            }
            $where[] = array(
                'operator'=>'FIELD_LIMIT',
                'style'=>'equals',
                'field'=>$field_name,
                'data'=>array('value'=>$field_obj->getDBValue())
                );
        }
        $parent_form_id = false;
        if (!$parent_form || !$parent_id) {
            $where[] = array(
                'operator'=>'OR',
                'operand'=>array(
                    0=> array(
                        'operator'=>'FIELD_LIMIT',
                        'style'=>'like',
                        'field'=>'parent',
                        'data'=>array('value'=> '%|'  )
                        ),
                    1=> array(
                        'operator'=>'FIELD_LIMIT',
                        'style'=>'like',
                        'field'=>'parent',
                        'data'=>array('value'=> '%|0'  )
                        )
                    )
                );
        } else {
            $parent_form_id = $parent_form . '|' . $parent_id;
        }
        if (count($where) > 1) {
            $where = array(
                'operator'=>'AND',
                'operand'=>$where
                );
        } else if (count($where) == 1) {
            reset($where);
            $where = current($where);
        }

        
        $found = I2CE_FormStorage::search($form_obj->getName(),$parent_form_id,$where,array(),1);
        if (!$found) {
            return true;
        }
        foreach ($form_obj->getFieldNames() as $field_name) {
            if ( ! ($field_obj = $form_obj->getField($field_name)) instanceof I2CE_FormField) {
                continue;
            }
            $field_obj->setInvalidMessage("unique");
        }
        return false;
    }

    /**
     * Hooked Function to check if a field is unique or unique restricted to a certain field
     * @param I2CE_FormField $field_obj
     * @returns boolean
     */
    public function validate_formfield($field_obj) {
        if (!$field_obj->hasOption('unique') || !$field_obj->getOption('unique') 
                || !$field_obj->isValid() 
                || !$field_obj->issetValue() || $field_obj->getDBValue() == "" ) {
            return;
        }
        $where = array(
            'operator'=>'FIELD_LIMIT',
            'style'=>'equals',
            'field'=>$field_obj->getName(),
            'data'=>array('value'=>$field_obj->getDBValue())                            
            );
        $form_obj = $field_obj->getContainer();        
        if (!$form_obj instanceof I2CE_Form) {
            I2CE::raiseError(get_class($form_obj));
            return;
        }
        $names = array();
        $search_parent = false;
        if ( $field_obj->hasOption('unique_field') ) {
            $unique = $field_obj->getOption('unique_field');
            if ( strpos( $unique, ':' ) !== false ) {
                //the value is a mapped thing.  this is handled by hooked mehtod defined in I2CE_Module_List
                return;
            }
            $unique_fields = explode(',',$unique);
            $unique_where = array($where);
            foreach ($unique_fields as $unique_field) {
                if ( $unique_field == "parent" ) {
                    $search_parent = $form_obj->getParent();
                    if ( $search_parent == "" ) {
                        // If there is no parent then we can't validate
                        // by parent so ignore this
                        return;
                    }
                    continue;
                }
                if ( !($unique_field_obj = $form_obj->getField($unique_field)) instanceof I2CE_FormField ) {
                    I2CE::raiseError("Invalid field $unique_field");
                    return;
                }
                if ($unique_field_obj->hasHeader('default')) {
                    $names[] = $unique_field_obj->getHeader('default');
                } else {
                    $names[] = $unique_field_obj->getName();
                }
                if ($unique_field_obj->isValid()) {
                    $unique_where[] = array(
                        'operator'=>'FIELD_LIMIT',
                        'style'=>'equals',
                        'field'=>$unique_field_obj->getName(),
                        'data'=>array('value'=>$unique_field_obj->getDBValue())
                        );
                } else {
                    $unique_where[] = 
                        array(
                            'operator'=>'OR',
                            'operand'=>array(
                                0=> array(
                                    'operator'=>'FIELD_LIMIT',
                                    'style'=>'equals',
                                    'field'=>$unique_field_obj->getName(),
                                    'data'=>array('value'=>$unique_field_obj->getDBValue())
                                    ),
                                1=>array(
                                    'operator'=>'FIELD_LIMIT',
                                    'style'=>'null',
                                    'field'=>$unique_field_obj->getName()
                                    )
                                )
                            );
                }
                    
                    
            }
            if (count($unique_where) > 1) { //we added somehitng
                $where = array(
                    'operator'=>'AND',
                    'operand'=>$unique_where
                    );
            }
        }
        $found = I2CE_FormStorage::search($form_obj->getName(),$search_parent,$where,array(),1);
        if ( $found !== false  && (''.$found) != (''.$form_obj->getId()) )  {
            I2CE::raiseMessage("found is $found and id is " . $form_obj->getId() . " and names are " . print_r( $names, true ) );
            if (count($names) > 1) {
                $field_obj->setInvalidMessage('unique_fields', null, ' '.implode(', ', $names));
                //$field_obj->setInvalid("This must be unique and another record has this value for the given value of the fields " .implode(',',$names) );
            } else if (count($names) == 1) {
                $field_obj->setInvalidMessage('unique_field', null, ' '.implode(', ', $names));
                //$field_obj->setInvalid("This must be unique and another record has this value for the given value of the field " .implode(',',$names) );
            } else {
                $field_obj->setInvalidMessage('unique');
                //$field_obj->setInvalid("This must be unique and another record has this value." );
            }
        }
    }

        
    /**
     * Move data for a form from one storage mechansim to another
     * @param string $form The form 
     * @param I2CE_FormStorage_Mechansim $source
     * @param I2CE_FormStorage_Mechansim $target
     * @param I2CE_User $user  The user object used to save the data to the target storage mechanism
     * @param mixed $ids.  An id or an  array of the ids we wish to move.  Defaults to null, in which case all
     * id's are moved
     * @returns boolean.  true on success
     */
    public static function moveData($form, $source,$target,$user, $ids = null) {
        if ( (!$source instanceof I2CE_FormStorage_Mechanism) || (!$target instanceof I2CE_FormStorage_Mechanism)) {
            I2CE::raiseError("Invliad source or target for moving data for $form:" . get_class($source) . " / " . get_class($target));
            return false;
        }
        if (is_scalar($ids)) {
            $ids = array($ids);
        }
        if (!is_array($ids)) {
            $ids = $source->search($form,false);
            if (!is_array($ids)) {
                I2CE::raiseError("Could not get ids for form $form using " . get_class($source));
                return false;
            }
        }
        I2CE::raiseError("Moving from " . get_class($source) . " to " . get_class($target) . " the following ids:\n" . implode(" ", $ids));
        $factory = I2CE_FormFactory::instance();
        $success =true;
        $self = I2CE_ModuleFactory::instance()->getClass('forms-storage');;
        foreach ($ids as $id) {
            I2CE::raiseError("Attempting to move $id");
            $formObj = $factory->createContainer(array($form , $id));
            if (!$formObj instanceof I2CE_Form) {
                I2CE::raiseError("Could not instantitate form $form");
                return false;
            }
            if (!$self->_populate($formObj,$source   )) {
                I2CE::raiseError("Could not populate $form|$id from " . get_class($source));
                $formObj->cleanup();
                $success =false;
                continue;
            }
            if (!$self->_save($formObj,$user,false,$target)) {
                I2CE::raiseError("Could not save $form|$id to " . get_class($target));
                $formObj->cleanup();
                $success =false;
                continue;
            }
            $formObj->cleanup();
        }
        return $success;
    }

    /**
     * Move data for a form from one storage mechansim to another
     * @param string $form The form 
     * @param I2CE_FormStorage_Mechansim $target
     * @param I2CE_User $user  The user object used to save the data to the target storage mechanism
     * @param mixed $ids.  An id or an  array of the ids we wish to move.  Defaults to null, in which case all
     * id's are moved
     * @returns boolean.  true on success
     */
    public static function exportData($form,$target, $user, $ids = null) {
        $source = self::getStorageMechanism( $form);            
        return self::moveData($form,$source,$target,$user, $ids);
    }


    /**
     * Move data for a form from one storage mechansim to another
     * @param string $form The form 
     * @param I2CE_FormStorage_Mechansim $target
     * @param I2CE_User $user  The user object used to import the data with
     * @param mixed $ids.  An id or an array of the ids we wish to move.  Defaults to null, in which case all
     * id's are moved
     * @returns boolean.  true on success
     */
    public static function importData($form,$source, $user,$ids = null) {
        $target = self::getStorage( $form_name);            
        return self::moveData($form,$source,$target,$user,$ids);
    }


    /**
     * Use method to duplicate a form object.
     * @param I2CE_Form $form
     * @param boolean $recurse. Defaults to false.  If true we duplicate all children, and {@param $save is forced to be true}
     * @param boolean $save. Defaults to true.  If true we save the form at the same time we duplicate it.
     * @param string $parentid.  Defaults to null in which case the parent id of the duplicated form is not set
     * @param I2CE_User $user, the user object to save.  Defaults to null.
     * @returns mixed false on error or I2CE_Form, the duplicated form 
     */
    public function duplicate( $form, $recurse = false, $save = true, $parentid = null, $user = null ) {
        if (!$form instanceof I2CE_Form) {
            I2CE::raiseError("Not A form");
            return false;
        }
        $form->populate();
        $save |=  $recurse;//if we recurse, we must save
        $ff = I2CE_FormFactory::instance();
        $newForm = $ff->createForm($form->getName());
        if (!$newForm instanceof I2CE_Form) {
            I2CE::raiseError("Could create form " . $form->getName() . "to duplicate ");
            return false;
        }
        foreach ($form as $field) {
            if (!$field instanceof I2CE_FormField) {
                continue;
            }
            $newField = $newForm->getField($field->getName());
            if (!$newField instanceof I2CE_FormField) {
                I2CE::raiseError("Could not duplicate field " . $field->getName() . " of form " . $form->getName());
                return false;
            }
            $newField->setFromDB($field->getDBValue());
        }
        if ($parentid !== null) {
            $newForm->setParent($parentid);
        }
        if ($save) {
            if (!$user instanceof I2CE_User) {
                $user = new I2CE_User();
            }
            $newForm->save($user);
            if ($recurse) {
                $child_forms = $form->getChildForms();
                $form->populateChildren($child_forms);
                foreach ( $form->getChildren() as $children) {
                    foreach ($children as $childForm) {
                        $childForm->duplicate(true,true, $newForm->getNameID(),$user);
                    }
                }
            }
        }
        return $newForm;
    }
    


     /**
      * static function to determine if a form is componentized
      * @param string $form
      * @returns boolean
      */
     public static function isComponentized($form) {
         if (!is_string($form) || strlen($form) == 0) {
             return false;
         }
         $storage = self::getStorage($form);
         if (!$storage) {
             return false;
         }
         $componentized = 0;
         I2CE::getConfig()->setIfIsSet($componentized, "/modules/forms/storage_options/$storage/componentized");
         return $componentized == 1;
     }

     /**
      *A cached list of componentized forms.
      * @var protected static array of string,  the form names
      */
     protected static $componentized_forms;
     /**
      * static function which gets a list of all componentized forms
      * @param boolean $use_cache.  Use the cached value of the list for componentized forms if possible.  Defualts to true
      * @returns array of string, the componentized forms
      */
     public static function getComponentizedForms($use_cache = true) {
         if (!$use_cache || !is_array(self::$componentized_forms)) {
             $components = array();
             $formsConfig = I2CE::getConfig()->traverse("/modules/forms/forms");         
             $forms = $formsConfig->getKeys();
             foreach ($forms  as $form) {
                 if (!self::isComponentized($form)) {
                     continue;
                 }
                 $components[] = $form;
             }
             self::$componentized_forms = $components;
         }
         return self::$componentized_forms;
     }


    /**
     * @var array An array with a cache of the storage type for a form.
     */
    static protected $storage;





    /**
     * Release the form storage associated to a form
     * @param mixed $form.  Either a string, theform name, or an I2CE_Form
     * @return string
     */
    static public function releaseStorage( $form) {
        if ($form instanceof I2CE_Form) {
            $form = $form->getName();
        }
        if (!is_string($form)
            || !is_array(self::$storage)
            || !array_key_exists($form,self::$storage)
            || ! ($mech = self::getStorageMechanism($form)) instanceof I2CE_FormStorage_Mechanism
            ) {
            return;
        }
        $mech->release($form);
    }



    /**
     * Return the storage class for the given form.
     * @param string $form_name
     * @param boolean $no_cache Check from the source, not the cached array
     * @return string
     */
    static public function getStorage( $form_name, $no_cache = false ) {
        if ($form_name instanceof I2CE_Form) {
            $form_name = $form_name->getName();
        }
        if ( !is_array( self::$storage ) ) {
            self::$storage = array();
        }        
        if ( $no_cache || !array_key_exists( $form_name, self::$storage ) ) {            
            $type = 'entry';
            $form_config = I2CE::getConfig()->traverse("/modules/forms/");
            if (!$form_config->setIfIsSet( $type,  "forms/" . $form_name . "/storage" )) {
                $form_config->setIfIsSet($type, "storage/default");
            }
            $not_assignable = false;
            I2CE::getConfig()->setIfIsSet( $not_assignable, "/modules/forms/storage/not_assignable/$type" );
            if ( $not_assignable ) {
                I2CE::raiseError( "Invalid form storage type was used ($type).  You must set the storage to something else." );
                return '';
            } else {
                self::$storage[$form_name] = $type;
            }
        }
        return self::$storage[$form_name];
    }


    /**
     * @var array An array with a cache of the storage mechanisms 
     */
    static protected $storageMechs;


    /**
     * @param string $form_name
     * @param boolean $no_cache Check from the source, not the cached array
     * @returns I2CE_FormStorage_Mechanism or false on failure
     */
    public static function getStorageMechanism( $form_name, $no_cache = false ) {
        $storage = self::getStorage( $form_name, $no_cache );
        if ( ($mech = self::getMechanismByStorage( $storage ))===false) {
            I2CE::raiseError("Could not get $storage mechansim for $form_name");
        }
        return $mech;
    }

    /**
     * Returns the storage mechanism for the given storage type.
     * @param string $storage
     * @return I2CE_FormStorage_Mechanism or false on failure
     */
    public static function getMechanismByStorage( $storage ) {
        if ( !is_array( self::$storageMechs ) ) {
            self::$storageMechs = array();
        }
        if (!array_key_exists($storage, self::$storageMechs) || !self::$storageMechs[$storage] instanceof I2CE_FormStorage_Mechanism) {
            $mechanism = 'I2CE_FormStorage_' . $storage;
            if (!class_exists($mechanism)) {
                I2CE::raiseError("Form storage mechanism $mechanism does not exist");
                return false;
            }
            if ( !is_subclass_of($mechanism,'I2CE_FormStorage_Mechanism')) {
                I2CE::raiseError("Form storage mechanism " . $mechanism . " does not subclass I2CE_FormStorage_Mechanism");
                return false;
            }
            $options = array();
            I2CE::getConfig()->setIfIsSet($options,"/modules/forms/storage_options/$storage",true);
            self::$storageMechs[$storage] = new $mechanism($storage,$options);
        }
        return self::$storageMechs[$storage];
    }

    /**
     * Save the given fields to the given magic data node so it can later
     * be referenced when migrating the form.
     * @param array $forms An array of forms with an array of fields for each:
     *                     array( "form_name" => array( "field1", "field2" ) );
     * @param string $migrate_path The path in magic data to save the data
     */
    public static function storeMigrateData( $forms, $migrate_path ) {
        $migrate_node = I2CE::getConfig()->traverse( $migrate_path, true, false );
        foreach( $forms as $form => $field_list ) {
            $form_storage = self::getStorageMechanism( $form );
            $migrate_data = $form_storage->listFields( $form, $field_list );
            foreach( $migrate_data as $id => $fields ) {
                foreach( $fields as $key => $value ) {
                    $migrate_node->fields->$form->$id->$key = $value;
                }
                $migrate_node->fields->$form->$id->unpopulate( true, true );
            }
        }
        $migrate_node->unpopulate( true, true );
        unset( $migrate_node );
    }
    /**
     * Get the old value for a field that has been migrated.
     * @param I2CE_MagicDataNode $migrate_node
     * @param string $form
     * @param mixed $old_id
     * @param string $field
     * @param mixed $map_form
     * @return array array( 'map_form', 'old_value' );
     */
    public static function getOldMigratedValue( $migrate_node, $form, $old_id, $field, $map_form ) {
        if ( !$migrate_node instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError( "Can't get migrate data without a valid magic data node." );
            return null;
        }
        $old_value = null;
        $return_arr = array();
        if ( is_array( $map_form ) ) {
            foreach( $map_form as $old_field => $old_map_form ) {
                $migrate_node->setIfIsSet( $old_value, "fields/$form/$old_id/$old_field" );
                if ( empty( $old_value ) ) {
                    continue;
                } else {
                    $return_arr = array( $old_map_form, $old_value );
                    break;
                }
            }
            if ( count( $return_arr ) == 0 ) {
                I2CE::raiseError( "Couldn't find old value for: $form $old_id " . print_r( $map_form, true ) );
            }
        } else {
            $migrate_node->setIfIsSet( $old_value, "fields/$form/$old_id/$field" );
            $return_arr = array( $map_form, $old_value );
        }
        if ( $map_form == "currency" ) {
            $old_arr = explode( "|", $old_value, 2 );
            if ( count( $old_arr ) != 2 ) {
                $old_amt = 0;
            } else {
                $old_value = $old_arr[0];
                $old_amt = $old_arr[1];
            }
            $return_arr[1] = $old_value;
            $return_arr[2] = $old_amt;
        }
        /*
        if ( !$old_value ) {
            I2CE::raiseError( "Failed to get old value for $old_id $map_form $field '$old_value'" );
        }
        */
        return $return_arr;
    }

    /**
     * Get the new migrated value for from the old value.
     * @param I2CE_MagicDataNode $migrate_node
     * @param string $map_form
     * @param mixed $old_id
     * @param integer $old_amt For currency data this is the amount
     * @return mixed
     */
    public static function getNewMigratedValue( $migrate_node, $map_form, $old_value, $old_amt = 0 ) {
       $map_form_storage = self::getStorage( $map_form, true );
        if ( $map_form_storage == "entry" ) {
            $new_value = $map_form . "|" . $old_value;
        } else {
            $migrate_node->setIfIsSet( $new_value, "forms/$map_form/$old_value" );
            // Check without the map_form in the value name since 
            // newer values are returned this way.
            if ( !$new_value ) {
                $tmp_value = str_replace( $map_form."|", "", $old_value );
                $migrate_node->setIfIsSet( $new_value, "forms/$map_form/$tmp_value" );
            }
        }
        if ( $new_value && $map_form == "currency" ) {
            $new_value .= "=" . $old_amt;
        }
        return $new_value;
    }

   
    /**
     * Get the new value for a field that has been migrated.
     * @param I2CE_MagicDataNode $migrate_node
     * @param string $form
     * @param mixed $old_id
     * @param string $field
     * @param mixed $map_form
     * @return string
     */
    public static function getMigratedValue( $migrate_node, $form, $old_id, $field, $map_form ) {
        if ( !$migrate_node instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError( "Can't get migrate data without a valid magic data node." );
            return null;
        }
        $old_data = self::getOldMigratedValue( $migrate_node, $form, $old_id, $field, $map_form );
        if ( count( $old_data ) == 1 ) {
            return array( 'old_value' => $old_data[0], 'new_value' => null );
        } elseif ( count( $old_data ) == 2 ) {
            $new_value = self::getNewMigratedValue( $migrate_node, $old_data[0], $old_data[1] );
        } elseif( count( $old_data ) == 3 ) {
            $new_value = self::getNewMigratedValue( $migrate_node, $old_data[0], $old_data[1], $old_data[2] );
        } else {
            I2CE::raiseError( "Invalid return from getOldMigratedValue: " . print_r( $old_data, true ) );
            return array( 'old_value' => null, 'new_value' => null );
        }
        if ( !$new_value && $old_data[1]) {
            I2CE::raiseError( "Failed to get new value for $old_id " . print_r($map_form,true) . " $field '$old_data[1]'" );
        }
        return array( 'old_value' => $old_data[1], 'new_value' => $new_value );
    }

    /**
     * Migrate a given form from one storage method to the current storage method.
     * This should only be used when upgrading a module that moved a form storage
     * from one type to another.
     * @param string $form_name
     * @param string $storage The old storage mechanism
     * @param I2CE_User $user The user object to use to save the new forms.
     * @param string $migrate_path The full path in MagicData to save the old to new mappings.
     * @param string $id_field The old field name to use the value of for the new form id.
     * @param array $skip_fields A list of fields to not migrate to the new form.
     * @param array $migrate_fields A list of fields that have already been migrated and need to use
     *                              the migrate path to convert the data.  Format is array( "field" => "map_form" )
     *                              These forms and fields should have already been passed to {@link storeMigrateData}
     *                              so the data can be retrieved from there.
     * @return boolean
     */
    public static function migrateForm( $form_name, $storage, $user, 
            $migrate_path=false, $id_field = false, $skip_fields = array(),
            $migrate_fields=array(), $callback=null )  {
        I2CE::longExecution();
        // Make sure that when the new form saves it uses the correct storage type if it was already
        // cached by a pre upgrade.
        self::getStorage( $form_name, true );

        $factory = I2CE_FormFactory::instance();
        $factory->clearFieldData( $form_name );
        $old_storage = self::getMechanismByStorage( $storage );
        if (!$old_storage instanceof I2CE_FormStorage_Mechanism) {
            I2CE::raiseError("No storage mechanism found for $storage" );
            return false;
        }
        I2CE::raiseError( "Migrate form $form_name from $storage to " . self::getStorage( $form_name ) );
        $migrate_data = null;
        if ( $migrate_path ) {
            $migrate_data = I2CE::getConfig()->traverse( $migrate_path, true, false );
        } else {
            if ( count($migrate_fields) > 0 ) {
                I2CE::raiseError( "Migrate fields have been passed to migrateForm without a migrate_data path in magic data." );
                return false;
            }
        }

        $use_fields = array();

        $obj = $factory->createContainer( $form_name, true );
        if (!$obj instanceof I2CE_Form) {
            I2Ce::raiseError("Could not instantiate form $form_name");
            return false; 
        }

        foreach( $obj as $field => $fieldObj ) {
            if ( in_array( $field, $skip_fields ) 
                    || array_key_exists( $field, $migrate_fields ) ) {
                continue;
            }
            $use_fields[] = $field;
        }
        $obj->cleanup();
        unset( $obj );

        if ( count($migrate_fields) > 0 && !$migrate_data->__isset( "fields/$form_name" ) ) {
            I2CE::raiseError( "No old migrate data set for $form_name\n" );
        }

        $old_data = $old_storage->listFields( $form_name, $use_fields, true );
        foreach( $old_data as $old_id => $fields ) {
            if ( $migrate_data instanceof I2CE_MagicDataNode ) {
                if ( $migrate_data->__isset( "forms/$form_name/$old_id" ) ) {
                    I2CE::raiseError( "Already migrated $form_name $old_id.  Did something fail earlier?  Skipping it." );
                    continue;
                }
            }
            $obj = $factory->createContainer( $form_name, true );
            foreach ($skip_fields as $skip_field) {
                $obj->removeField($skip_field);
            }
            if ( $id_field && array_key_exists( $id_field, $fields ) ) {
                if ( $callback === null ) {
                    $new_id = $fields[$id_field];
                } elseif ( $callback === true ) {
                    $new_id = strtolower( str_replace( ' ', '_', $fields[$id_field] ) );
                } else {
                    $new_id = call_user_func( $callback, $fields[$id_field] );
                }
                $obj->setId( $new_id );
            }

            foreach( $fields as $key => $value ) {
                if ( !isset($value) ) {
                    continue;
                }
                if ( $key == "parent" ) {
                    $obj->setParent( $value );
                    if ( $migrate_data instanceof I2CE_MagicDataNode ) {
                        $parent_form = $obj->getParentForm();
                        $parent_id = $obj->getParentID();
                        $new_parent = $migrate_data->forms->$parent_form->$parent_id;
                        $obj->setParent( $new_parent );
                        //I2CE::raiseError( "Setting parent for $form_name $old_id to $new_parent (was $value)." );
                    } else {
                        //I2CE::raiseError( "Setting parent for $form_name $old_id to $value." );
                    }
                    continue;
                }
                $fieldObj =  $obj->getField( $key );
                if  (!$fieldObj instanceof I2CE_FormField) {
                    continue;
                }
                $fieldObj->setFromDB( $value );
            }

            foreach( $migrate_fields as $field => $map_form ) {
                $new_value = self::getMigratedValue( $migrate_data, $form_name, $old_id, $field, $map_form );
                if ( is_array( $new_value ) && array_key_exists( 'new_value', $new_value ) 
                        && $new_value['new_value'] ) {
                    $fieldObj =  $obj->getField( $field );
                    if  (!$fieldObj instanceof I2CE_FormField) {
                        continue;
                    }                            
                    $fieldObj->setFromDB( $new_value['new_value'] );
                }
            }

            if ( $obj->save( $user ) ) {
                if ( $migrate_data instanceof I2CE_MagicDataNode ) {
                    $migrate_data->forms->$form_name->$old_id = $form_name . "|" . $obj->getId();
                } else {
                    I2CE::raiseError( "Moved over $form_name: $old_id to " . $obj->getId() );
                }
            } else {
                return false;
            }
            $obj->cleanup();
            unset( $obj );
        }

        if ( $migrate_data instanceof I2CE_MagicDataNode ) {
            $child_forms = I2CE_Form::getChildFormsByForm( $form_name );
            foreach( $child_forms as $child_form ) {
                $children = $factory->getRecords( $child_form );
                $bad_ids =array();
                foreach( $children as $child_id ) {
                    $child_obj = $factory->createContainer( $child_form . '|' . $child_id, true );
                    if (!$child_obj instanceof I2CE_Form) {
                        $bad_ids[] = $child_id;
                        continue;
                    }
                    $child_obj->populate();
                    $old_id = $child_obj->getParentID();
                    $new_id = $migrate_data->forms->$form_name->$old_id;
                    $child_obj->setParent( $new_id );
                    if ( $child_obj->save( $user ) ) {
                    }
                    $child_obj->cleanup();
                    unset( $child_obj );
                }
                if (count($bad_ids) > 0) {
                    I2CE::raiseError("Bad Child ids " . implode(',', $bad_ids) . " for child form $child_form of form $form_name");
                }
                I2CE::raiseError( "Migrated parent for $child_form to $form_name." );
            }
        }

        $migrate_data->unpopulate( true, true );
        return true;

    }

    /**
     * Migrate field data from an old setMap reference to a new MAP form field type.
     * The old data should have been saved in a migrate_data magic data (see {@link storeMigrateData}).
     * @param string $form_name
     * @param array $field_list The list of fields that need to be changed as an array of field => map_form.
     *                          Optionally the field list can be:
     *                          array( "field" => array( "prev_field" => "prev_map_form" ) )
     *                          The field will get the old value from the list of fields (with mapped forms)
     *                          for when the field name was changed.
     * @param string $migrate_path Where the migrate data has been saved.
     * @param I2CE_User $user The user to save the new form field.
     * @return boolean
     */
    static public function migrateField( $form_name, $field_list, $migrate_path, $user ) {
        I2CE::longExecution();
        $factory = I2CE_FormFactory::instance();
        $factory->clearFieldData( $form_name );
        $storage = self::getStorageMechanism( $form_name, true );
        $migrate_node = I2CE::getConfig()->traverse( $migrate_path, true, false );
        $field_node = $migrate_node->traverse( "fields/$form_name" );
        if ( $field_node instanceof I2CE_MagicDataNode ) {
            foreach( $field_node as $id => $fields ) {
                if ( !$fields instanceof I2CE_MagicDataNode ) {
                    I2CE::raiseError( "Invalid data stored in MagicData: $migrate_path fields/$form_name" );
                    return false;
                }
                $obj = $factory->createContainer( $form_name.'|'. $id, true );

                foreach( $field_list as $field => $map_form ) {

                    $new_value = self::getMigratedValue( $migrate_node, $form_name, $id, $field, $map_form );

                    if ( is_array( $new_value ) && array_key_exists( 'old_value', $new_value ) 
                            && !$new_value['old_value'] ) {
                        // No old value so nothing to do here.
                        continue;
                    }
                    if ( is_array( $new_value ) && array_key_exists( 'new_value', $new_value ) 
                            && $new_value['new_value'] ) {
                        $fieldObj = $obj->getField( $field );
                        if (!$fieldObj instanceof I2CE_FormField) {
                            I2CE::raiseError( "Failed to get new field for $form_name+$field " );
                            return false;
                        }
                        $fieldObj->setFromDB( $new_value['new_value'] );
                        $fieldObj->setStaticAttribute( "DBEntry_change_type", I2CE_FormStorage_Mechanism::CHANGE_UPDATE );
                        if ( !$storage->FF_save( $fieldObj, false, $user ) ) {
                            I2CE::raiseError( "Failed to save new field for $form_name $field " . $new_value['new_value'] );
                            return false;
                        }
                    } else {
                        I2CE::raiseError( "Migrate: Failed to get new value for $id $map_form $field" );
                        return false;
                    }
                }
                $obj->cleanup();
                unset( $obj );
                $field_node->$id->unpopulate( true, true );
            }
        }
        $migrate_node->unpopulate( true, true );
        return true;

    }


    /**********************
     *
     *  "instance" methods
     *
     **********************/



    /**
     * @param string $form  The form name.
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     * and then we get all forms with parent the given id.
     * @param mixed $where_data array or class implementing ArrayAccess, Iterator, and Countable (e.g. MagicDataNode) . the where data.  
     * @param mixed $ordering. An field or an  array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit. Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.  
     * @returns mixed an array of matching form ids.  However, ff $limit_one is true or 1 or 
     * array ($offset,1) then then we return either the id or false,  if none found or there was an error.
     */
     public static  function search($form, $parent=false, $where_data=array(), $ordering=array(), $limit = false) {                                      
        $storageMechanism = self::getStorageMechanism($form);
        if (!$storageMechanism) {
            I2CE::raiseError('invalid storage mecahnism for ' . $form);
            $limit_one = false;
            if (is_array($limit)) {
                if (count($limit) == 2) {
                    end($limit);
                    if (current($limit) == 1) {
                        $limit_one = true;
                    }
                }
            } else {
                $limit_one = (($limit === true ) || (is_numeric($limit) && $limit == 1 ));
            }
            if ($limit_one) {
                return false;
            } else {
                return array();
            }
        }
        if (is_scalar($ordering)) {
            $ordering =array($ordering);
        }
        return  $storageMechanism->search($form,$parent,$where_data, $ordering, $limit);        
     }


    /**
     * Looks up the dbvalue of the item based on the code.
     * 
     * This is the default method that most implementations of {@link lookup()} use.  It finds the description of
     * the object based on the code and saves it in the {@link cache} and returns it.
     * @param string $form The name of the form in the database.
     * @param integer $id The code of the entry to lookup.
     * @param mixed $fields A field or an array  of fields to look up and return.  Defaults to 'name'
     * @param string $delim The delimiter to put between returned fields if there are more 
     *                      than one.  If false , then we return as an array.  Defaults to '-'
     * @return string or false on failure

     */
     static public function lookupField( $form, $id,$fields =array('name'), $delim='-') {
         $storageMechanism = self::getStorageMechanism($form);
         if (!$storageMechanism) {
             return false;
         }
         if (is_scalar($fields)) {
             $fields = array($fields);
         }
         return $storageMechanism->lookupField( $form, $id, $fields, $delim );
     }
 



    /**
     * Looks up the display value of the item based on the code.
     * 
     * This is the default method that most implementations of {@link lookup()} use.  It finds the description of
     * the object based on the code and saves it in the {@link cache} and returns it.
     * @param string $form The name of the form in the database.
     * @param integer $id The code of the entry to lookup.
     * @param mixed $fields A field or an array  of fields to look up and return.  Defaults to 'name'
     * @param string $delim The delimiter to put between returned fields if there are more 
     *                      than one.  If false , then we return as an array.  Defaults to '-'
     * @return string or false on failure

     */
     static public function lookupDisplayField( $form, $id,$fields =array('name'), $delim=' - ') {
         $storageMechanism = self::getStorageMechanism($form);
         if (!$storageMechanism) {
             return false;
         }
         if (is_scalar($fields)) {
             $fields = array($fields);
         }
         return $storageMechanism->lookupDisplayField( $form, $id, $fields, $delim );
     }






    /**
     * @param string $form The form name
     * @param array $fields of string. The fields we want returned.  Can include the special field 'last_modified' to get the last modification time for any of the fields of that form which is returned in the format  "Y-m-d H:i:s"
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     *    and then we get all forms with parent the given id.
     * @param mixed $where_data. Either I2CE_MagicDataNode or array. contains the  where clause information about this form or a nested      
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *    If it is as an array of two integers, it is the offset and then number of results to limit to.
     * @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *    time greater than or equal to  $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.
     * @param boolean $use_cache Use the cached form for data if it's available.
     * @returns mixed an array with key id's and value and array of values.  the array of values has as keys the fields with their corresponding value.     
     */
     static public  function listFields($form, $fields, $parent = false , $where_data=array(), $ordering=array(), $limit = false, $mod_time = -1, $use_cache=false) { 
         if ( $use_cache && I2CE_ModuleFactory::instance()->isEnabled("CachedForms") ) {
             $storageMechanism = self::getMechanismByStorage( "cached" );
         } else {
             $storageMechanism = self::getStorageMechanism($form);
         }
         if (!$storageMechanism) {
             return array(); 
         }
         if (is_scalar($fields)) {
             $fields = array($fields);
         }
         return $storageMechanism->listFields( $form, $fields, $parent, $where_data, $ordering, $limit, $mod_time);
    }


    /**
     * Lists the display values for a field
     * @param string $form The form name
     * @param array $fields of string. The fields we want returned
     * Can include the special field 'last_modified' to get the last modification time for any of the fields of that form which is returned in the format  "Y-m-d H:i:s"
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     *    and then we get all forms with parent the given id.
     * @param mixed $where_data. Either I2CE_MagicDataNode or array. contains the  where clause information about this form or a nested      
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *    If it is as an array of two integers, it is the offset and then number of results to limit to.
     * @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *    time greater than or equal to  $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.
     * @param boolean $use_cache Use the cached form for data if it's available.
     * @param mixed $user The user id of the user to limit the results to so it only returns results limited to that user.  This can be a single value or an array
     * @returns mixed an array with key id's and value and array of values.  the array of values has as keys the fields with their corresponding value.     
     */
     static public  function listDisplayFields($form, $fields, $parent = false , $where_data=array(), $ordering=array(), $limit = false, $mod_time = -1, $use_cache=false, $user=false ) { 
         if ( $use_cache && I2CE_ModuleFactory::instance()->isEnabled("CachedForms") ) {
             $storageMechanism = self::getMechanismByStorage( "cached" );
         } else {
             $storageMechanism = self::getStorageMechanism($form);
         }
         if (!$storageMechanism) {
             return array();
         }
         if (is_scalar($fields)) {
             $fields = array($fields);
         }
         return $storageMechanism->listDisplayFields( $form, $fields, $parent, $where_data, $ordering, $limit, $mod_time, $user);
    }
     



     /*********************************
      * 
      *        Fuzzy Methods Implementations
      *
      *************************************/

     


     /**
      * Fuzzy method to check if a form instance is componentized
      * @param I2CE_Form $formClass
      * @returns boolean
      */
     public function isComponentizedForm($formClass) {
         if (!$formClass instanceof I2CE_Form) {
             I2CE::raiseError("Invalid argument");
             return false;
         }
         return I2CE_FormStorage::isComponentized($formClass->getName());
         
     }


    /*****************
     *
     *  Reading  fuzzy methods for I2CE_Form
     *
     *********************/    

    /**
     * Use method to populate a form object.
     * @param I2CE_Form $form
     * @param boolean $repopulate. Defaults to false
     * @returns boolean
     */
     public function populate( $form, $repopulate = false) {
         return self::_populate($form,null,$repopulate);
     }
     
     protected $populated_list = array();
        
     /**
      * Hooked form cleanup method
      */
     public function form_cleanup($args) {
         if (!array_key_exists('form',$args)) {
             return;
         }
         if (array_key_exists('remove_from_cache',$args) && !$args['remove_from_cache']) {
             return;
         }
         $this->removeFromPopulatedList($args['form']);
         
     }

     /**
      * remove a form from the "its already been populated cache"
      */
     public function removeFromPopulatedList($form) {
         if (!$form instanceof I2CE_Form) {
             return;
         }
         $formid = $form->getNameId();
         if (array_key_exists($formid,$this->populated_list) && $this->populated_list[$formid]) {
             unset($this->populated_list[$formid]);
         }
    }
        
    /**
     * Internal worker method to populate a form object.
     * @param I2CE_Form $form
     * @param I2CE_FormStorage_Mechanism $storageMechanism.  Default to null which indicates we should use the
     * registered form storage mecahnsim.
     * @param boolean $repopulate. Defaults to false
     * @returns boolean
     */
    public function _populate( $form, $storageMechanism = null, $repopulate = false) {
        if ( $form->getId() === null || $form->getId() == "0" ) {
            return false;
        }
        if (!$storageMechanism instanceof I2CE_FormStorage_Mechanism) {
            $storageMechanism = self::getStorageMechanism($form->getName());
        }
        if (!$storageMechanism) {
            return false;
        }
        $formId = $form->getFormId();
        if (!$repopulate && array_key_exists($formId,$this->populated_list) && $this->populated_list[$formId]) {
            //don't repopulate
            return true;
        }
        I2CE_ModuleFactory::callHooks( "form_pre_populate", array( 'form' => $form ) );
        $storageMechanism->populate($form);
        I2CE_ModuleFactory::callHooks( "form_post_populate", array( 'form' => $form ) );
        $this->populated_list[$formId] =true;
        return true;
    }




        
    /**
     * Populate Last of a form object.
     * @param I2CE_Form $form
     */
    public function populateHistory( $form) {
        $storageMechanism = self::getStorageMechanism($form);
        if (!$storageMechanism) {
            return false;
        }
        return $storageMechanism->populateHistory($form);
    }

        
    /**
     * Populate the last child of a form object based on the given field
     * @param I2CE_Form $form
     * @param array $forms an associative array with keys form names and values field names or ordering array
     */
    public function populateLast( $form, $forms=array()) {
        $storageMechanism = self::getStorageMechanism($form);
        if (!$storageMechanism) {
            return false;
        }
        foreach ($forms as $child_form_name=>$orderby)  {
            $childStorage = self::getStorageMechanism($child_form_name);
            if (!$childStorage) {
                continue;
            }
            if (is_scalar($orderby)) {
                $orderby = array($orderby);
            }
            if (!is_array($orderby)) {
                $orderby = array();
            }
            foreach ($orderby as $i=>&$field) {
                if (!is_string($field) || strlen($field) < 1) {
                    unset($orderby[$i]);
                }
                if ($field[0] == '-') {
                    $field = substr($field,1);
                } else {
                    $field = '-' . $field;
                }
            }
            $ids = $childStorage->getIdsAsChild($child_form_name, $form->getNameId(),$orderby,array(),1);
            if (!is_array($ids) || count($ids) != 1) {
                continue;
            }
            reset($ids);
            $child_form = I2CE_FormFactory::instance()->createContainer($child_form_name.'|'.current($ids));
            if (!$child_form instanceof I2CE_Form) {
                continue;
            }
            $child_form->populate();
            $form->addChildForm($child_form);
        }
        return true;
    }


    /**
     * Populate the first child of a form object based on the given field
     * @param I2CE_Form $form
     * @param array $forms an associative array with keys form names and values field names or ordering array
     */
    public function populateFirst( $form, $forms=array()) {
        $storageMechanism = self::getStorageMechanism($form);
        if (!$storageMechanism) {
            return false;
        }
        foreach ($forms as $child_form_name=>$orderby)  {
            if (is_scalar($orderby)) {
                $orderby = array($orderby);
            }
            if (!is_array($orderby)) {
                $orderby = array();
            }
            $childStorage = self::getStorageMechanism($child_form_name);
            if (!$childStorage) {
                continue;
            }
            $ids = $childStorage->getIdsAsChild($child_form_name, $form->getNameId(),$orderby,array(),1);
            if (!is_array($ids) || count($ids) != 1) {
                continue;
            }
            reset($ids);
            $child_form = I2CE_FormFactory::instance()->createContainer($child_form_name.'|'.current($ids));
            if (!$child_form instanceof I2CE_Form) {
                continue;
            }
            $child_form->populate();
            $form->addChildForm($child_form);
        }
        return true;
    }


    /**
     * Gets the id's for the given child for this form.
     * @param I2CE_Form $form
     * @param  string $child_form_name the child form name 
     * @param mixed $order_by.  A string or array of strings.  The fields to oreder by .  defaults to empty array.
     * @param mixed $limit. Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.  
     * @return array
     */
    public function getChildIds( $form, $child_form_name, $order_by = array(), $where = array(), $limit = false) {
        $storageMechanism = self::getStorageMechanism($child_form_name);
        if (!$storageMechanism ) {
            return array();
        }
        $ret = $storageMechanism->getIdsAsChild($child_form_name, $form->getNameId(),$order_by,$where, $limit);
        if (!is_array($ret)) {
            return  array();            
        }
        return $ret;
    }
    

    /**
     * add  the given child to the form for this object.
     * @param I2CE_Form $form
     * @param string $form_name: The form name to add
     * @param string  the id of this form
     */
    public function addChild( $form, $form_name,$id) {
        if ($form->childFormAdded($form_name,$id)) {
            return true;
        }
        $child_form = I2CE_FormFactory::instance()->createContainer($form_name.'|'. $id );
        if (!$child_form instanceof I2CE_Form) {
            return false;
        }
        $child_form->populate();
        return $form->addChildForm( $child_form);
    }


    

    /**
     * Populate all instances of the given child form for this object.
     * @param I2CE_Form $form
     * @param string $form_name: The form name to populate
     * @param mixed  $order_by  A field or array  of fields to sort by.  Preceded by "-" to sort in reverse order.
     *                    - array( "-start_date", "name" ). Defaults to null in which case if get the default sort
     *               order that is registered for the type (which defaults to none)
     * @param mixed @where an aarray of where information to limit getting the child id's by.   If null, we get
     * the default limits for the type (which defaults to none)
     * @param string $type.  Defaults to 'default'
     * @param mixed $limit. Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.  
     */
    public function populateChild( $form, $form_name, $order_by = null, $where = null, $type = 'default' , $limit = false) {
        if ( !$form_name) {
            I2CE::raiseError("Invalid arguments passed to populateChild: ");
            return;
        }
        if (!is_string($type) || strlen($type) == 0) {
            $type = 'default';
        }
        if (is_scalar($order_by)) {
            $order_by = explode(",",$order_by);
        }
        if (! $form->getName()) {
            I2CE::raiseError("Form has no name");
            return;
        }
        $child_data_path = "/modules/forms/forms/" . $form->getName() . "/meta/child_form_data/$type/$form_name";
        if (I2CE::getConfig()->is_parent($child_data_path)) {
            $child_data = I2CE::getConfig()->$child_data_path;
            if ($where === null && $child_data->is_parent('limits')) {
                $where = $child_data->limits->getAsArray();
            }
            if ($order_by === null && $child_data->is_scalar('order')) {
                $order_by = explode(',',$child_data->order);
            }
        }        
        if (!is_array($where)) {
            $where = array();
        }
        if (!is_array($order_by)) {
            $order_by = array();
        }
        $ids = $form->getChildIds( $form_name, $order_by, $where , $limit);
        foreach( $ids as $id ) {
            $form->addChild($form_name,$id);
        }
    }




    /**
     * Populate the given child form for this object.
     * @param I2CE_Form $form
     * @param mixed $forms.  A string or an  array such, the form names (the child forms to populate)
     *                    - array( "demographic", "contact" )
     * @param array $orderBy An associative array of form names with an array of either string, field to sort by or an array of the fields
     *     Defaults to empty array
     *                    - array( "contact" => "contact_type" )
     * 
     */
    public function populateChildren( $form, $forms, $orderBy=array()) {
        if (is_scalar($forms)) {
            $forms = array($forms);
        }
        if ( !is_array($forms)) {
            I2CE::raiseError("Invalid arguments passed to populateChildren: "  );
            return;
        }
        foreach ($forms as $formName) {
            if (!array_key_exists($formName,$orderBy)) {
                $orderBy[$formName] = array();
            }
            $form->populateChild($formName,$orderBy[$formName]);
        }
    }

    
    
    /****************************************************
     *
     *  Writing fuzzy methods for I2CE_Form and I2CE_FormField
     *
     ******************************************************/


    /**
     *
     * Set the change type for the given form.
     * If this method isn't called then the change type will be I2CE_FormStorage::CHANGE_UPDATE
     * @param I2CE_Form $form
     * @param integer $change_type: the change type to set Defautls to I2CE_FormStorage::CHANGE_UPDATE
     * @param string $field.  Defaults to 'default'.  optional field name to set the change type to.
     */
    public function setChangeType( $form, $change_type = I2CE_FormStorage_Mechanism::CHANGE_UPDATE, $field ='default') {
        if (!$this->isWritable($form)) {
            return true;
        }
        $storageMechanism = self::getStorageMechanism($form);
        if (!$storageMechanism) {
            return false;
        }
        if (!$storageMechanism->isWritable()) {
            return true;
        }        
        $form->setAttribute( "change_type_" . $field, $change_type );
    }


    /**
     * Change the id of the given form
     * @param I2CE_Form $formObj
     * @param mixed $newid
     * @returns boolean. true on success
     */
    public  function changeID( $form,  $newid) {
        if (!$form instanceof I2CE_Form) {
            I2CE::raiseError("Not a form object");
            return false;
        }
        return self::changeFormID($form->getNameID(),$newid);
    }

    
    /**
     * Change the id of the given form
     * @param string $formid, i.e. "$form|$id"

     * @param mixed $newid
     * @returns boolean. true on success
     */
    public static function changeFormID( $formid,   $newid) {
        list( $form, $id ) = array_pad(explode( '|', $formid, 2 ),2,'');
        if (!$form || !$id || (is_string($id) && strlen($id) == 0)) {
            I2CE::raiseError("No passed a valid form id  $form|$id");
            return false;
        }
        if (!$newid || (is_string($newid) && strlen($newid) == 0)) {
            I2CE::raiseError("Invalid new id $newid supplied");
            return false;
        }
        I2CE::raiseError("Attempting to change $formid to $newid");
        $ff = I2CE_FormFactory::instance();
        if (!$ff->hasRecord($form,$id) ) {
            I2CE::raiseError("Cannot change $formid to $form|$newid as $id does not exist");
            return false;
        }
        if ($ff->hasRecord($form,$newid) ) {
            I2CE::raiseError("Cannot change $formid to $form|$newid as $newid already exists");
            return false;
        }
        $storageMechanism = self::getStorageMechanism($form);
        if (!$storageMechanism) {
            I2CE::raiseError("The form " . $form->getName() . " has no storage mechansim");
            return false;
        }
        if (!self::isWritable($form)) {
            I2CE::raiseError("The form " . $form->getName() . " is not writable in "  . get_class($storageMechanism));
            return false;
        }
        I2CE_ModuleFactory::callHooks( "form_pre_changeid", array( 'form' => $form, 'oldid'=>$id, 'newid' => $newid ));
        if (! $storageMechanism->changeID($form, $id, $newid)) {
            return false;
        }
        I2CE_ModuleFactory::callHooks( "form_post_changeid", array( 'form' => $form, 'oldid'=>$id, 'newid' => $newid ));
        return true;
    }



    /**
     * User method to save a form object.
     * @param I2CE_Form $form
     * @param I2CE_User $user 
     * @param boolean $transact Defaults to true.
     * @returns boolean. true on success
     */
    public function save( $form, $user, $transact = true) {
        return self::_save($form,$user,$transact);
    }

    /**
     * Internal worker method to save a form object.
     * @param I2CE_Form $form
     * @param I2CE_User $user 
     * @param boolean $transact Defaults to true.
     * @param I2CE_FormStorage_Mechanism $storageMechanism.  Default to null which indicates we should use the
     * registered form storage mecahnsim.
     * @returns boolean. true on success
     */
    protected function _save($form,$user,$transact = true, $storageMechanism = null) {
        if (!$user instanceof I2CE_User) {
            I2CE::raiseError("No user specified\n");
            return false;
        }
        if (!$storageMechanism instanceof I2CE_FormStorage_Mechanism) {
            $storageMechanism = self::getStorageMechanism($form);
        }
        $storageMechanism = self::getStorageMechanism($form);
        if (!$storageMechanism) {
            return false;
        }
        if (!$this->isWritable($form)) {
            return true;
        }
        I2CE_ModuleFactory::callHooks( "form_pre_save", array( 'form' => $form, 'user' => $user ) );
        I2CE_ModuleFactory::callHooks( "form_pre_save_" . $form->getName(), array( 'form' => $form, 'user' => $user ) );
        $save_result = $storageMechanism->save($form, $user,$transact);
        if (in_array($form->getParentForm(),I2CE::getConfig()->getKeys("/modules/forms/forms")) 
            && ($parentObj = I2CE_FormFactory::instance()->createContainer($form->getParent())) instanceof I2CE_Form) {
            $parentObj->updateTimeStamp($user);
        }
        I2CE_ModuleFactory::callHooks( "form_post_save_" . $form->getName(), array( 'form' => $form, 'user' => $user ) );
        I2CE_ModuleFactory::callHooks( "form_post_save", array( 'form' => $form, 'user' => $user ) );
        return $save_result;
    }


    /**
     * Updates time stamp on given object
     * @param I2CE_Form $form
     * @param int $timestamp.  If not set, defaults to current unix timestamp
     * @returns boolean. true on success
     */
    public function updateTimeStamp($form, $user,$timestamp = null) {
        if ($timestamp === null) {
            $timestamp = time();
        }
        if (!is_int($timestamp) || $timestamp < 0) {
            I2CE::raiseError("Invalid timestamp");
            return false;
        }
        $ff = I2CE_FormFactory::instance();
        $seen =array(); //array to avoid recursion
        while ($form instanceof I2CE_Form) {
            if (in_array($form->getNameId(),$seen)) {
                break;
            }
            if (  $form->getId() == '0') {
                I2CE::raiseError("Cannot update timestamp of object with blank ID");
                return false;
            }
            $seen[] = $form->getNameId();

            if ($this->isWritable($form)) {
                if (! ($storageMechanism = self::getStorageMechanism($form)) instanceof I2CE_FormStorage_Mechanism) {
                    I2CE::raiseError("Invalid form storage mechanism for " . $form->getName);
                    return false;
                }
                I2CE_ModuleFactory::callHooks( "form_pre_save", array( 'form' => $form, 'user' => $user ) );
                I2CE_ModuleFactory::callHooks( "form_pre_save_" . $form->getName(), array( 'form' => $form, 'user' => $user ) );
                if (! ($storageMechanism->updateTimeStamp($form,$timestamp))) {
                    return false;
                }
                I2CE_ModuleFactory::callHooks( "form_post_save_" . $form->getName(), array( 'form' => $form, 'user' => $user ) );
                I2CE_ModuleFactory::callHooks( "form_post_save", array( 'form' => $form, 'user' => $user ) );
            }            
            $form = $ff->createContainer($form->getParent());
        }
        return true;
    }

    /**
     * Delete a form object.
     * @param I2CE_Form $form
     * @param  boolean $transact a flag to use transactions or not. default: true
     * @param boolean $no_history a flag to determine if the record should not go to the deleted_records
     *                             table. default: false
     * @return boolean
     */
    public function delete( $form, $transact = true, $no_history = false) {
        $storageMechanism = self::getStorageMechanism($form);
        if (!$storageMechanism) {
            return false;
        }
        if ( $form->getId() == '0' || $form->getId() == '' ) {
            return false;
        }
        if (!$this->isWritable($form)) {
            return true;
        }
        if (!$no_history && !$form->storeHistory()) {
            I2CE::raiseError("Could not store form hustory");
            return false;
        }
        I2CE_ModuleFactory::callHooks( "form_pre_delete", array( 'form' => $form ) );
        $delete_result = $storageMechanism->delete($form,$transact);
        I2CE_ModuleFactory::callHooks( "form_post_delete", array( 'form' => $form ) );
        return $delete_result;
    }


    /**
     * @var PDOStatemnt $store_stmt  Insert statment for storing form history
     */
    protected $store_stmt =false;

    /**
     * Store the history for a form
     * I2CE_Form $form
     * 
     */
    public function storeHistory($form) {
        if (!$form instanceof I2CE_Form) {
            I2CE::raiseError("Not a form");
            return false;
        }
        if ( $form->getId() == '0' || $form->getId() == '' ) {
            I2CE::raiseError("Invalid form id");
            return false;
        }
        $db = I2CE::PDO();
        if (!$this->store_stmt) {
            try {
                $this->store_stmt = $db->prepare("INSERT INTO form_history (formid,history,date,version) VALUES (?,?,NOW(),1)" );
            } catch ( PDOException $e ) {
                I2CE::pdoError( $e,"Could prepare  store  statment");
                return false;
            }
        }
        $form->populateHistory();
        $history = $form->getHistory(true);
        if (!is_array($history) || count($history) == 0) {
            I2CE::raiseError("No history");
            return false;
        }
        $this->recursiveEncode64($history);
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            $history = json_encode($history,JSON_FORCE_OBJECT);
        } else {
            $history = json_encode($history);
        }
        $formID = $form->getFormID();
        try {
            $this->store_stmt->execute(array($formID,$history));
        } catch ( PDOException $e ) {
            I2CE::pdoError($e,"Could not store form data for $formID");
            return false;
        }
        return true;
    }


    protected function recursiveEncode64(&$data) {
        foreach ($data as $k=>&$val) {
            if (is_scalar($val)) {
                $val = base64_encode($val);
            } else if (is_array($val)) {
                $this->recursiveEncode64($val);
            } else {
                I2CE::raiseError("Skipping $k when encoding");
            }
        }
        unset($val);
    }
        




    /*****************
     *
     *  Form field fuzzy methods
     *
     *
     *********************/


    /**
     * Save the FormField to the database.
     * @param I2CE_FormField $form_field
     * @param boolean $do_check A flag to determine if a check should be made for the same value being saved.
     * @param I2CE_User I2CE_User The user saving this data.
     * @return boolean
     */
    public function FF_save( $form_field, $do_check, $user) {
        if (!$form_field instanceof I2CE_FormField) {
            return false;
        }
        $storageMechanism = self::getStorageMechanism($form_field->getContainer()->getName());
        if (!$storageMechanism) {
            return false;
        }
        if (!$user instanceof I2CE_User) {
            I2CE::raiseError("No user");
            return false;
        }
        if (!$this->isWritable($form_field)) {
            return true;
        }
        return $storageMechanism->FF_save($form_field, $do_check,$user);
    }

    /**
     * Save the FormField to the database.
     * @param boolean $do_check A flag to determine if a check should be made for the same value being saved.
     * @param I2CE_User I2CE_User The user saving this data.
     * @return boolean
     */
    public function FF_IG_save( $form_field, $do_check, $user) {
        if (!$form_field instanceof I2CE_FormField_INT_GENERATE) {
            return false;
        }
        $storageMechanism = self::getStorageMechanism($form_field->getContainer()->getName());
        if (!$storageMechanism) {
            return false;
        }
        if (!$user instanceof I2CE_User) {
            I2CE::raiseError("No user");
            return false;
        }
        if (!$this->isWritable($form_field)) {
            return true;
        }
        if ( $form_field->getGenerate() ) {
            $form_field->setSequence();
        }
        return $storageMechanism->FF_save($form_field, $do_check,$user);
    }


    /**
     * Save the FormField_STRING_PASS to the database.
     * @param I2CE_FormField_STRING_PASS $form_field
     * @param boolean $do_check A flag to determine if a check should be made for the same value being saved.
     * @param I2CE_User I2CE_User The user saving this data.
     * @return boolean
     */
    public function FF_SP_save( $form_field, $do_check,$user) {
        if (!$form_field instanceof I2CE_FormField_STRING_PASS) {
            return false;
        }
        $storageMechanism = self::getStorageMechanism($form_field->getContainer()->getName());
        if (!$storageMechanism) {
            return false;
        }
        if (!$user instanceof I2CE_User) {
            I2CE::raiseError("No user");
            return false;
        }
        if (!$this->isWritable($form_field)) {
            return true;
        }
        if ( strlen( $form_field->getDBValue() ) > 0 ) { 
            //only allow saves of non-empty passwords.   
            return $storageMechanism->FF_save($form_field, $do_check,$user);
        } else {
            return true;
        }
    }



    /**
     * Set the value of this field to the next sequence for the form field.
     * @param I2CE_FormField_INT_GENERATE $form_field
     */
    public function FF_IG_setSequence( $form_field) {
        if (!$form_field instanceof I2CE_FormField_INT_GENERATE) {
            return false;
        }
        $storageMechanism = self::getStorageMechanism($form_field->getContainer()->getName());
        if (!$storageMechanism) {
            return false;
        }
        if (!$this->isWritable($form_field)) {
            return true;
        }
        return $storageMechanism->FF_IG_setSequence($form_field);
    }


    /**
     * Populate the history of entries for the form field if the storage module handles history.
     * @param I2CE_FormField $form_field
     * @return boolean
     */
    public function FF_populateHistory( $form_field) {
        if ($form_field instanceof I2CE_FormField_STRING_PASS) {            
            return false;
        }
        if ($form_field->hasAttribute('populated_history') && $form_field->getAttribute('populated_history')) {
            return true;
        }
        $storageMechanism = self::getStorageMechanism($form_field->getContainer()->getName());
        if (!$storageMechanism) {
            return false;
        }
        $form_field->setAttribute('populated_history',1);
        return $storageMechanism->FF_populateHistory($form_field);
    }


    /*****************
     *
     *  Form factory fuzzy methods
     *
     *
     *********************/


    /**
     * Return an array of all the record ids for a given form.
     * @param I2CE_FormFactory $factory
     * @param string $form_name
     * @param integer $mod_time.  Defaults to  -1.  If non-negative, it is a unix timestamp and we retrieve records modified at or after the given time
     * @param string $parent Defaults to false.  If true, The parent field we want to restrict values to
     * @return array
     */
    public function getRecords( $factory, $form_name, $mod_time = -1,$parent = false) {
        if ( !is_string($form_name) || strlen($form_name)  == 0 ) {
            I2CE::raiseError( "Invalid arguments passed to I2CE_FormFactory::getRecords. ");
            return array();
        }
        $storageMechanism = self::getStorageMechanism($form_name);
        if (!$storageMechanism) {
            I2CE::raiseError("no storage mechanism for $form_name");
            return array();
        }
        $ret =  $storageMechanism->getRecords($form_name, $mod_time, $parent);
        if (!is_array($ret)) {
            I2CE::raiseError("bad results returend for $form_name from " . get_class($storageMechanism));
            return array();
        }
        return $ret;
    }


    /**
     * Checks if the given record exists.
     * @param I2CE_FormFactory $factory
     * @param string $form_name
     * @param string $form_id
     * @return array
     */
    public function hasRecord( $factory, $form_name, $form_id) {
        if ( !is_string($form_name) || strlen($form_name)  == 0 ) {
            I2CE::raiseError( "Invalid arguments passed to I2CE_FormFactory::getRecords. ");
            return false;
        }
        $storageMechanism = self::getStorageMechanism($form_name);
        if (!$storageMechanism) {
            I2CE::raiseError("no storage mechanism for $form_name");
            return false;
        }
        if (!$form_id) {
            return false;
        }
        $ret =  $storageMechanism->hasRecord($form_name, $form_id);
        if (!is_bool($ret)) {
            I2CE::raiseError("bad results returend for $form_name from " . get_class($storageMechanism));
            return false;
        }
        return $ret;
    }


    /**
     *Check to see if this form is considered to be writable.
     * @param mixed $form.  string, I2CE_Form or I2CE_FormField
     */
    public static function isWritable($form) {
        if ($form instanceof I2CE_FormField) {
            $form = $form->getContainer()->getName();
        }
        if ($form instanceof I2CE_Form) {
            $form = $form->getName();
        }
        if (!is_string($form)) {
            return false;
        }
        $storageMechanism = self::getStorageMechanism($form);
        if (!$storageMechanism) {
            return false;
        }
        if (!$storageMechanism->isWritable()) {
            return false;
        }
        if (I2CE::getConfig()->setIfIsSet($read_only,"/modules/forms/forms/$form/read_only")) {
            //the optional read_only attribute is set.  If it evaluates to false then it is writable.
            return ($read_only == false);
        } else {
            //the optional read_only attribute is not set.  so it is writable.
            return true; 
        } 
    }

    /**
     * Add the last results from a search query for a form.
     * @param string $form The form name
     * @param integer $count The number of results.
     */
    public static function setLastListCount( $form, $count ) {
        if ( !is_array( self::$lastListCount ) ) {
            self::$lastListCount = array();
        }
        self::$lastListCount[$form] = $count;
    }

    /**
     * Return the last results from a search query for a form.
     * @param string $form The form name
     * @return integer
     */
    public static function getLastListCount( $form ) {
        if ( is_array( self::$lastListCount ) 
                && array_key_exists( $form, self::$lastListCount ) ) {
            return self::$lastListCount[$form];
        } else {
            I2CE::raiseError( "Trying to query last list count for $form but it hasn't been set." );
            return 0;
        }
    }



}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
