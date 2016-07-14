<?php
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', 'simpletest/');
}
require_once("../lib/general_functions.php");
require_once("mocks/mock_db.php");
require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once("fixtures.php");
require_once("../lib/drop_table.php");
require_once("../databases/Mysql.php");

class TestOfDropTable extends UnitTestCase {
    function TestOfDropTable() {
        $this->UnitTestCase("DropTable Class Tests");
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
        $query = "users";
        $sql =& new DropTable($alc, $query);

        $this->assertEqual("users", $sql->table);
        $this->assertEqual("DROP TABLE users;", $sql->query);
    }
}

$test =& new TestOfDropTable();
$test->run(new TextReporter());
?>