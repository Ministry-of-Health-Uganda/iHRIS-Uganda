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


/**
 * Dependencies.
 */
require_once "I2CE_MagicDataNode.php";
require_once "I2CE_Locales.php";
/**
 * Configuration class to lookup and save configuration options.
 * @package I2CE 
 * @access public
 */
class I2CE_MagicData extends I2CE_MagicDataNode {

    /**
     * @var array A list of named instances of this class.
     */
    static protected $instances;
    /**
     * @var string The index of the last accessed stored instance of this class
     */
    static protected $last_instance;
    /**
     * @var string The instance index for this configuration grouping.
     */
    protected $instance;

    /**
     * A list of storage objects to use to store and retrieve values.
     * Values will be saved using all storage objects and retrieved
     * until a match is found in the order the objects are assigned.
     * @var array
     */
    protected $storage;
        
    /**
     * Return an instance of this class based on the index given.
     * This will create the instance if it doesn't exist.
     *
     * @param string $index
     * @param boolean $replace.  Defaults to false.  If true and
     * $index is non null, then we create a new instance at $index.
     * @return I2CE_MagicData
     */
    static public function instance( $index = NULL, $replace = FALSE ) {
        if ( !is_array( self::$instances ) ) {
            self::$instances = array();
        }
        if ( $index === NULL ) {
            $index = self::$last_instance;
        }
        if ( !is_string( $index ) || empty( $index ) ) {
            I2CE_MagicDataNode::raiseError("Invalid index passed to ".
                                           "I2CE_MagicData::instance(): ".
                                           "$index");
            return NULL;
        }
        self::$last_instance = $index;
        if ( !array_key_exists( $index, self::$instances ) 
             || !self::$instances[ $index ] instanceof I2CE_MagicData 
             || $replace) {            
            self::$instances[ $index ] = new I2CE_MagicData( $index );
            self::$instances[ $index ]->instance = $index;
        }
        return self::$instances[ $index ];
    }

    /**
     * 
     * Return the path divider for this MagicDataNode object
     * @return string
     */
    public function pathDivider() { return ":"; }
    /**
     * Return the full path to this configuration setting.
     *
     * @param boolean $show_top defaults to true if we are to show the parent
     * @return string
     */
    public function getPath($show_top=true) {
        if ($show_top) {
            return $this->instance;
        } else {
            return '';
        }
    }

    /**
     * Store the given magic data node (which should be a descendent
     * of this instance.)  Starts storing at the last added storage
     * mechanism until it reaches the first storage mechanism.  If it
     * fails saving at any storage mechanis, it will call the destroy
     * method on that storage mechanism and any remaining one.
     *
     * @param  I2CE_MagicDataNode $node
     * @param  integer            $max  The max index of storage objects to save.
     *                                  Defaults to -1 meaning we store on all 
     *                                  mechanisms
     * @returns boolean  true on success.  
     */
    public function store( $node, $max = -1 ) {
        if ($node->type == I2CE_MagicDataNode::TYPE_NOT_POPULATED) {
            I2CE::raiseError("Trying to store magic data which is unpopulated at " .
                             $node->getPath());
            return FALSE;
        }

        if($this->num_storage === 0) {
            return TRUE;
        }
        if ( $max < 0 || $max >  $this->num_storage  ) {
            $max = $this->num_storage -1;
        }

        //there is a problem here if there in only one storage mechanism that we have just retrieved/populated from

        $fails = array();
        for( $i = $max; $i >= 0; $i-- ) {
            if (! $this->storage[$i]->store( $node )) {
                $fails[] = $i;
            }
        }
        if (count($fails) > 0) {
            $fail_max = max($fails);
            if ($fail_max == $this->num_storage -1) {
                I2CE::raiseError("Unrecoverable data storage failure for " . $node->getPath()  );
                return false;
                //we failed on the permanent data store
            } 
            I2CE::raiseError("Attempting to recover from data storage failure for " . $node->getPath()  );
            //we did not fail on the permanent data store
            //we try to clear all caches at and above the failure point.
            $cleared = true;
            for ($i =$fail_max; $i>=0; $i--) {
                $cleared &= $this->storage[$i]->destroy($node);
            }
            if (!$cleared) {
                I2CE::raiseError("Could not clear cached data for " . $node->getPath()  );
                return false;
            }
        }
        return true;
    }
    /**
     * Destroy the specified node from each of the storage mechanisms
     * @returns boolean  true on success
     */
    public function destroy($node) {
        $max = $this->num_storage;
        $sucess = true;
        for( $i = 0; $i < $max; $i++ ) {
            $sucess = $sucess && $this->storage[$i]->destroy( $node );
        }        
        if (!$sucess )  {
            I2CE::raiseError("Could not destroy for " . $node->getPath(false));
        }
        return $sucess;
    }
    
