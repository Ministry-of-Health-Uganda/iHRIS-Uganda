<?php
/**
 * @copyright © 2009 Intrahealth International, Inc.
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
 * @author Carl Leitner, Mark A. Hershberger <mhershberger@intrahealth.org>
 * @version 2.1
 * @access public
 */

/**
 * pull in static classes.
 */
require_once 'lib/I2CE_Framework_TestClass.php';
require_once 'I2CE_MagicDataStorageSysV.php';

class I2CE_MagicDataStorageSysVTest extends I2CE_Framework {
    static private $store_sysv;
    static private $config;

    function setUp() {
        parent::setup();
        self::$config = I2CE_MagicData::instance("config");
        self::$store_sysv = new I2CE_MagicDataStorageSysV( "ManageFlying_config" );
        self::$config->addStorage(self::$store_sysv);
    }

    function tearDown() {
        self::$config->tearDown();
    }

    function testClear() {
        $this->assertEquals(array(), self::$config->getAsArray());

        self::$store_sysv->clear();

        $this->assertEquals(array(), self::$config->getAsArray());

        $this->assertEquals(array(), self::$errors);
        $this->assertContains("Clearing all shared memory segemnts",
                              self::$not_errors);
    }

    function testSimpleAssign() {
        self::$config->test = "my test 4";

        $this->assertEquals(array("test" => "my test 4"),
                            self::$config->getAsArray());

        $this->assertRegExp("{shm_get_var..: variable key \d+ doesn't exist}",
                            self::$not_errors[0]);
        $this->assertEquals(array(), self::$errors);
    }

    function testDeeperAssign() {
        self::$config->ow->one = '13';    
        self::$config->ow->two = '23';

        $this->assertEquals(array("ow" => array("two" => 23,
                                                "one" => 13)),
                                  self::$config->getAsArray());

        self::$config->traverse("/ow/one",false,false)->erase();
        $this->assertEquals(array("ow" => array("two" => 23)),
                                  self::$config->getAsArray());

        $this->assertRegExp("{shm_get_var..: variable key \d+ doesn't exist}",
                            self::$not_errors[0]);
        $this->assertEquals(array(), self::$errors);
    }


    function testErasingTraverse() {
        self::$config->ow->two = '33';    

        $this->assertEquals(array("ow" => array("two" => 33)),
                            self::$config->getAsArray());

        $this->assertRegExp("{shm_get_var..: variable key \d+ doesn't exist}",
                            self::$not_errors[0]);
        $this->assertEquals(array(), self::$errors);
    }        
}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
