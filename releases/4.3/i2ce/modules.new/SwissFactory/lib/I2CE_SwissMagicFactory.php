<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
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
*  I2CE_SwissMDNFactory
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @version 2.1
* @access public
*/


class I2CE_SwissMagicFactory extends I2CE_SwissFactory{

    protected $status;
    protected $root_path;
    protected $root_type;
    protected $root_path_create;
    protected $root_url_postfix;
    protected $root_url;

    /**
     * construct a swiss swiss factory and create it if it doesn't exist.
     * @param I2CE_MagicDataNode $storage.  The root of the magic data we will be operating on
     * @param string  $type.  The  type of the root swiwss.  Defaults to 'Swiss'
     * @throws Exception
     */
    public function __construct( $page, $init_options=array()) {
        if (array_key_exists('root_url_postfix',$init_options) && $init_options['root_url_postfix']) {
            $this->root_url_postfix = $init_options['root_url_postfix'];
        } else {
            $this->root_url_postfix = false;
        }
        if (array_key_exists('root_type',$init_options) && $init_options['root_type']) {
            $this->root_type = $init_options['root_type'];
        } else {
            $this->root_type = false;
        }
        if (array_key_exists('root_url',$init_options) && $init_options['root_url']) {
            $this->root_url = $init_options['root_url'];
        } else {
            $this->root_url = false;
        }
        if (array_key_exists('root_path',$init_options) && $init_options['root_path']) {
            $this->root_path = $init_options['root_path'];
        } else {
            $this->root_path = false;
        }
        if (array_key_exists('root_path_create',$init_options) && $init_options['root_path_create']) {
            $this->root_path_create = true;
        } else {
            $this->root_path_create = false;
        }
        parent::__construct($page,$init_options);
        if (array_key_exists('status',$init_options) && is_array($init_options['status'])) {
            $this->status = $init_options['status'];
        } else {
            $this->status = array(
                'showIndex'=>false,
                'visible'=>true,
                'advanced'=>true,
                );
        }
    }

    protected function getRootStorage() {
        if ( $this->root_path ) {
            return I2CE::getConfig()->traverse($this->root_path,$this->root_path_create,false);
        } else {
            return I2CE::getConfig();            
        }
    }
        
    protected function getRootType() {
        if ($this->root_type) {
            return $this->root_type;
        } else {
            return parent::getRootType();
        }
    }



    public function getChildNames($swiss) {
        $storage = $swiss->getStorage();
        if (!$storage instanceof I2CE_MagicDataNode) {
            return array();
        }
        return $storage->getKeys();
    }

    public function getChildStorage($swiss,$child = null, $create = false) {
        $storage = $swiss->getStorage();
        if (!$storage instanceof I2CE_MagicDataNode) {
            if ($child === null) {
                return array();
            } else {
                return null;
            }
        }
        if ($child !== null) {
            return $storage->traverse($child,$create,false);
        } else {
            $children = $storage->getKeys();
            $childStorages = array();
            foreach ($children as $child) {
                $childStorage = $storage->traverse($child,$create,false);
                if ($childStorage instanceof I2CE_MagicDataNode) {
                    $childStorages[$child] = $childStorage;
                }
            }
            return $childStorages;
        }
    }
    

    public function getURLRoot($action = null) {
        if ($this->root_url) {
            if ($action === null) {
                $url = $this->root_url;
            } else {
                $url = $this->root_url . '/' .$action;
            }
        } else {
            if ($action === null) {
                $url =  $this->page->module() . '/' . $this->page->page();
            }else {
                $url =  $this->page->module() . '/' . $action;
            }
            if ($this->root_url_postfix) {
                $url .= '/' . $this->root_url_postfix;
            }
        }
        return  $url;
    }


    public function getChildType($swiss,$child) {
        $childStorage = $this->getChildStorage($swiss,$child,false);
        if (!$childStorage instanceof I2CE_MagicDataNode) {
            return null;
        }
        if ($childStorage->hasAttribute('config')) {
            return $childStorage->getAttribute('config');
        }
        if ($childStorage->is_scalar()) {
            return 'Default_Leaf';
        }
        return null;
    }


    public function getStatus($swiss) {
        return $this->status;
    }



    /**********************************
     *                                *
     *   Wrapper for Iterator Interface           *
     *                                *
     *********************************/
    
    public function key($swiss) {
        return $swiss->getStorage()->key();
    }
    
    public function next($swiss) {
        return $swiss->getStorage()->next();
    }
    public function rewind($swiss) {
        return $swiss->getStorage()->rewind();
    }
    public function valid($swiss) {
        if ($swiss->getStorage()->is_scalar()) {
            return false;
        }
        return $swiss->getStorage()->valid();
    }

    /**********************************
     *                                *
     *   Wrapper for CountableInterface           *
     *                                *
     *********************************/
    public function count($swiss) {
        if ($swiss->getStorage()->is_scalar()) {
            return 0;
        } else {
            return $swiss->getStorage()->count();
        }
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