    /**
     * Clear caching for each of the storage mechanisms
     * @return boolean
     */
    public function clearCache() {
        $max = $this->num_storage - 1;
        $success = true;
        for( $i = 0; $i < $max; $i++ ) {
            $success = $success && $this->storage[$i]->clear();
        }
    	return $success;
    }
        

    /**
     * Retrieve the given magic data node (which should be a
     * descendent of this instance.)
     *
     * @param I2CE_MagicDataNode $node
     * @return array
     */
    public function retrieve( $node ) {
        $node_data = array();
        $i_start = 0;
        if ($node->volatile()) { 
            //if the node is volatile we want to retrieve if from the
            //last storage mechanism added as
            //it is considered the most secure
            $i_start = $this->num_storage - 1;
        }
        for( $i = $i_start; $i < $this->num_storage; $i++ ) {
            $node_data = $this->storage[$i]->retrieve( $node );
            if ( is_array( $node_data ) && count( $node_data ) > 0 ) {
                if (! (array_key_exists('type',$node_data) &&
                       array_key_exists('value',$node_data) &&
                       array_key_exists('children',$node_data))) {
                    I2CE_MagicDataNode::raiseError( $node->getPath() .
                                                    ": got invalid data "
                                                    . print_r($node_data,true) .
                                                    " from " .
                                                    get_class($this->storage[$i]),
                                                    E_ERROR);
                    $node_data = array();
                }
                else if ($node_data['type'] ==
                         I2CE_MagicDataNode::TYPE_NOT_POPULATED) {
                    I2CE_MagicDataNode::raiseError("Retreived unpopulated type at "
                                                   . $node->getPath() . " from " .
                                                   get_class( $this->storage[$i] ));
                    $node_data = array();
                } else {
                    break;
                }
            } 
        }
        return array( "storage_idx" => $i, "data" => $node_data );
    }
        
    /**
     * Add the given storage object to this instance. 
     * The last one added is considered to be the "definitive" one -- the one whose contents
     * should be considered correct.  The first one added should be the one which has quickest
     * access
     * @param I2CE_MagicDataStorage $storage
     */
    public function addStorage( $storage ) {
        if ( !is_array( $this->storage ) ) {
            $this->storage = array();
        }
        if (!$storage instanceof I2CE_MagicDataStorage) {
            I2CE::raiseError("Magic data storage mechanism is invalid " .
                             print_r($storage,true));
            return false;
        }
        if (!$storage->isAvailable()) {
            I2CE::raiseError("Magic data storage mechanism provided by " .
                             get_class($storage)  . " is not available");
            return false;
        }
        $this->storage[] = $storage;
        $this->num_storage = count($this->storage);
        return true;
    }


    /**
     * Gets the class of the permanent storage object (the last one added)
     * @returns string
     */
    public function getPermanentStorageClass()  {
        end($this->storage);
        return get_class(current($this->storage));
    }




    /**
     *Rename a child node
     *@param I2CE_MagicDataNode 
     *@param string $old
     *@param string $new
     *@returns boolean.  True on success, false on failure
     */
    protected function moveChild($node,$old,$new) {        
        if (count($this->storage) == 0) {
            I2CE::raiseError("No storage mechanisms");
            return false;
        }
        if ( !I2CE_MagicDataNode::checkKey( $old ) || !I2CE_MagicDataNode::checkKey($new)) {
            I2CE::raiseError("Bad rename $old=>$new");
            return false;
        }
        $children = $node->getKeys();
        if (!in_array($old,$children)) {
            I2CE::raiseError("Trying to rename non-existing child $old of " . $node->getPath(false));
            return false;
        }
        if (in_array($new,$children)) {
            I2CE::raiseError("Trying to rename existing child $old to $new but $new is already present at " . $node->getPath(false));
            return false;
        }
            
        $node->unpopulate(true,true);

        $node->volatile(true);
        if (!$this->clearCache()) {
            I2CE::raiseError("Cannot clear cache");
        }
        end($this->storage);
        $permStorage = current($this->storage);
        
        return $permStorage->renameChild($node,$old,$new);
    }


    protected $num_storage;
    protected $locales;

    /**
     * Construct a new configuration value or grouping.
     * @param string $name The name of this configuration setting.
     * @param I2CE_MagicDataNode $parent The parent of this configuration setting.
     * @param boolean $check_key. Defaults to true in which case we check that the key is valid.
     */
    protected function __construct( $name = null, $parent = null, $check_key = true) {
        parent::__construct($name,null,$check_key);
        $this->locales = array (I2CE_Locales::DEFAULT_LOCALE);
        $this->num_storage = 0;
    }


    public function setLocales($locales) {
        $locales = I2CE_Locales::validateLocales($locales);
        $this->locales = $locales;
    }

}


# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
