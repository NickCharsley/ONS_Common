<?php
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', 'simpletest/');
}
require_once("../lib/general_functions.php");
require_once("mocks/mock_db.php");
require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once("fixtures.php");
require_once("../lib/rename_column.php");
require_once("../databases/Mysql.php");

class TestOfRenameColumn extends UnitTestCase {
    function TestOfRenameColumn() {
        $this->UnitTestCase("RenameColumn Class Tests");
    }

    function setUp() {
    }

    function tearDown() {
    }

    function testTruth() {
        $this->assertTrue(TRUE);
    }

    function testInitialiazeMysql() {
        $alc =& new Mysql(default_database_config());

        $query = "schema_info, version, new_version, type : varchar, length : 30";
        $sql =& new RenameColumn($alc, $query);

        $this->assertEqual("schema_info", $sql->table);
        $this->assertEqual("version", $sql->column_name);
        $this->assertEqual("new_version", $sql->new_name);
        $this->assertEqual("ALTER TABLE schema_info CHANGE COLUMN version new_version VARCHAR(30) ;", $sql->query);
    }
}

$test =& new TestOfRenameColumn();
$test->run(new TextReporter());
?>