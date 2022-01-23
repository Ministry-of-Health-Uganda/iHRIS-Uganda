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
require_once dirname(__FILE__).'/lib/I2CE_Framework_TestClass.php';

ini_set('include_path',ini_get('include_path').":".
        TOP_DIR."/modules/PackageUtils/lib");

//I2CE_Framework_TestClass::addClassPath($path);
require_once 'I2CE_Packager.php';
require_once 'I2CE_Configurator.php';
require_once 'I2CE_Process.php';


/**
 * Configurator Test class -- handles dependencies and such.
 * @package I2CE
 */
class I2CE_PackagerTest extends I2CE_Framework {
    public function testTopPackageName() {
        self::$c->setTopModule(".", "I2CE");
        $p = new I2CE_Packager("iHRIS_Manage", self::$c);

        $this->assertEquals("i2ce",
                            $p->getPackageName("I2CE"));
        $this->assertEquals("i2ce-ihris-common",
                            $p->getPackageName("iHRIS_Common"));
        $this->assertEquals("i2ce-ihris-manage",
                            $p->getPackageName("iHRIS_Manage"));
        $this->assertEquals("i2ce-ihris-qualify",
                            $p->getPackageName("iHRIS_Qualify"));

        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
    }

    public function testPermutate() {
        self::$c->setTopModule(".", "ihris-manage");
        $p = new I2CE_Packager("iHRIS_Manage", self::$c);

        $this->assertEquals("i2ce-ihris-manage-salary",
                            $p->getPackageName("ihris-manage-Salary"));
        $this->assertEquals
          ("i2ce-ihris-manage-training-sample-data",
           $p->getPackageName("ihris-manage-training-sample-data"));
        $this->assertEquals("i2ce-simple-competency",
                            $p->getPackageName("simple-competency"));

        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
    }

    public function testTopNotInTopList() {
        self::$c->setTopModule(".", "ihris-manage-site-demo");
        $p = new I2CE_Packager("iHRIS_Manage", self::$c);

        $this->assertEquals("i2ce-ihris-manage-site-demo",
                            $p->getPackageName("ihris-manage-site-demo"));

        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
    }

    public function testPrependCorrectTop() {
        self::$c->setTopModule(".", "I2CE");
        $p = new I2CE_Packager("I2CE", self::$c);

        $this->assertEquals("i2ce-forms",
                            $p->getPackageName("Forms"));

        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
    }

    public function testGetFileSearch() {
        $fs = I2CE::getFileSearch();
        $this->assertTrue($fs instanceOf I2CE_FileSearch);
    }        

    public function testBogusPackageBuild() {
        $dir = realpath(dirname(dirname(__FILE__)));
        $build_dir = self::data_dir(__CLASS__)."/I2CE-Build/";
        self::$c->getAvailableModules(array($dir));

        $packager = new I2CE_Packager("BOGUS", self::$c);
        $this->assertEquals(array(), self::$errors);
    }

    public function testTrimPath() {
        $file = "/one/two/three";

        $a = I2CE_Packager::trimPath($file, 0);
        $b = "one/two/three";
        $this->assertEquals($b, $a);

        $a = I2CE_Packager::trimPath($file, 1);
        $b = "two/three";
        $this->assertEquals($b, $a);

        $a = I2CE_Packager::trimPath($file, 2);
        $b = "three";
        $this->assertEquals($b, $a);
    }

    public function testGetTopModule() {
        $dir = self::data_dir(__CLASS__)."/testI2CE";

        $a = self::$c->getTopModule($dir);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $this->assertEquals("testI2CE", $a);
    }


    public function testGetAvailableModulesBadArg() {
        $data_dir = self::data_dir(__CLASS__)."/Files";
        self::$c->getAvailableModules($data_dir);

        $this->assertContains("Invalid argument search_dirs passed to getAvailableModules.",
                              self::$errors);
        $this->assertEquals(array(), self::$not_errors);
    }

    public function testFilesNoClass() {
        $data_dir = self::data_dir(__CLASS__)."FilesNoClass";
        self::$c->getAvailableModules(array($data_dir));

        $packager = new I2CE_Packager("files-test", self::$c);
        $a = $packager->files();
        $b = array ("lib/subdir/here2.php",
                    "here.php",
                    "not-here/dont-look.php",
                    "nothere.txt",
                    "bin/test-binary",
                    "conf/some.cfg",
                    "One.xml");
        $c = array_merge(array_diff($a, $b),
                         array_diff($b, $a));
        $this->assertEquals(array(), $c);
    }

    public function testDir() {
        $data_dir = self::data_dir(__CLASS__)."Files";
        $build_dir = self::data_dir(__CLASS__)."I2CE-Build";
        self::$c->getAvailableModules(array($data_dir));

        $packager = new I2CE_Packager("files-test", self::$c);
        $this->assertEquals(realpath(NULL), $packager->dir());
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        system("rm -rf $build_dir");
        $packager->dir($build_dir);
        $this->assertEquals(array(), self::$errors);
        $this->assertContains("$build_dir does not exist", self::$not_errors);
        self::$not_errors = array();

        mkdir($build_dir, 0777, TRUE);
        $packager->dir($build_dir);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertEquals($build_dir, $packager->dir());
    }

