<?php
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', 'simpletest/');
}
require_once("../lib/general_functions.php");
require_once("mocks/mock_db.php");
require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once("fixtures.php");
require_once("../lib/remove_column.php");
require_once("../databases/Mysql.php");

class TestOfRemoveColumn extends UnitTestCase {
    function TestOfRemoveColumn() {
        $this->UnitTestCase("RemoveColumn Class Tests");
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

        $query = "users, name";
        $sql =& new RemoveColumn($alc, $query);

        $this->assertEqual("users", $sql->table);
        $this->assertEqual("name", $sql->column_name);
        $this->assertEqual("ALTER TABLE users DROP COLUMN name;", $sql->query);
}}

$test =& new TestOfRemoveColumn();
$test->run(new TextReporter());
?>