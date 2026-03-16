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
 * @subpackage Core
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @version 2.1
 * @access public
 */

if (!class_exists('I2CE_Fuzzy',false)) {
    /**
     * Fuzzy Object
     * @package I2CE
     */
    class I2CE_Fuzzy {
        /**
         * An array of cached fuzzy methods.  keys are method names
         * values are either error strings if no method is available
         * or the method details.
         *
         * @var private $__methodCache
         */
        private $__methodCache;

        function clearMethodCache() {
            $this->__methodCache = null;
        }

        function __call($method,$params) {
            if (!is_array($this->__methodCache)) {
                $this->__methodCache = array();
            }
            if (array_key_exists($method,$this->__methodCache)) {
                $data = $this->__methodCache[$method];
            } else {
                $data = $this->_hasMethod($method,true,true);
            }
            if (is_string($data)) {
                I2CE::raiseError($data, E_USER_NOTICE);
                return null;
            }
            $module = $data['module_instance'];
            $m = $data['method'];
            array_unshift($params,$this);
            return call_user_func_array(array($module,$m),$params);
        }

        public function _hasMethod($method,$getFuzzy = false,$returnErrors = false) {
            if ((!$getFuzzy) && method_exists($this,$method)) {//is it a regular method?
                return true;
            }
            if (!is_array($this->__methodCache)) {
                $this->__methodCache = array();
            }
            if (!array_key_exists($method,$this->__methodCache)) { //use a cached fuzzy method
                //wasn't a regular method, wasn't cached... check to see if it is a fuzzy method.
                $mod_factory = I2CE_ModuleFactory::instance();
                $className = get_class($this);
                $data = $mod_factory->getMethod($className,$method);
                if (count($data) == 0) {
                    $data ="Fuzzy method '$method' called by " . get_class($this) . " was not found in module factory";
                } else {
                    //there is a fuzzy method.  make sure it is callable
                    $module = $mod_factory->getClass($data['shortname']);
                    //if (!in_array($data['method'],get_class_methods($module))) {
                    if ( $module instanceof I2CE_Fuzzy && $data['shortname'] != $className) {
                        //this feels really dangerous --- we might end up in a loop:  
                        //what if a subclass of i2ce_module defined a fuzzy method for itsel --- this is taken care of, but i am sure some
                        //could do something else really stupid.  
                        // Here is an example of something really stupid which I am not going to check for:
                        // A and B subclass I2CE_Module.  A has a fuzzy method a() which is implemented by fuzzy method b() of B.
                        // But then B implements b() by a().
                        if (  !$module->_hasMethod($data['method'])) {                        
                            $data =
                                "Fuzzy method '$method' called by " . get_class($this) . " references method '" .$data['method'] . "' which is not callable in fuzzy class " . get_class($module) ;
                        } else {
                            $data['module_instance'] = $module;
                        }
                    } else {
                        $class_methods = get_class_methods($module);
                        if ( !is_array($class_methods) || !in_array($data['method'],$class_methods)) {
                            $data =
                                "Fuzzy method '$method' called by " . get_class($this) . " references method '" .$data['method'] . "' which is not callable in module $module's class " . get_class($module) ;
                        } else {
                            //method is callable.  Cache the method data
                            $data['module_instance'] = $module;
                        }
                    }
                }
                $this->__methodCache[$method] = $data;
            }
            if ($getFuzzy) {
                if (is_string($this->__methodCache[$method])) {
                    if ($returnErrors) {
                        return $this->__methodCache[$method];
                    } else {
                        return false;
                    }
                } else {
                    return $this->__methodCache[$method];
                }
            } else {
                if (is_string($this->__methodCache[$method])) {
                    if ($returnErrors) {
                        return $this->__methodCache[$method];
                    } else {
                        return false;
                    }
                }  else {
                    return true;
                }
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
