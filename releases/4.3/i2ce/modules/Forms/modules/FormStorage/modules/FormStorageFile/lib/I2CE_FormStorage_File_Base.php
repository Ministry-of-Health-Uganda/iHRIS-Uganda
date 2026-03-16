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
* @package i2ce
* @subpackage forms
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.6
* @since v4.0.6
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_File_Base
* 
* @access public
*/


abstract class I2CE_FormStorage_File_Base extends I2CE_FormStorage_Mechanism {
    
    /**
     * Get the search category
     * @param string $form
     * @returns string
     */
    abstract protected function getSearchCategory($form);

 
    /**
     * Checks to see if this is writalbe
     * @return boolean
     */
    public function isWritable() {
        return false;
    }


    
   

    /**
     * Release any resourced held by this form storage mechanism for the indicated form
     * @param string $form
     */
    public function release($form) {
        if (!is_string($form)) {
            return;
        }
        if (array_key_exists($form,$this->locations)) {
            unset($this->locations[$form]);
        }        
        if (array_key_exists($form,$this->mod_time)) {
            unset($this->mod_time[$form]);
        }        
        parent::release($form);

    }


    /**
     * Array of arrays indexed by form and id.
     */
    protected $locations  = array();


    /**
     * Get the location/data object in the  file for the specified form object
     * @param string $formName
     * @params string $id
     * @returns mixed on success, false on failure.
     */
    protected function getLocation($formName,$id) {
        $this->ensureLocations($formName);
        if (!array_key_exists($id,$this->locations[$formName]) ||  $this->locations[$formName][$id] ===false) {
            return false;
        }
        return $this->locations[$formName][$id];
    }

    /**
     * Return an array of all the record ids for a given form.
     * @param string $form
     * @param integer $mod_time.  Defaults to  -1.  If non-negative, it is a unix timestamp and we retrieve records modified at or after the given time
     * @param string $parent Defaults to false.  If true, The parent field we want to restrict values to
     * @return array
     */
    public function getRecords( $form, $mod_time = -1, $parent = false ) {
        $this->ensureLocations($form);
        if ($mod_time < 0) {
            return array_keys($this->locations[$form]);
        } else {
            return array_keys($this->listFields($form, array(),  $parent, array(), array(),  false, $mod_time));
        }
    }


   /**
     * Process the given location/data object for form data
     * @param string $form
     * @param mixed $locationObject
     * @returns array indexed by field name (including 'parent') and values the DB value
     */
    abstract protected function getFormData($form,$location);
 
    /**
     * Populate the member variables of the object from the Cross Sectional Data Set
     * @param I2CE_Form $form
     * @return boolean
     */
    public function populate( $form ) {
        $formName = $form->getName();
        $this->ensureLocations($formName);
        $id = $form->getId();
        if (
            (!array_key_exists($id,$this->locations[$formName]) )
            || ($location  = $this->locations[$formName][$id]) === false){
            return;
        }
        $data = $this->getFormData($formName,$location);
        foreach ($data as $field=>$dbval) {
            if ($field == 'parent') {
                if ($dbval != '0') {
                    $form->setParent($dbval);
                }
            } else {
                $fieldObj = $form->getField($field);
                if (!$fieldObj instanceof I2CE_FormField) {
                    continue;
                }
                $fieldObj->setFromDB($dbval);
            }            
        }
        return true;
    }
    



    /**
     * Get the id associated to the given location data object
     * @param string $form
     * @param mixed $location
     * @param int count
     * @returns string.  '0' or null  on failure a string on success.
     */
    abstract protected function getLocationId($form, $location, $count);


    protected function getFileData_stream($stream, $as_array) {
        if ($as_array) {
            $ret =file($stream);
            if ($ret === false) {
                return  array();
            } else {
                return $ret;
            }
        } else {
            return file_get_contents($stream);
        }
    }

    protected function getFileData_mdn($path,$as_array) {
        $path = substr($path,6); //strip out the leading mdn://
        $config = I2CE::getConfig()->traverse("/" . $path,false,false);
        if (!$config instanceof I2CE_MagicDataNode
            || !$config->is_scalar()) {
            if ($as_array) {
                return array();
            } else{
                return false;
            }
        } 
        $data = $config->getValue();
        $config->unpopulate();
        if ($as_array) {
            return explode("\n",$data);;
        } else{
            return $data;
        }
    }

    public function getFileModTime($form) {
        if ( ($uri = $this->getFileURIType($form)) === false) {
            return 0;
        }
        if ( ($file = $this->_getFile($form)) === false) {
            return 0;
        }
        if ($uri == 'mdn') {
            return 0;
        } else if ($uri == 'file') {
            $abs_file = null;
            if ( !I2CE_FileSearch::isAbsolut( $file ) ) {
                $abs_file = I2CE::getFileSearch()->search($this->getSearchCategory($form), $file );
            } else {
                $abs_file = $file;
            }
            if ( !$abs_file || !is_readable( $abs_file ) ) {
                I2CE::raiseError( "Could not find $file ($abs_file)" );
                return false;
            }
            return filemtime($abs_file);
        } else {
            return 0;
        }
    }

    
    public function getFileData($form,$as_array = false) {
        if ( ($uri = $this->getFileURIType($form)) === false) {
            if ($as_array) {
                return array();
            }else {
                return false;
            }
        }
        $file = $this->_getFile($form);
        if ($uri == 'mdn') {
            return $this->getFileData_mdn($file,$as_array);
        } else {
            if ($uri == 'file') {
                $abs_file = null;
                if ( !I2CE_FileSearch::isAbsolut( $file ) ) {
                    $abs_file = I2CE::getFileSearch()->search($this->getSearchCategory($form), $file );
                } else {
                    $abs_file = $file;
                }
                if (!$abs_file ||!is_readable( $abs_file ) ) {
                    I2CE::raiseError( "Could not find readable $file ($abs_file)" );
                    return false;
                }
            } else {
                $abs_file = $file;
            }
            return $this->getFileData_stream($abs_file,$as_array);
        }
    }

