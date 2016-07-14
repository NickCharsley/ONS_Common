<?php
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', 'simpletest/');
}
require_once("../lib/general_functions.php");
require_once("mocks/mock_db.php");
require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once("fixtures.php");
require_once("../lib/sql.php");
require_once("../databases/Mysql.php");

class TestOfSql extends UnitTestCase {
    function TestOfSql() {
        $this->UnitTestCase("Sql Class Tests");
    }

    function setUp() {
    }

    function tearDown() {
    }

    function testTruth() {
        $this->assertTrue(TRUE);
    }

    function testSingleLineFormat() {
        $query = "SELECT * FROM schema_info;";
        $sql =& new Sql(array(), $query);

        $this->assertEqual("SELECT * FROM schema_info;", $sql->query);
    }

    function testMultiLineFormat() {
        $query = "SELECT
*
FROM
schema_info;";
        $sql =& new Sql(array(), $query);

        $this->assertEqual("SELECT
*
FROM
schema_info;", $sql->query);
    }

    function testExecute() {
        $alc =& new Mysql(default_config());
        $query = "SELECT * FROM schema_info;";
        $sql =& new Sql($alc, $query);

        $this->assertTrue($sql->execute());
    }
}

$test =& new TestOfSql();
$test->run(new TextReporter());
?>
