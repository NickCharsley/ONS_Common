<?php
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', 'simpletest/');
}
require_once("../lib/general_functions.php");
require_once("mocks/mock_db.php");
require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once("fixtures.php");
require_once("../lib/rename_table.php");
require_once("../databases/Mysql.php");

class TestOfRenameTable extends UnitTestCase {
    function TestOfRenameTable() {
        $this->UnitTestCase("RenameTable Class Tests");
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

        $query = "schema_info, schema_infos";
        $sql =& new RenameTable($alc, $query);

        $this->assertEqual("schema_info", $sql->table);
        $this->assertEqual("schema_infos", $sql->new_name);
        $this->assertEqual("ALTER TABLE schema_info RENAME TO schema_infos;", $sql->query);
    }
}

$test =& new TestOfRenameTable();
$test->run(new TextReporter());
?>