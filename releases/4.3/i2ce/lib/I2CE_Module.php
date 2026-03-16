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
 * @access public
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @since Demo-v1.a
 * @version Demo-v2.a
 */

/**
 * Implements
 */
require_once "I2CE_Fuzzy.php";

/**
 * The abstract class for that all modules must implement
 * @package I2CE
 */
abstract class I2CE_Module extends I2CE_Fuzzy{
    


    /**
     * Construct this object
     */
    public function __construct() {
    }

    /** 
     * Upgrade this module if necessary
     * @param string $old_vers
     * @param string $new_vers
     * @return boolean
     */
    public function upgrade($old_vers,$new_vers) {
        return true;
    }

    /** 
     * Post Update this module if necessary
     * @param string $old_vers
     * @param string $new_vers
     * @return boolean
     */
    public function post_update($old_vers,$new_vers) {
        return true;
    }




    /** 
     * Run the pre upgrade for this module.  This can use the old config data before it
     * has been changed from the config.
     * @param string $old_vers
     * @param string $new_vers
     * @param I2CE_MagicDataNode $new_storage
     * @return boolean
     */
    public function pre_upgrade($old_vers,$new_vers, $new_storage) {
        return true;
    }


    /**
     * Get the configuration data for this module
     * @returns I2CE_MagicDataNode 
     */
    public function getConfig() {
        return I2CE::getConfig()->traverse("/modules/{$this->shortname}",true);
    }

    /**
     * Method called before the module is enabled for the first time.
     * @param boolean -- returns true on success. false on error.
     */
    public function action_initialize() {
        return true;
    }


    /**
     * Method called to perform the configuration for this module
     * All the configuration should take place within the div with id 'moduleConfiguration'
     */
    public function action_configure() {
    }


    /**
     * Method called to get the modules' hooks
     * @returns an associative array where the key is the hookname
     * and the value is mixed. 
     * The first option for the value is that it is a string.  This string is the
     * method name to call.  The priority for the method in this case is the priority of the module
     * <br/>
     * The second options is is that each value consists of an array 
     * with keys integers, the priority, and values the methods
     * <br/>
     * Example we may return:
     *    array('post_configure'=>'andNowForSomethingCompletelyDifferent',
     *          'post_junk'=>array(
     *                        -10=>'method0'
     *                        10=>'method1'
     *                        100=>'method2'
     *                        1004=>'method3'
     *                            )
     *         )
     *<br/>
     * Each of the methods takes either 0 or 1 argument depending on the hook.
     * For a complete list of hooks and their arguments see....
     */
    public static function getHooks() {
        return array();
    }



    /**
     * Any 'fuzzy' methods that this module implements.
     * @returns an associative array.
     * the keys are the form of $key = '$class::$method'
     * where $class is the name of a class which is an instance of I2CE_FuzzyMethod
     * and $method is the name of a $method we wish to add to the class $class
     * and which is not already in the class.
     * the value is the name of a public function in the I2CE_Module subclass
     * which will handle the call to $method.
     * Example:
     * array('I2CE_Template->setForm'=>'setForm')
     * You also have the option of
     * array('I2CE_Template->setForm'=>array('method'=>'setForm','priority'=>100)
     * If you wish to set a priority.  The default proiority is the priority of the module
     */
    public static function getMethods() {
        return array();
    }





    /**
     * Any 'fuzzy' methods that this module implements on the command line.
     * @returns an associative array.
     * the keys are the form of $key = '$class::$method'
     * where $class is the name of a class which is an instance of I2CE_FuzzyMethod
     * and $method is the name of a $method we wish to add to the class $class
     * and which is not already in the class.
     * the value is the name of a public function in the I2CE_Module subclass
     * which will handle the call to $method.
     * Example:
     * array('I2CE_Template->setForm'=>'setForm')
     * You also have the option of
     * array('I2CE_Template->setForm'=>array('method'=>'setForm','priority'=>100)
     * If you wish to set a priority.  The default proiority is the priority of the module
     */
    public static function getCLIMethods() {
        return array();
    }




    /**
     * Perform any actions that a module needs to when it is enabled.
     * @returns boolean.   Returns true on success, returns false on failure and prevents
     * the module from being enabled.
     */
    public function action_enable() {
        return true;
    }


    /**
     * Perform any actions that a module needs to when it is disabled.
     * @returns boolean.   Returns true on success, returns false on failure and prevents
     * the module from being disabled.
     */
    public function action_disable() {
        return true;
    }



}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
