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
ini_set('include_path',ini_get('include_path').":".TOP_DIR."/lib".
        ":".TOP_DIR."/modules/PackageUtils/lib");
require_once dirname(__FILE__).'/lib/I2CE_Framework_TestClass.php';
require_once 'I2CE_Process.php';

/**
 * Configurator Test class -- handles dependencies and such.
 * @package I2CE
 */
class I2CE_ProcessTest extends I2CE_Framework {
    static private $c;
    function setUp() {
        parent::setUp();
        I2CE::setupFileSearch();
        self::$c = new I2CE_Configurator;
        self::$c->resetCheckedPaths();
    }

    function tests() {return 0;}

    function tearDown() {
        I2CE_MagicData::tearDown();
        I2CE::resetFileSearch();
    }

}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
