<?php
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', 'simpletest/');
}
require_once("../lib/general_functions.php");
require_once("mocks/mock_db.php");
require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once("fixtures.php");
require_once("../lib/remove_index.php");
require_once("../databases/Mysql.php");

class TestOfRemoveIndex extends UnitTestCase {
    function TestOfRemoveIndex() {
        $this->UnitTestCase("RemoveIndex Class Tests");
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

        $query = "schema_info, schema_type";
        $sql =& new RemoveIndex($alc, $query);

        $this->assertEqual("schema_info", $sql->table);
        $this->assertEqual("schema_type", $sql->column_name);
        $this->assertEqual("ALTER TABLE schema_info DROP INDEX schema_type;", $sql->query);
    }
}

$test =& new TestOfRemoveIndex();
$test->run(new TextReporter());
?>