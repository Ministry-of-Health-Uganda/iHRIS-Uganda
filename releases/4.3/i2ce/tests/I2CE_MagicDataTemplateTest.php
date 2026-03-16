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
 * @author Mark A. Hershberger <mhershberger@intrahealth.org>
 * @version 2.1
 * @access public
 */

/**
 * pull in static classes.
 */
require_once dirname(__FILE__).'/lib/I2CE_Framework_TestClass.php';

require_once 'I2CE_MagicDataStorageMem.php';
require_once 'I2CE_MagicDataTemplate.php';

class TestMagicDataTemplate extends I2CE_MagicDataTemplate {
    /* Can't let __call() handle this since $paths is passed by reference  */
    public function updatePaths($node = NULL, &$paths = NULL) {
        return parent::updatePaths($node, $paths);
    }

    public function __call($name, $args) {
        return call_user_func_array(array($this, $name), $args);
    }
}

/**
 * Configurator Test class -- handles dependencies and such.
 * @package I2CE
 */
class I2CE_MagicDataTemplateTest extends I2CE_Framework {
    static private $mdt;
    function setUp() {
        parent::setUp();
        self::$mdt = new TestMagicDataTemplate;
        self::$mdt->setVerboseErrors(TRUE);
    }

    function tearDown() {
        parent::tearDown();
        self::$mdt = NULL;
    }

    function testGetTextContent() {}

    function testGetConfigurationTextContent() {}

    function testProcessStatus() {}

    function testSetConfigValues() {}

    function testUpdatePaths() {
        self::$errors = array();
        self::$not_errors = array();
        $dom = new DomDocument;
        $node  = new DOMElement("yo");
        $dom->appendChild($node);
        $paths = NULL;

        $ret = self::$mdt->updatePaths();
        $this->assertEquals(FALSE, $ret);
        $this->assertContains("Internal Error", self::$errors[0]);
        $this->assertEquals(array(), self::$not_errors);
        self::$errors = array();

        $ret = self::$mdt->updatePaths($node);
        $this->assertEquals(FALSE, $ret);
        $this->assertContains("Internal Error", self::$errors[0]);
        $this->assertEquals(array(), self::$not_errors);
        self::$errors = array();

        $node->setAttribute("name", "");
        $ret = self::$mdt->updatePaths($node, $paths);
        $this->assertEquals(FALSE, $ret);
        $this->assertContains("Internal Error", self::$errors[0]);
        $this->assertEquals(array(), self::$not_errors);
        self::$errors = array();

        $paths = array();
        $ret = self::$mdt->updatePaths($node, $paths);
        $this->assertEquals(FALSE, $ret);
        $this->assertContains("yo has empty path at //", self::$errors[0]);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertEquals(array(), $paths);

        $node->removeAttribute("name");
        $node->setAttribute("path", "");
        $ret = self::$mdt->updatePaths($node, $paths);
        $this->assertEquals(FALSE, $ret);
        $this->assertContains("yo has empty path at //", self::$errors[0]);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertEquals(array(), $paths);
        self::$errors = array();
        
        $node->removeAttribute("path");
        $node->setAttribute("path", "/");
        $ret = self::$mdt->updatePaths($node, $paths);
        $this->assertEquals(TRUE, $ret);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertEquals(array("/"), $paths);

        $node->removeAttribute("path");
        $node->setAttribute("name", "test");
        $ret = self::$mdt->updatePaths($node, $paths);
        $this->assertEquals(TRUE, $ret);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertEquals(array("/", "test"), $paths);

        $node->removeAttribute("name");
        $node->setAttribute("name", "test");
        $ret = self::$mdt->updatePaths($node, $paths);
        $this->assertEquals(TRUE, $ret);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertEquals(array("/", "test", "test"), $paths);
        
        $node->removeAttribute("name");
        $node->setAttribute("name", "/test");
        $ret = self::$mdt->updatePaths($node, $paths);
        $this->assertEquals(TRUE, $ret);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertEquals(array("/test"), $paths);
    }

    function testGetConfigPath() {}

    function testTraversePaths() {}

    function testProcessValues() {}

    function testValidateValues_string_single() {}

    function testProcessValues_string_single() {}

    function testProcessValues_string_many() {}

    function testProcessValues_boolean_single() {}

    function testProcessValues_list_single() {}

    function testProcessValues_list_many() {}

    function testProcessValues_delimited_single() {}

    function testProcessValues_delimited_many() {}

    function testGetConfigMetaData() {}

    function testUpdateClassPath() {
        $config = I2CE_MagicData::instance("config");
        $store = new I2CE_MagicDataStorageMem( "_Config" );
        $config->addStorage($store);
        $datadir = self::data_dir("UpdateClassPath");

        $ret = self::$mdt->updateClassPath();
        $this->assertEquals(FALSE, $ret);
        $this->assertContains("Internal Error", self::$errors[0]);
        $this->assertEquals(array(), self::$not_errors);
        self::$errors = array();

        $ret = self::$mdt->updateClassPath($config);
        $this->assertEquals(FALSE, $ret);
        $this->assertContains("Internal Error", self::$errors[0]);
        $this->assertEquals(array(), self::$not_errors);
        self::$errors = array();

        $ret = self::$mdt->updateClassPath($config, "test");
        $this->assertEquals(TRUE, $ret);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
        self::$errors = array();

        $config->test->class->file = "super";
        $ret = self::$mdt->updateClassPath($config, "test");
        $this->assertEquals(TRUE, $ret);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertTrue(!isset($config->test->class->file));
        self::$errors = array();

        $config->test->class->name = "NotFound";
        $ret = self::$mdt->updateClassPath($config, "test");
        $this->assertEquals(FALSE, $ret);
        $this->assertRegexp("{Class NotFound cannot be found}",
                            self::$errors[0]);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertTrue(!isset($config->test->class->file));
        self::$errors = array();

        /* Not obvious till you dig into the code but without
           specifying a path for CLASSES, you get ./ which
           FileSearch->absolut() resolves to the directory of the
           calling function (from the backtrace.  Which, in this case
           is the I2CE/lib directory. */
        $path = dirname(dirname(__FILE__))."/lib/I2CE_MagicDataTemplate.php";
        $config->test->class->name = "I2CE_MagicDataTemplate";
        $ret = self::$mdt->updateClassPath($config, "test");
        $this->assertEquals(TRUE, $ret);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertEquals($path, $config->test->class->file);
        self::$errors = array();

        $config->test->paths->CLASSES = array("$datadir/NoSuch");
        $config->test->class->name = "Blah";
        $ret = self::$mdt->updateClassPath($config, "test");
        $this->assertEquals(FALSE, $ret);
        $this->assertRegExp("{NoSuch doesn't exist!}", self::$errors[0]);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertTrue(!isset($config->test->class->file));
        self::$errors = array();

        $config->test->paths->CLASSES = array("$datadir");
        $config->test->class->name = "NotFound";
        $ret = self::$mdt->updateClassPath($config, "test");
        $this->assertEquals(FALSE, $ret);
        $this->assertRegexp("{Class NotFound cannot be found in the given ".
                            "class search path: [^ ]+/tests/UpdateClassPath}",
                            self::$errors[0]);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertTrue(!isset($config->test->class->file));
        self::$errors = array();

        $config->test->paths->CLASSES = array("$datadir");
        $config->test->class->name = "Found";
        $ret = self::$mdt->updateClassPath($config, "test");
        $this->assertEquals(TRUE, $ret);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertEquals("{$datadir}Found.php", $config->test->class->file);
        self::$errors = array();
    }
}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
