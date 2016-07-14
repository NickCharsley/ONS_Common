<?php
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', 'simpletest/');
}
require_once("../lib/general_functions.php");
require_once("mocks/mock_db.php");
require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once("fixtures.php");
require_once("../lib/add_index.php");
require_once("../databases/Mysql.php");

class TestOfAddIndex extends UnitTestCase {
    function TestOfAddIndex() {
        $this->UnitTestCase("AddIndex Class Tests");
    }

    function setUp() {
    }

    function tearDown() {
    }

    function testTruth() {
        $this->assertTrue(TRUE);
    }

    function testFormat() {
        $alc =& new Mysql(default_database_config());

        $query = "schema_info, schema_type, unique : true, using : BTREE, columns:[version ASC, no_column DESC, another_column ASC]";
        $sql =& new AddIndex($alc, $query);

        $this->assertEqual("schema_info", $sql->table);
        $this->assertEqual("schema_type", $sql->column_name);
        $this->assertEqual("CREATE UNIQUE INDEX schema_type USING BTREE ON schema_info (version ASC, no_column DESC, another_column ASC);", $sql->query);
    }
}

$test =& new TestOfAddIndex();
$test->run(new TextReporter());
?>