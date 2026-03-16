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

/**
 * Configurator Test class -- handles dependencies and such.
 * @package I2CE
 */
class I2CE_ConfiguratorTest extends I2CE_Framework {
    static private $c;

    function setUp() {
        parent::setUp();
        I2CE::setupFileSearch();
        self::$c = new I2CE_Configurator;
        self::$c->resetCheckedPaths();
    }

    public function testNothingLoaded() {
        $b = array ( 'failure' => 'Data not loaded' );

        $this->assertEquals($b, self::$c->checkRequirements(array("I2CE")));
        $this->assertEquals(array('Data not loaded.'), self::$errors);
        self::$errors = array();

        $fs = I2CE::getFileSearch();
        $this->assertEquals(array(), $fs->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testModulePath() {
        $a = self::$c->setModulePath("test", "not/this");
        $b = "";
        $this->assertEquals($b, $a);
        $this->assertContains("not/this doesn't exist.", self::$errors);
        $this->assertEquals(array(), self::$not_errors);
        self::$errors = array();

        $a = self::$c->setModulePath("test", self::data_dir(__CLASS__).
                                     "/OneModule/One.xml");
        $b = self::$c->getModulePath("test");
        $this->assertEquals($b, $a);
        $this->assertEquals(self::data_dir(__CLASS__)."OneModule/One.xml", $b);

        $this->assertEquals(array(), self::$not_errors);
        $this->assertEquals(array(), self::$errors);

        $fs = I2CE::getFileSearch();
        $this->assertEquals(array(), $fs->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
    }

    public function testGetTopModule() {
        $mod = self::$c->getTopModule(self::data_dir(__CLASS__)."/OneModule",
                                      TRUE, FALSE, TRUE);
        $this->assertEquals('one', $mod);

        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
    }

    public function testOneModule() {
        $a = self::$c->getAvailableModules(array(self::data_dir(__CLASS__).
                                                 "/OneModule"), TRUE, FALSE, TRUE);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array('one'), $a);

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testOneModuleWithUnavailableClass() {
        $a = self::$c->getAvailableModules(array(self::data_dir(__CLASS__).
                                                 "/OneModuleWithUnavailableClass"),
                                           TRUE, FALSE, TRUE);

        $this->assertRegExp("{Class One cannot be found in the given class ".
                            "search path: .*OneModuleWithUnavailableClass/lib}",
                            self::$errors[0]);
        $this->assertRegExp("{Can not get ConfigMetaData}", self::$not_errors[0]);
    }

    public function testOneModuleWithClass() {
        $a = self::$c->getAvailableModules(array(self::data_dir(__CLASS__).
                                                 "/OneModuleWithClass"),
                                           TRUE, FALSE, TRUE);

        $this->assertEquals(array("one"), $a);

        $this->assertEquals(array(), self::$errors);
        $this->assertRegExp("{Found one as top}", self::$not_errors[0]);
    }

    public function testOneModuleCheckRequirementsNone() {
        $dir = self::data_dir(__CLASS__)."OneModuleCheckRequirements";
        self::$c->getAvailableModules(array($dir));

        $b = array ( 'requirements' => array ( 'one' => "$dir/One.xml" ),
                     'removals'     => array ( ),
                     'optional'     => array ( 'one' => TRUE ),
                     'moved'        => array ( ), );

        $this->assertEquals($b, self::$c->checkRequirements(array("one")));
        $this->assertEquals(array(), self::$errors);
        $this->assertRegExp("{Looking for available}", self::$not_errors[0]);

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testOneModuleCheckRequirementsUnfulfilled() {
        self::$c->getAvailableModules(array(self::data_dir(__CLASS__).
                                            "/OneModuleCheckRequirementsUnfulfilled"), TRUE, FALSE, TRUE);
        $this->assertRegExp("{Can not get ConfigMetaData}", self::$not_errors[0]);
        $this->assertRegExp("{Class OneTwo cannot be found}", self::$errors[0]);
        self::$errors = array();
        self::$not_errors = array();

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);

        $b = array ( 'requirements' => array ( ),
                     'removals'     => array ( ),
                     'optional'     => array ( ),
                     'moved'        => array ( ), );

        $this->assertEquals($b, self::$c->checkRequirements(array("onetwo")));

        /* $this->assertEquals(array(), self::$not_errors); /\* Notices produced *\/ */
        $this->assertEquals(array(), self::$errors);

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testSearchPaths() {
        $dir = self::data_dir(__CLASS__)."testI2CE";
        self::$c->resetCheckedPaths();
        $fs = I2CE::getFileSearch();

        $a = $fs->searchPaths(TRUE, array("{xml$}"),
                              array($dir => I2CE_Locales::DEFAULT_LOCALE), '');
        $b = array($dir."//one.xml");
        $this->assertEquals($b, $a);
            
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testFindByRegularExpression() {
        $dir = self::data_dir(__CLASS__)."testI2CE";
        self::$c->resetCheckedPaths();
        I2CE::setupFileSearch(array('MODULES' => $dir));
        $fs = I2CE::getFileSearch();

        $a = $fs->findByRegularExpression('MODULES', '/xml$/', TRUE);
        $b = array(self::data_dir(__CLASS__)."testI2CE//one.xml");
        $this->assertEquals($b, $a);

        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
    }

    public function testFindAvailableConfigs() {
        $dir = self::data_dir(__CLASS__)."testI2CE";
        I2CE::setupFileSearch(array('MODULES' => $dir));
        $fs = I2CE::getFileSearch();

        $a = $fs->getSearchPath("MODULES");
        $this->assertEquals(array(array($dir)), $a);

        $a = $fs->limitToSubdir("MODULES");
        $this->assertEquals(array($dir=>TRUE), $a);

        $a = self::$c->processConfigFile("$dir/one.xml");
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $a = self::$c->findAvailableConfigs(NULL, TRUE, array("xml"), '', TRUE);

        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertEquals(array("testI2CE"), $a);
    }

    public function testGetAvailableModulesNULLDir() {
        $a = self::$c->getAvailableModules(array(NULL));
        $b = array();
        $this->assertEquals($b, $a);

        $this->assertEquals(array(), self::$errors);

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
   }

    public function testGetAvailableModules() {
        $dir = self::data_dir(__CLASS__)."/testI2CE";

        $a = self::$c->getAvailableModules(array($dir));
        $b = array("testI2CE");
        $this->assertEquals($a, $b);

        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testGetModulePath() {
        $dir = self::data_dir(__CLASS__)."testI2CE/";

        self::$c->getAvailableModules(array($dir));
        $a = self::$c->getModulePath("testI2CE");
        $b = realpath($dir);
        $this->assertEquals("$b/one.xml", $a);

        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testGetModuleDir() {
        $dir = self::data_dir(__CLASS__)."testI2CE";

        self::$c->getAvailableModules(array($dir));
        $this->assertEquals(dirname(dirname(__FILE__))."/", self::$c->getRoot());

        $this->assertEquals($dir, realpath(self::$c->getModuleDir("testI2CE")));

        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testGetModulePathList() {
        $dir = self::data_dir(__CLASS__)."/testI2CE";

        self::$c->getAvailableModules(array($dir));
        $a = self::$c->getModulePathList("testI2CE");
        $b = array("BIN"      => array("./bin"),
                   "NOT-HERE" => array("./not-here"),
                   'CLASSES' => array("./lib"),
                   'MODULES' => array("./modules"));
        ksort($a);
        ksort($b);
        $this->assertEquals($b, $a);

        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testGetModuleFileMap() {
        $dir = self::data_dir(__CLASS__)."testI2CE/";
        I2CE::setupFileSearch(array('MODULES' => $dir));

        self::$c->setRoot($dir);
        self::$c->findAvailableConfigs(NULL, TRUE, array("xml"), '', TRUE);

        $b = array("BIN"  => array("bin/found.php"),
                   'MODULES' => array("modules/not-found.php"),
                   ''     => array("one.xml"));
        $a = self::$c->getModuleFileMap("testI2CE");

        $c = array_merge((array)array_diff($a, $b),
                         (array)array_diff($b, $a));

        $this->assertEquals(array(), $c);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testGetModuleFileList() {
        $dir = self::data_dir(__CLASS__)."testI2CE/";
        self::$c->getAvailableModules(array($dir), TRUE, TRUE, TRUE);

        self::$not_errors = array(); /* Notices produced */
        $files = self::$c->getModuleFileList("testI2CE");
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $expectedFiles = array ("bin/found.php",
                                "one.xml");
        $c = array_merge(array_diff($files, $expectedFiles),
                         array_diff($expectedFiles, $files));
        $this->assertEquals(array(), $c, "Arrays don't match");

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testGetModuleFileMapWithNoClass() {
        $data_dir = self::data_dir(__CLASS__)."/FilesNoClass";
        self::$c->getAvailableModules(array($data_dir));

        $a = self::$c->getModuleFileMap("files-test");
        $b = array('CONF' => array("conf/some.cfg"),
                   'BIN'  => array("bin/test-binary"),
                   'CLASSES' => array("not-here/dont-look.php",
                                      "lib/subdir/here2.php"),
                   '' => array("One.xml"));
        $c = array_merge(array_diff($a, $b),
                         array_diff($b, $a));
        $this->assertEquals(array(), $c);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testGetModuleFileListWithNoClass() {
        $data_dir = self::data_dir(__CLASS__)."/FilesNoClass";
        self::$c->getAvailableModules(array($data_dir));

        $a = self::$c->getModuleFileList("files-test");
        $b = array ("lib/subdir/here2.php",
                    "here.php",
                    "not-here/dont-look.php",
                    "nothere.txt",
                    "bin/test-binary",
                    "conf/some.cfg",
                    "One.xml");

        $c = array_merge(array_diff($b, $a), 
                         array_diff($a, $b));
        $this->assertEquals(array(), $c);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testGetRegisteredFiles() {
        $available = self::$c->getAvailableModules(array(self::data_dir(__CLASS__).
                                                         "GetRegisteredFiles"),
                                                   TRUE, TRUE, TRUE);

        $files = self::$c->getModuleFileList("one");
        $expectedFiles = array ("lib/subdir/here2.php",
                                "lib/here.php",
                                "One.xml");
        $c = array_diff($expectedFiles, $files);
        $this->assertEquals(array(), $c, "Arrays don't match");
        $this->assertEquals(array(), self::$errors);
        /* $this->assertEquals(array(), self::$not_errors); # notices produced */

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testPrefixCheck() {
        $a = self::$c->getAvailableModules(array(self::data_dir(__CLASS__).
                                                 "PrefixCheck/OneModule"),
                                           TRUE, TRUE, TRUE);

        $this->assertEquals(array("OneModule"), $a);
        $this->assertEquals(array(), self::$errors);
        /* $this->assertEquals(array(), self::$not_errors); */

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

    public function testPrefixCheckListFiles() {
        self::$c->getAvailableModules(array(self::data_dir(__CLASS__).
                                            "PrefixCheck/OneModule"),
                                      TRUE, TRUE, TRUE);

        $this->assertEquals(array("One.xml", "can-you-see-me.txt"),
                            self::$c->getModuleFileList("OneModule"));
        $this->assertEquals(array(), self::$errors);
        /* $this->assertEquals(array(), self::$not_errors); */

        $this->assertEquals(array(), I2CE::getFileSearch()->getSearchPath('MODULES'));
        $this->assertEquals(array(), self::$errors);
    }

}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
