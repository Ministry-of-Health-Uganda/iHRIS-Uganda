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
require_once 'I2CE_FileSearch.php';

/**
 * Configurator Test class -- handles dependencies and such.
 * @package I2CE
 */
class I2CE_FileSearchTest extends I2CE_Framework {
    static private $c;
    static private $fs;

    function setUp() {
        parent::setUp();
        self::$fs = new I2CE_FileSearch();
    }

    function testFindByRegularExpression() {
        $data_dir = self::data_dir(__CLASS__)."FindBy";
        self::$fs->addPath("TOP", "$data_dir/");
        self::$fs->addPath("ONE", "$data_dir/1");
        self::$fs->addPath("TWO", "$data_dir/2");

        $found = self::$fs->findByRegularExpression("TOP", "{.[^#~]$}", TRUE);
        $this->assertEquals(array("$data_dir//top-file"), $found);

        $found = self::$fs->findByRegularExpression("ONE", "{.}");
        $this->assertEquals("$data_dir/1//one", $found);

        $found = self::$fs->findByRegularExpression("ONE", "{.}", TRUE);
        $this->assertEquals(array("$data_dir/1//one",
                                  "$data_dir/1//two"),
                            $found);

        $found = self::$fs->findByRegularExpression("TWO", "{.}", TRUE);
        $this->assertEquals(array("$data_dir/2//one-two"), $found);

        $found = self::$fs->findByRegularExpression("TOP", "{.[^#~]$}");
        $this->assertEquals("$data_dir//top-file", $found);

    }

}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