    protected function getFileURIType($form) {
        $file = $this->_getFile($form);
        if ( ($pos = strpos($file,'://')) !== false) {
            $uri = strtolower(substr($file,0,$pos));
            if ($uri == 'mdn') {
                return 'mdn';
            }
            $wrappers = stream_get_wrappers();
            foreach ($wrappers as &$wrapper) {
                $wrapper = strtolower($wrapper);
            }
            unset($wrapper);
            if (in_array($uri,$wrappers)) {
                return $uri;
            } else {
                return false;
            }
        } else {
            return 'file';
        }
    }



    /**
     * @var protected array $mod_time of int, the unix style mod time of the  file associated to the form which is the index.
     */
    protected $mod_time =array();

    /**
     * Get the absolute location of the XML file for the given  form
     * @param string $form
     * @returns mixed. false on failure, a string on success.
     */
    public function getFile($form) {
        $this->mod_time[$form] = $this->getFileModTime($form);
        $file = $this->_getFile($form);
        return $file;
    }


    /**
     * Get the absolute location of the XML file for the given  form
     * @param string $form
     * @returns mixed. false on failure, a string on success.
     */
    protected function _getFile($form) {        
        $options = $this->getStorageOptions($form);
        if ( !$options instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError( "Invalid storage options for $form" );
            return false;
        }
        $file = null;
        if ( !$options->setIfIsSet( $file, "file" ) ) {
            I2CE::raiseError( "File is not set" );
            return false;
        }    
        if (strlen( (string)$file) == 0) {
            return false;
        }
        return $file;
    }




    /**
     * @param string $form.  THe form name
     * @param array $fields of string. The fields we want returned.  Can include the special field 'last_modified' to get the last modification time for any of the fields of that form which is returned in the format  "Y-m-d H:i:s"
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     * and then we get all forms with parent the given id. If true, we return the parent as one of the fields.
      *@param array $where_data.  contains the  where clause information about this form or a nested      
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.
     * @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *    time greater than or equal to $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.
     * @returns mixed an array with key id's and value and array of values.  the array of values has as keys the fields with their corresponding value.
     */
    public  function listFields($form, $fields, $parent = false, $where_data=array(), $ordering=array(), $limit = false, $mod_time = -1) { 
        if (is_array($mod_time) && array_key_exists('mod_time',$mod_time)) {
            $mod_time = $mod_time['mod_time'];
        }
        if (is_scalar($mod_time) && $mod_time > 0) {
            $this->getDOMData($form); //need to do this so the mod time is set.
            if ($this->mod_time[$form] < $mod_time) {
                return array();
            }
        }
        $vals = parent::listFields($form,$fields,$parent,$where_data,$ordering,$limit,$mod_time);
        if (array_search('last_modified',$fields) !== false) {
            $mod_time = date("Y-m-d H:i:s",$this->mod_time[$form]);
            foreach ($vals as $id=>&$data) {
                $data['last_modified'] =  $mod_time;
            }
        }
        return $vals;
    }
    



    /**
     * @param string $form.  THe form name
     * @param array $fields of string. The fields we want returned
     * Can include the special field 'last_modified' to get the last modification time for any of the fields of that form which is returned in the format  "Y-m-d H:i:s"
     * @param boolean $parent. Defaults to false.    If it is scalar and non-boolean, it is consider to be the ID of the parent, 
     * and then we get all forms with parent the given id. If true, we return the parent as one of the fields.
      *@param array $where_data.  contains the  where clause information about this form or a nested      
     * @param array $ordering. An array of fields to order by.  Defaults to the empty array.  Prepend a - to order by in descending order.
     * @param mixed $limit Defaults to false.  It true, returns only one result.  If an integer it is the numeber of records to limit to.
     *  If it is as an array of two integers, it is the offset and then number of results to limit to.
     * @param integer $mod_time. Defaults to -1.  If non-negative, we only list the requested fields for an id if at least one of them has a modification
     *    time greater than or equal to $mod_time.  If the form storage has no way of tracking modifucation time, all entries are listed.
     * @returns mixed an array with key id's and value and array of values.  the array of values has as keys the fields with their corresponding value.
     */
    public  function listDisplayFields($form, $fields, $parent = false, $where_data=array(), $ordering=array(), $limit = false, $mod_time = -1, $user=false) { 
        //public  function listDisplayFields($form, $fields, $parent = false, $where_data=array(), $ordering=array(), $limit = false, $mod_time = -1) { 
        if (is_array($mod_time) && array_key_exists('mod_time',$mod_time)) {
            $mod_time = $mod_time['mod_time'];
        }
        if (is_scalar($mod_time) && $mod_time > 0) {
            $this->getDOMData($form); //need to do this so the mod time is set.
            if ($this->mod_time[$form] < $mod_time) {
                return array();
            }
        }
        $vals = parent::listDisplayFields($form,$fields,$parent,$where_data,$ordering,$limit,$mod_time);
        if (array_search('last_modified',$fields) !== false) {
            $mod_time = date("Y-m-d H:i:s",$this->mod_time[$form]);
            foreach ($vals as $id=>&$data) {
                $data['last_modified'] =  $mod_time;
            }
        }
        return $vals;
    }




}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
