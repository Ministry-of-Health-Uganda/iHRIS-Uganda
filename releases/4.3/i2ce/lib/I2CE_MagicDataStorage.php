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

if (!class_exists('I2CE_MagicDataStorage',false)) {
/**
 * Configuration class to lookup and save configuration options.
 * @package I2CE 
 * @access public
 */
    abstract class I2CE_MagicDataStorage {

        /**
         * @var string The name associated with this storage object.
         */
        protected $name;
        /**
         * Create a new instance for magic data storage.
         * @param string $name The name assigned to this storage object
         */
        public function __construct( $name ) {
            $this->name = $name;
        }
        /**
         * Store the given I2CE_MagicDataNode into the database.
         * @param I2CE_MagicDataNode $node
         * @returns boolean.  True on sucess
         */
        abstract public function store( $node );
        /**
         * Retrieve the given I2CE_MagicDataNode value and type.
         * @param I2CE_MagicDataNode $node
         * @return array
         */
        abstract public function retrieve( $node );

        /**
         * Erases the given I2CE_MagicDataNode from the storage mechanism
         * @param I2CE_MagicDataNode
         * @param boolean.  True on sucess
         */
        abstract public function destroy( $node );

        /**
         * Return the md5 Hash of the path of this object.
         * @param I2CE_MagicDataNode $node
         * @param string $childPath Defaults to null
         * @return string
         */
        public function getHash( $node , $childPath = null) {
            if ($childPath) {
                return md5( $this->getChildPath($node,$childPath));
            } else {
                return md5( $node->getPath() );
            }
        }
        /**
         * Return the md5 Hash of the path of this object.
         * @param I2CE_MagicDataNode $node
         * @param string $childPath 
         * @param boolean $show_top defaults to true if we are to show the parent
         * @return string
         */        
        public function getChildPath($node,$childPath,$show_top  = true) {
            if ($show_top) {
                if ($node instanceof I2CE_MagicData) {
                    return $node->getPath(true) .$node->pathDivider() . $childPath ;
                } else {
                    return $node->getPath(true) . '/' . $childPath ;
                }
            } else {
                if ($node instanceof I2CE_MagicData) {
                    return '/' . $childPath ;
                } else {
                    return $node->getPath(false) .'/' . $childPath ;
                }
            }
        }

        /**
         * Clear the all keys/values associated with this storage 
         * @return boolean
         */
        abstract public function clear();


        /**
         * Returns true if this storage mechanism is ready to be used.  false otherwise.
         */
        abstract public function isAvailable();

        /**
         *Renames a child node.  This is slow
         *@param string $old
         *@param string $new
         *@returns boolean.  True on success, false on failure
         */        
        abstract public function renameChild($node, $old,$new);
    }

}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
