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
   * Implements
   */
require_once "I2CE_MagicDataStorage.php";

/**
 * Configuration class to lookup and save configuration options.
 * @package I2CE 
 * @access public
 */
if(!class_exists("I2CE_MagicDataStorageMem",false)) {
    class I2CE_MagicDataStorageMem extends I2CE_MagicDataStorage {

    const TYPE = 0;
    const PATH = 1;
    const CHILDREN = 2;
    const VALUE = 3;
    /**
     * @var array An array of the data saved in memory.
     */
    private $data;


    /**
     * Returns true if this storage mechanism is ready to be used.
     * false otherwise.
     */
    public function isAvailable() {
        return TRUE;
    }

        
    public function __construct() {
        $this->data = array();
    }

    /**
     * Erases the given I2CE_MagicDataNode from the storage mechanism
     * @param I2CE_MagicDataNode
     * @param boolean.  True on sucess
     */
    public function destroy($node) {
        $hash = $this->getHash( $node );
        if ( array_key_exists( $hash, $this->data ) ) {
            unset($this->data[$hash]);
        }

        return TRUE;
    }

    /**
     * Clear the all keys/values associated with this storage 
     * @return boolean
     */
    public function clear() {
        $this->data = array();
        return true;
    }

    /**
     * Store the given I2CE_MagicDataNode into memory.
     * @param I2CE_MagicDataNode $node
     */
    public function store( $node ){
        $hash = $this->getHash( $node );
        $this->data[$hash] = array( self::TYPE => $node->getType(),
                                    self::PATH => $node->getPath() );
                
        $this->data[$hash][self::VALUE] = $node->getSaveValue();
        $this->data[$hash][self::CHILDREN] = $node->getKeys(NULL,TRUE);

        return TRUE;
    }

    /**
     * Retrieve the given I2CE_MagicDataNode value and type.
     * @param I2CE_MagicDataNode $node
     * @return array
     */
    public function retrieve( $node ) {
        $hash = $this->getHash( $node );
        if ( array_key_exists( $hash, $this->data ) ) {
            return array( "type"     => $this->data[$hash][self::TYPE],
                          "value"    => $this->data[$hash][self::VALUE],
                          "children" => $this->data[$hash][self::CHILDREN],);
        } else {
            return NULL;
        }
    }
    
    /**
     *Renames a child node
     * @param I2CE_MagicDataNode $node
     *@param string $old
     *@param string $new
     *@returns boolean.  True on success, false on failure
     */        
    public function renameChild($node,$old,$new) {
        $hash = $this->getHash($node);
        if ( !array_key_exists( $hash, $this->data ) ) {
            I2CE::raiseError("Node " . $node->getPath(false) . " not found");
            return false;
        }
        if ( ($key = array_search($old,$this->data[$hash][self::CHILDREN])) === false) {
            I2CE::raiseError("Child $old on " . $node->getPath(false) . " does not exist");
            return false;
        }
        $this->data[$hash][self::CHILDREN][$key] = $new;
        $this->renameDecendents($node,array($old=>$new));

        return true;
    }


    /**
     * Rename the descenedent children for which we need to rename  its paths
     * @param I2CE_MagicDataNode $node 
     * @param array $children an array of child paths we need to rename its path
     */
    protected function renameDecendents($node,$children) {
        foreach ($children as $old=>$new) { //these are the old child paths
            $old_hash = $this->getHash($node , $old); //existing hash
            if (!array_key_exists($old_hash,$this->data)) {
                continue;
            }            
            $new_hash = $this->getHash($node,$new);
            $this->data[$new_hash] = $this->data[$child_hash];
            $this->data[$new_hash][self::PATH] = $this->getChildPath($node,$new);
            unset($this->data[$old_hash]);
            
            $t_children = array();
            foreach ($this->data[$new_child_hash][self::CHILDREN] as $t_child) {
                $t_children[$old . '/' . $t_child ] = $new . '/' . $t_child;
            }
            $this->renameDescendents($node,$t_children);
        }
    }
}
}

# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
