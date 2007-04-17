<?php
/**
 * Installation operations
 *
 * @author Team USVN <contact@usvn.info>
 * @link http://www.usvn.info
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2-en.txt CeCILL V2
 * @copyright Copyright 2007, Team USVN
 * @since 0.5
 * @package install
 *
 * This software has been written at EPITECH <http://www.epitech.net>
 * EPITECH, European Institute of Technology, Paris - FRANCE -
 * This project has been realised as part of
 * end of studies project.
 *
 * $Id$
 */

// Call InstallTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "InstallTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'www/install/Install.php';
require_once 'www/USVN/autoload.php';

/**
 * Test class for Install.
 * Generated by PHPUnit_Util_Skeleton on 2007-03-20 at 09:07:00.
 */
class InstallTest extends USVN_Test_Test {
	private $db;

	/**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main() {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("InstallTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

	private function clean()
	{
		USVN_Db_Utils::deleteAllTables($this->db);
		if (file_exists('tests/tmp/config.ini')) {
			unlink('tests/tmp/config.ini');
		}
		if (file_exists('tests/tmp/.htaccess')) {
			unlink('tests/tmp/.htaccess');
		}
	}

    public function setUp() {
		parent::setUp();
		$params = array ('host' => 'localhost',
                 'username' => 'usvn-test',
                 'password' => 'usvn-test',
                 'dbname'   => 'usvn-test');

		$this->db = Zend_Db::factory('PDO_MYSQL', $params);
		Zend_Db_Table::setDefaultAdapter($this->db);
		USVN_Db_Utils::deleteAllTables($this->db);
		$_SERVER['SERVER_NAME'] = "localhost";
		$_SERVER['REQUEST_URI'] = "/test/install/index.php?step=7";
		$this->clean();
    }

    public function tearDown() {
		$this->clean();
		parent::tearDown();
    }

    public function testInstallDbHostIncorrect() {
		try {
			Install::installDb("tests/tmp/config.ini", "www/SQL", "fake.usvn.info", "usvn-test", "usvn-test", "usvn-test", "usvn_");
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
    }

    public function testInstallDbUserIncorrect() {
		try {
			Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-fake", "usvn-test", "usvn-test", "usvn_");
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
    }

	public function testInstallDbPasswordIncorrect() {
		try {
			Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-fake", "usvn-test", "usvn_");
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
    }

	public function testInstallDbDatabaseIncorrect() {
		try {
			Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-test", "usvn-fake", "usvn_");
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
    }

	public function testInstallDbConfigFileNotWritable() {
		try {
			Install::installDb("tests/fake/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-test", "usvn-test", "usvn_");
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
    }

	public function testInstallDbTestLoadDb() {
		Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-test", "usvn-test", "usvn_");
		$list_tables =  $this->db->listTables();
		$this->assertTrue(in_array('usvn_users', $list_tables));
		$this->assertTrue(in_array('usvn_files', $list_tables));
		$userTable = new USVN_Db_Table_Users();
		$this->assertNotEquals(False, $userTable->fetchRow(array('users_login = ?' => 'anonymous')));
    }

	public function testInstallDbTestLoadDbOtherPrefixe() {
		Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-test", "usvn-test", "fake_");
		$list_tables =  $this->db->listTables();
		$this->assertFalse(in_array('usvn_users', $list_tables));
		$this->assertFalse(in_array('usvn_files', $list_tables));
		$this->assertTrue(in_array('fake_users', $list_tables));
		$this->assertTrue(in_array('fake_files', $list_tables));
    }

	public function testInstallDbTestConfigFile() {
		Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-test", "usvn-test", "usvn_");
		$this->assertTrue(file_exists("tests/tmp/config.ini"));
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("localhost", $config->database->options->host);
		$this->assertEquals("usvn-test", $config->database->options->dbname);
		$this->assertEquals("usvn-test", $config->database->options->username);
		$this->assertEquals("usvn-test", $config->database->options->password);
		$this->assertEquals("pdo_mysql", $config->database->adapterName);
		$this->assertEquals("usvn_", $config->database->prefix);
    }

	public function testInstallLanguage()
	{
		Install::installLanguage("tests/tmp/config.ini", "fr_FR");
		$this->assertTrue(file_exists("tests/tmp/config.ini"));
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("fr_FR", $config->translation->locale);
	}

	public function testInstallBadLanguage()
	{
		try {
			Install::installLanguage("tests/tmp/config.ini", "fake");
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}

	public function testInstallUrl()
	{
		Install::installUrl("tests/tmp/config.ini", "tests/tmp/.htaccess");
		$this->assertTrue(file_exists("tests/tmp/config.ini"));
		$this->assertTrue(file_exists("tests/tmp/.htaccess"));
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("/test", $config->url->base);
		$this->assertEquals("localhost", $config->url->host);
		$htaccess = file_get_contents("tests/tmp/.htaccess");
		$this->assertContains("RewriteBase /test", $htaccess);
	}

	public function testInstallUrlCantWriteHtaccess()
	{
		try {
			Install::installUrl("tests/tmp/config.ini", "tests/fake/.htaccess");
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}

	public function testInstallUrlCantWriteConfig()
	{
		try {
			Install::installUrl("tests/fake/config.ini", "tests/tmp/.htaccess");
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}

	public function testInstallAdmin()
	{
		Install::installDb("tests/tmp/config.ini", "www/SQL", "localhost", "usvn-test", "usvn-test", "usvn-test", "usvn_");
		Install::installAdmin("tests/tmp/config.ini", "root", "secretpassword", "James", "Bond");
		$userTable = new USVN_Db_Table_Users();
		$user = $userTable->fetchRow(array('users_login = ?' => 'root'));
		$this->assertNotEquals(False, $user);
		$this->assertEquals($user->password, crypt("secretpassword", $user->password));
		$this->assertEquals("James", $user->firstname);
		$this->assertEquals("Bond", $user->lastname);
	}

	public function testInstallEnd()
	{
		Install::installEnd("tests/tmp/config.ini");
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("0.5", $config->version);
	}

	public function testInstallPossibleNoConfigFile()
	{
		$this->assertTrue(Install::installPossible("tests/tmp/config.ini"));
	}

	public function testInstallPossibleInstallNotEnd()
	{
		Install::installLanguage("tests/tmp/config.ini", "fr_FR");
		$this->assertTrue(Install::installPossible("tests/tmp/config.ini"));
	}

	public function testInstallNotPossible()
	{
		Install::installEnd("tests/tmp/config.ini");
		$this->assertFalse(Install::installPossible("tests/tmp/config.ini"));
	}

	public function testInstallConfiguration()
	{
		Install::installConfiguration("tests/tmp/config.ini", "Noplay", "Test description");
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("Noplay", $config->site->title);
		$this->assertEquals("default", $config->template->name);
		$this->assertEquals("Test description", $config->site->description);
		$this->assertEquals("medias/default/images/USVN.ico", $config->site->ico);
		$this->assertEquals("medias/default/images/USVN-logo.png", $config->site->logo);
	}

	public function testInstallConfigurationNotTitle()
	{
		try {
			Install::installConfiguration("tests/tmp/config.ini", "", "");
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}
}

// Call InstallTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "InstallTest::main") {
    InstallTest::main();
}
?>
