<?php
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', 'simpletest/');
}
require_once("../lib/general_functions.php");
require_once("mocks/mock_db.php");
require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once("fixtures.php");
require_once("../lib/change_column.php");
require_once("../databases/Mysql.php");

class TestOfChangeColumn extends UnitTestCase {
    function TestOfChangeColumn() {
        $this->UnitTestCase("ChangeColumn Class Tests");
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

        $query = "schema_info, version, type : varchar, length : 36, default : NULL";
        $sql =& new ChangeColumn($alc, $query);

        $this->assertEqual("schema_info", $sql->table);
        $this->assertEqual("version", $sql->column_name);
        $this->assertEqual("ALTER TABLE schema_info MODIFY COLUMN version VARCHAR(36)  DEFAULT NULL;", $sql->query);
    }
}

$test =& new TestOfChangeColumn();
$test->run(new TextReporter());
?>