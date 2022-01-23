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
 * @author Mark A. Hershberger <mhershberger@intrahealth.org>
 * @version 2.1
 * @access public
 */

/**
 * pull in static classes.
 */
require_once 'PHPUnit/Framework.php';

error_reporting(E_ALL | E_STRICT);

function IGNORE_ERROR($errno, $errstr, $errfile, $errline)
{
    switch ($errno) {
    case 2048:
        foreach(debug_backtrace() as $f => $l) {
            if(array_key_exists("class", $l)) {
                if($l['class']    === 'MDB2' &&
                   $l['function'] === 'raiseError') {
                    return TRUE;
                }
            }
        }
        break;
    }

    if(class_exists("I2CE")) {
        I2CE::raiseError("$errstr (at line $errline in $errfile)", $errno);
        return TRUE;
    }
    return FALSE;
}
set_error_handler('IGNORE_ERROR');

define("TOP_DIR", dirname(dirname(dirname(__FILE__))));
ini_set('include_path',ini_get('include_path').":".TOP_DIR."/lib");
require_once 'I2CE.php';
require_once 'I2CE_MagicData.php';

/**
 * Configurator Test class -- handles dependencies and such.
 * @package I2CE
 */
class I2CE_Framework extends PHPUnit_Framework_TestCase {
    static protected function data_dir($class) {
        return TOP_DIR."/tests/$class/";
    }

    static protected $errors = array();
    static protected $not_errors = array();
    protected function setUp() {
        self::$errors = array();
        self::$not_errors = array();
        I2CE::pushErrorHandler(array(__CLASS__, "errorHandler"));
    }

    protected function tearDown() {
        I2CE_MagicData::tearDown();
        I2CE::resetFileSearch();
        I2CE::popErrorHandler(array(__CLASS__, "errorHandler"));
    }

    static public function errorHandler($base, $str, $type) {
        if($type === E_ERROR) {
            array_push(self::$errors, $str);
        }
        else {
            array_push(self::$not_errors, $str);
        }
        # Just keep them quiet for now.
    }

    static protected function addClassPath($path) {
        ini_set('include_path',ini_get('include_path').":".$path);
    }

    function tests() {
        if(get_class($this) === __CLASS__) {
            return 0;
        } else {
            return 1;
        }
    }
}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