    public function testFiles() {
        $data_dir = self::data_dir(__CLASS__)."Files";
        $build_dir = self::data_dir(__CLASS__)."/I2CE-Build/";
        self::$c->getAvailableModules(array($data_dir));

        $packager = new I2CE_Packager("files-test", self::$c);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $a = $packager->files();
        $b = array ("lib/subdir/here2.php",
                    "lib/here.php",
                    "bin/test-binary",
                    "conf/some.cfg",
                    "One.xml");
        $c = array_diff($a, $b);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), $c, "List of files differs");
    }

    public function testDebianSetupPackageDir() {
        $data_dir = self::data_dir(__CLASS__)."/Files";
        $check_dir = self::data_dir(__CLASS__)."/i2ce-files-test/";
        $build_dir = self::data_dir(__CLASS__)."/I2CE-Build";
        self::$c->getAvailableModules(array($data_dir));

        system("rm -rf {$build_dir}");
        $packager = new I2CE_Packager("files-test", self::$c);
        $packager->dir($build_dir, TRUE);
        $deb = new I2CE_Packager_Debian($packager, "files-test");
        $deb->setupPackageDir();
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
        $this->assertFileEquals("$check_dir/debian/i2ce-files-test.install",
                                "$build_dir/DEB/i2ce-files-test/debian/i2ce-files-test.install");
    }

    public function testDebianCopyOriginalSource() {
        $data_dir = self::data_dir(__CLASS__)."/Files";
        $check_dir = self::data_dir(__CLASS__)."/i2ce-files-test/";
        $build_dir = self::data_dir(__CLASS__)."/I2CE-Build";
        self::$c->getAvailableModules(array($data_dir));

        system("rm -rf {$build_dir}");
        $packager = new I2CE_Packager("files-test", self::$c);
        $packager->dir($build_dir, TRUE);

        $deb = new I2CE_Packager_Debian($packager, "files-test");
        $deb->setupPackageDir();
        chdir($packager->startDir());
        $deb->copyOriginalSource();
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
        chdir($packager->startDir());
    }

    public function testDebianBuildBinary() {
        $data_dir = self::data_dir(__CLASS__)."/Files";
        $check_dir = self::data_dir(__CLASS__)."/i2ce-files-test/";
        $build_dir = self::data_dir(__CLASS__)."/I2CE-Build";
        self::$c->getAvailableModules(array($data_dir));

        system("rm -rf {$build_dir}");
        $packager = new I2CE_Packager("files-test", self::$c);
        $packager->dir($build_dir, TRUE);

        $deb = new I2CE_Packager_Debian($packager, "files-test");
        $deb->setupPackageDir();
        chdir($packager->startDir());
        $deb->copyOriginalSource();
        chdir($packager->startDir());
        $deb->buildBinary();
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
    }

    public function testBuildPackage() {
        $data_dir = self::data_dir(__CLASS__)."/Files";
        $check_dir = self::data_dir(__CLASS__)."/i2ce-files-test/";
        $build_dir = self::data_dir(__CLASS__)."/I2CE-Build";
        system("rm -rf {$build_dir}");
        self::$c->getAvailableModules(array($data_dir));

        $packager = new I2CE_Packager("files-test", self::$c);

        $packager->buildPackage($build_dir);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);

        $deb = glob($build_dir."/DEB/i2ce-files-test*.deb");
        $this->assertFileExists($deb[0]);

        $p = new I2CE_Process("dpkg-deb --contents {$deb[0]}");
        $this->assertEquals(FALSE, $p->is_error());

        $path = array();
        foreach(explode("\n", $p->stdout()) as $line) {
            if(!empty($line)) {
                $bit = preg_split("{[ \t]+}", $line, 6);
                $path[] = $bit[5];
            }
        }
        $this->assertEquals(array("./",
                                  "./etc/",
                                  "./etc/i2ce/",
                                  "./etc/i2ce/some.cfg",
                                  "./usr/",
                                  "./usr/share/",
                                  "./usr/share/doc/",
                                  "./usr/share/doc/i2ce-files-test/",
                                  "./usr/share/doc/i2ce-files-test/copyright",
                                  "./usr/share/doc/i2ce-files-test/changelog.Debian.gz",
                                  "./usr/share/i2ce/",
                                  "./usr/share/i2ce/I2CE/",
                                  "./usr/share/i2ce/I2CE/tests/",
                                  "./usr/share/i2ce/I2CE/tests/I2CE_PackagerTest/",
                                  "./usr/share/i2ce/I2CE/tests/I2CE_PackagerTest/Files/",
                                  "./usr/share/i2ce/I2CE/tests/I2CE_PackagerTest/Files/One.xml",
                                  "./usr/share/i2ce/I2CE/tests/I2CE_PackagerTest/Files/lib/",
                                  "./usr/share/i2ce/I2CE/tests/I2CE_PackagerTest/Files/lib/subdir/",
                                  "./usr/share/i2ce/I2CE/tests/I2CE_PackagerTest/Files/lib/subdir/here2.php",
                                  "./usr/share/i2ce/I2CE/tests/I2CE_PackagerTest/Files/lib/here.php",
                                  "./usr/bin/",
                                  "./usr/bin/test-binary",
                                  ), $path);
        $this->assertEquals(array(), self::$errors);
        $this->assertEquals(array(), self::$not_errors);
    }

    static private $c;
    static private $startDir;
    function setUp() {
        self::$startDir = getcwd();
        parent::setUp();
        I2CE::setupFileSearch();
        self::$c = new I2CE_Configurator;
        self::$c->resetCheckedPaths();
    }

    function tearDown() {
        chdir(self::$startDir);
        parent::tearDown();
    }
}
# Local Variables:
# mode: php
# c-default-style: bsd
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
