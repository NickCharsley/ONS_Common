<?php
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', 'simpletest/');
}
require_once("../lib/general_functions.php");
require_once("mocks/mock_db.php");
require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once("fixtures.php");
require_once("../phpdbmigrate.php");

class TestOfPHPDbMigrate extends UnitTestCase {
    function TestOfPHPDbMigrate() {
        $this->UnitTestCase("PHPDbMigrate Class Tests");
    }

    function setUp() {
    }

    function tearDown() {
        delete_migration_directory();
    }

    function testTruth() {
        $this->assertTrue(TRUE);
    }

    function testInitializeWithGivenConfig() {
        $pm =& new PHPDbMigrate(default_config());
        $this->assertEqual(default_config(), $pm->config);
    }

    function testGetNumberWithCorrectFormat() {
        $pm =& new PHPDbMigrate(default_config());
        $filename = "001_test_me.ini";
        $num = $pm->_get_file_number($filename);
        $this->assertEqual("001", $num);
    }

    function testVerifyUniqueFileNumberInMigrations() {
        $pm =& new PHPDbMigrate(default_config());
        $files = array('001_test_one.ini', '002_test_two.ini');
        $this->assertTrue($pm->_check_duplicates($files));
    }

    function testVerifyUniqueFileNumberInMigrationsThatNotUnique() {
        $pm =& new PHPDbMigrate(default_config());
        $files = array('001_test_one.ini', '001_test_two.ini');
        $this->expectException();
        $pm->_check_duplicates($files);
    }

    function testFindAllTheMigrationFilesInTheMigrationFolder() {
        build_migration_directory();
        build_file("migrations/001_file.ini");
        build_file("migrations/002_file.ini");
        $pm =& new PHPDbMigrate(default_config());
        $files = $pm->_build_files_list('migrations');

        $this->assertTrue(array(
                array('number' => '001', 'file' => '001_file.ini'),
                array('number' => '002', 'file' => '002_file.ini')),
            $files);
    }

    function testFindAllTheMigrationFilesInTheMigrationFolderWithNonMigrations() {
        build_migration_directory();
        build_file("migrations/001_file.ini");
        build_file("migrations/002_file.ini");
        build_file("README.txt");
        $pm =& new PHPDbMigrate(default_config());
        $files = $pm->_build_files_list('migrations');
        $this->assertEqual(array(
                array('number' => '001', 'file' => '001_file.ini'),
                array('number' => '002', 'file' => '002_file.ini')),
            $files);
    }

    function testParseCommandFromFile() {
        $pm =& new PHPDbMigrate(default_config());
        $cmdline = "sql: SELECT *";
        $result = $pm->_parse_command($cmdline);
        $this->assertEqual(array('sql', 'SELECT *'), $result);
    }

