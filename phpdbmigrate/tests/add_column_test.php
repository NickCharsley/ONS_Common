<?php
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', 'simpletest/');
}
require_once("../lib/general_functions.php");
require_once("mocks/mock_db.php");
require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once("fixtures.php");
require_once("../lib/add_column.php");
require_once("../databases/Mysql.php");

class TestOfAddColumn extends UnitTestCase {
    function TestOfAddColumn() {
        $this->UnitTestCase("AddColumn Class Tests");
    }

    function setUp() {
    }

    function tearDown() {
    }

    function testTruth() {
        $this->assertTrue(TRUE);
    }

    function testMysqlAddColumnMysql() {
        $alc =& new Mysql(default_database_config());
        $query = "schema_info, schema_type, type : integer, not_null : true, default : 0, after : version";
        $sql =& new AddColumn($alc, $query);

        $this->assertEqual("schema_info", $sql->table);
        $this->assertEqual("schema_type", $sql->column_name);
        $this->assertEqual("ALTER TABLE schema_info ADD schema_type INTEGER  NOT NULL DEFAULT '0' AFTER `version`;", $sql->query);
    }
}

$test =& new TestOfAddColumn();
$test->run(new TextReporter());
?>
