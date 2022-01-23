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
require_once 'lib/I2CE_Framework_TestClass.php';
require_once 'I2CE_Configurator.php';
require_once 'I2CE_MagicData.php';
require_once 'I2CE_MagicDataStorageMem.php';

/**
 * Configurator Test class -- handles dependencies and such.
 * @package I2CE
 */
class TestMagicDataTearDown extends I2CE_MagicData {
    static public function brokenTearDown() {
        if(is_array(self::$instances)) {
            foreach(self::$instances as $i => $inst) {
                $inst->erase();
            }
        }
    }
}

class I2CE_MagicDataTest extends I2CE_Framework {
    static private $md;
    static private $c;

    function setUp() {
        parent::setup();
        I2CE::setupFileSearch();
        $store = new I2CE_MagicDataStorageMem;
        self::$md = TestMagicDataTearDown::instance("config");
        self::$md->addStorage($store);
        I2CE::setConfig(self::$md);
        self::$c = new I2CE_Configurator(self::$md);
        self::$c->resetCheckedPaths();
    }

    public function testRegularErase() {
        $a = self::$c->getAvailableModules(array(self::data_dir(__CLASS__).
                                                 "/OneModuleWithClass"));

        TestMagicDataTearDown::tearDown();
        I2CE::resetFileSearch();
        $this->assertEquals(array(), self::$not_errors);
        $this->assertEquals(array(), self::$errors);

        $this->setUp();
        $a = self::$c->getAvailableModules(array(self::data_dir(__CLASS__).
                                                 "/OneModuleWithClass"));
        $b = array("one");
        $this->assertEquals($b, $a);

        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
    }

    public function testBrokenErase() {
        $a = self::$c->getAvailableModules(array(self::data_dir(__CLASS__).
                                                 "/OneModuleWithClass"));

        TestMagicDataTearDown::brokenTearDown();
        I2CE::resetFileSearch();
        $this->assertEquals(array(), self::$not_errors);
        $this->assertEquals(array(), self::$errors);

        $this->setUp();
        $a = self::$c->getAvailableModules(array(self::data_dir(__CLASS__).
                                                 "/OneModuleWithClass"));
        $b = array();
        $this->assertEquals($b, $a);

        $this->assertRegExp("{Trying to get property of non-object}",
                            self::$not_errors[0]);
        $this->assertContains("Got a NULL value for storage.", self::$errors);
        self::$errors = array();
        
        TestMagicDataTearDown::tearDown();
        I2CE::resetFileSearch();

        $this->assertEquals(array(), self::$errors);
    }
}


# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