    function testParseFilesWithTwoMigrationsUpVersion() {
        build_migration_directory();
        build_file("migrations/001_file.ini", build_test_one_file());
        build_file("migrations/002_file.ini", build_test_two_file());
        $pm =& new PHPDbMigrate(default_config());
        $queries = $pm->_parse_file("[up]", "[down]", "001_file.ini", "test");

        $this->assertEqual( array(array('command' => 'sql', 'data' => "SELECT *
 FROM
 schema_info
")),
                          $queries);

        $queries = $pm->_parse_file("[up]", "[down]", "002_file.ini", "test");
        $this->assertEqual(array(array('command' => 'sql', 'data' => 'SELECT * FROM accounts')),
                          $queries);
    }

    function testParseFilesWithTwoMigrationsDownVersion() {
        build_migration_directory();
        build_file("migrations/001_file.ini", build_test_one_file());
        build_file("migrations/002_file.ini", build_test_two_file());
        $pm =& new PHPDbMigrate(default_config());
        $queries = $pm->_parse_file("[down]", "[up]", "001_file.ini", "test");
        $this->assertEqual(array(array('data' => 'SELECT * FROM users', 'command' => 'sql')),
                          $queries);

        $queries = $pm->_parse_file("[down]", "[up]", "002_file.ini", "test");
        $this->assertEqual(array(array('data' => 'SELECT * FROM links', 'command' => 'sql')),
                          $queries);
    }

    function testDetermineStopVersionStarting000Stopping002With003file() {
        build_migration_directory();
        build_file("migrations/001_file.ini", build_test_one_file());
        build_file("migrations/002_file.ini", build_test_two_file());
        build_file("migrations/003_file.ini", build_test_two_file());
        $pm =& new PHPDbMigrate(default_config());
        $files = $pm->_build_files_list('migrations');
        $control = $pm->_determine_stop_version(000, 002, $files);
        $this->assertEqual(array('start' => 0, 'stop' => 2, 'walk' => 'up'), $control);
    }

    function testDetermineStopVersionStarting000Stopping001With003file() {
        build_migration_directory();
        build_file("migrations/001_file.ini", build_test_one_file());
        build_file("migrations/002_file.ini", build_test_two_file());
        build_file("migrations/003_file.ini", build_test_two_file());
        $pm =& new PHPDbMigrate(default_config());
        $files = $pm->_build_files_list('migrations');
        $control = $pm->_determine_stop_version(000, 001, $files);
        $this->assertEqual(array('start' => 0, 'stop' => 1, 'walk' => 'up'), $control);
    }

    function testDetermineStopVersionStarting003Stopping001() {
        build_migration_directory();
        build_file("migrations/001_file.ini", build_test_one_file());
        build_file("migrations/002_file.ini", build_test_two_file());
        build_file("migrations/003_file.ini", build_test_two_file());
        $pm =& new PHPDbMigrate(default_config());
        $files = $pm->_build_files_list('migrations');
        $control = $pm->_determine_stop_version(003, 001, $files);
        $this->assertEqual(array('start' => 3, 'stop' => 1, 'walk' => 'down'), $control);
    }

    function testDetermineStopVersionStarting003Stopping003() {
        build_migration_directory();
        build_file("migrations/001_file.ini", build_test_one_file());
        build_file("migrations/002_file.ini", build_test_two_file());
        build_file("migrations/003_file.ini", build_test_two_file());
        $pm =& new PHPDbMigrate(default_config());
        $files = $pm->_build_files_list('migrations');
        $control = $pm->_determine_stop_version(003, 003, $files);
        $this->assertEqual(array('start' => 3, 'stop' => 3, 'walk' => 'up'), $control);
    }

    function testDetermineStopVersionStarting001WithNoOptionVersion() {
        build_migration_directory();
        build_file("migrations/001_file.ini", build_test_one_file());
        build_file("migrations/002_file.ini", build_test_two_file());
        build_file("migrations/003_file.ini", build_test_two_file());
        $pm =& new PHPDbMigrate(default_config());
        $files = $pm->_build_files_list('migrations');
        $control = $pm->_determine_stop_version(001, NULL, $files);
        $this->assertEqual(array('start' => 1, 'stop' => 3, 'walk' => 'up'), $control);
    }

    function testRunFilesWithOutError() {
        build_migration_directory();
        build_file("migrations/001_file.ini", "[up]\n[down]");
        build_file("migrations/002_file.ini", "[up]\n[down]");
        build_file("migrations/003_file.ini", "[up]\n[down]");

        $pm =& new PHPDbMigrate(default_config());
        $db =& new Mockdb();
        $pm->db =& $db;

        $files = $pm->_build_files_list('migrations');
        $control = $pm->_determine_stop_version(000, NULL, $files);

        $pm->_run_files($files, $control, "test");

        $this->assertEqual('003', $db->get_version());
    }

    function testVersionUp() {
        build_migration_directory();
        build_file("migrations/001_file.ini", "[up]\n[down]");
        build_file("migrations/002_file.ini", "[up]\n[down]");
        build_file("migrations/003_file.ini", "[up]\n[down]");

        $pm =& new PHPDbMigrate(default_config());
        $db =& new Mockdb();
        $pm->db =& $db;

        $files = $pm->_build_files_list('migrations');
        $control = $pm->_determine_stop_version(000, 002, $files);

        $this->assertTrue($pm->_run_version_up($control, 0, $files));
        $this->assertTrue($pm->_run_version_up($control, 1, $files));
        $this->assertFalse($pm->_run_version_up($control, 2, $files));
    }

    function testVersionDown() {
        build_migration_directory();
        build_file("migrations/001_file.ini", "[up]\n[down]");
        build_file("migrations/002_file.ini", "[up]\n[down]");
        build_file("migrations/003_file.ini", "[up]\n[down]");

        $pm =& new PHPDbMigrate(default_config());
        $db =& new Mockdb();
        $pm->db =& $db;

        $files = $pm->_build_files_list('migrations');
        $control = $pm->_determine_stop_version(002, 000, $files);
        $this->assertFalse($pm->_run_version_down($control, 0, $files));
        $this->assertTrue($pm->_run_version_down($control, 1, $files));
        $this->assertTrue($pm->_run_version_down($control, 2, $files));
    }

    function testExecuteFileUp() {
        build_migration_directory();
        build_file("migrations/001_file.ini", "[up]\n[down]");

        $pm =& new PHPDbMigrate(default_config());
        $db =& new Mockdb();
        $pm->db =& $db;

        $files = $pm->_build_files_list('migrations');
        $control = $pm->_determine_stop_version(000, 001, $files);

        $pm->_execute_file("up", "down", 0, $files, 1, "test");

        $this->assertEqual(1, $db->get_version());
    }

    function testExecuteFileDown() {
        build_migration_directory();
        build_file("migrations/001_file.ini", "[up]\n[down]");

        $pm =& new PHPDbMigrate(default_config());
        $db =& new Mockdb();
        $pm->db =& $db;

        $files = $pm->_build_files_list('migrations');
        $control = $pm->_determine_stop_version(001, 000, $files);

        $pm->_execute_file("down", "up", 0, $files, 000, "test");

        $this->assertEqual(0, $db->get_version());
    }

    function testExecuteFileOnErrorUp() {
        build_migration_directory();
        build_file("migrations/001_file.ini", "[up]\n[down]");
        build_file("migrations/002_file.ini", "[up]\ncode: throw new Exception('fail');\n[down]");
        build_file("migrations/003_file.ini", "[up]\n[down]");

        $pm =& new PHPDbMigrate(default_config());
        $db =& new Mockdb();
        $pm->db =& $db;

        $files = $pm->_build_files_list('migrations');
        $pm->_execute_file("up", "down", 0, $files, 3, "test");
        $this->expectException();
        $pm->_execute_file("up", "down", 1, $files, 3, "test");

        $this->assertEqual(1, $db->get_version());
    }

    function testExecuteEndToEndUpWithNoVersionUpArgument() {
        build_migration_directory();
        build_file("migrations/001_file.ini", "[up]\n[down]");
        build_file("migrations/002_file.ini", "[up]\n[down]");
        build_file("migrations/003_file.ini", "[up]\n[down]");

        $pm =& new PHPDbMigrate(default_config());
        $db =& new Mockdb();
        $pm->db =& $db;

        $this->assertEqual(3, $pm->run(NULL, "test"));
    }

    function testExecuteEndToEndUpWithVersionUpArgument() {
        build_migration_directory();
        build_file("migrations/001_file.ini", "[up]\n[down]");
        build_file("migrations/002_file.ini", "[up]\n[down]");
        build_file("migrations/003_file.ini", "[up]\n[down]");

        $pm =& new PHPDbMigrate(default_config());

        $this->assertEqual(2, $pm->run(002, "test"));
        reset_db($pm);
    }

    function testExecuteEndToEndUpNoVersionUpArgumentOnError() {
        build_migration_directory();
        build_file("migrations/001_file.ini", "[up]\n[down]");
        build_file("migrations/002_file.ini", "[up]\ncode:\nthrow new Exception();\n[down]");
        build_file("migrations/003_file.ini", "[up]\n[down]");

        $pm =& new PHPDbMigrate(default_config());
        $this->expectException();
        $this->assertEqual(1, $pm->run(NULL, "test"));
        reset_db($pm);
    }

    function testStartTheFileCommandsUpWithUpKeyword() {
        $pm =& new PHPDbMigrate(default_config());
        $this->assertTrue($pm->_start_file_commands_recording("[up]", "[up]"));
    }

    function testNotStartTheFileCommandsUpWithDownKeyword() {
        $pm =& new PHPDbMigrate(default_config());
        $this->assertFalse($pm->_start_file_commands_recording("[up]", "[down]"));
    }

    function testStopTheFileCommandsDownWithDownKeyword() {
        $pm =& new PHPDbMigrate(default_config());
        $this->assertTrue($pm->_stop_file_commands_recording("[down]", "[down]"));
    }

    function testStopTheFileCommandsDownWithUpKeyword() {
        $pm =& new PHPDbMigrate(default_config());
        $this->assertFalse($pm->_stop_file_commands_recording("[down]","[up]"));
    }

    function testBuildQueriesList() {
        $queries = array();
        $pm =& new PHPDbMigrate(default_config());
        $pm->_build_queries_list($queries, "code: print");
        $this->assertEqual(array(array('data' => 'print', 'command' => 'code')), $queries);
        $pm->_build_queries_list($queries, "code: print");
        $this->assertEqual(array(array('data' => 'print', 'command' => 'code'),
            array('data' => 'print', 'command' => 'code')),
            $queries);
    }
}

function build_migration_directory() {
    if (!is_dir('migrations')) {
        mkdir('migrations');
    }
}

function delete_migration_directory() {
    if (!is_dir('migrations')) {
        return;
    }

    if ($handle=opendir('migrations'))
    {
      while (false!==($file=readdir($handle)))
      {
        if ($file<>"." AND $file<>"..")
        {
          if (is_file('migrations/'.$file))
          {
            @unlink('migrations/'.$file);
          }
        }
      }
    }

    rmdir('migrations');
}

function build_file($filename, $data="") {
    $fh = fopen($filename, 'w') or die("can't open file");
    fwrite($fh, $data);
    fclose($fh);
}

function build_test_one_file() {
    return "[up]
sql: SELECT
*
FROM
schema_info

[down]
sql: SELECT * FROM users
";
}

function build_test_two_file() {
    return "[up]
sql: SELECT * FROM accounts

[down]
sql: SELECT * FROM links
";
}

function reset_db($pm) {
    $query = "UPDATE schema_info set version = '000';";
    $pm->db->execute_raw_query($query);
}

$test =& new TestOfPHPDbMigrate();
$test->run(new TextReporter());

?>
