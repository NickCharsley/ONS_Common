<?php
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', 'simpletest/');
}
require_once("../lib/general_functions.php");
require_once("mocks/mock_db.php");
require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once("fixtures.php");
require_once("../lib/create_table.php");
require_once("../databases/Mysql.php");

class TestOfCreateTable extends UnitTestCase {
    function TestOfCreateTable() {
        $this->UnitTestCase("CreateTable Class Tests");
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
        $query = "users,engine:InnoDB,char_set:latin1,comment:some comment
    column:id,type:int,length:11,auto:true,not_null:true
    column:first_name,type:varchar,length:100,default:NULL
    column:last_name,type:varchar,length:100,default:NULL
     column:updated_at,type:datetime,default:NULL
    column:created_at,type:datetime,default:NULL
    primary_key:id,first_name";
        $sql =& new CreateTable($alc, $query);
        
        $this->assertEqual("users", $sql->table);
        $this->assertEqual("CREATE TABLE IF NOT EXISTS users ( id INT(11)  NOT NULL AUTO_INCREMENT,first_name VARCHAR(100)  DEFAULT NULL,last_name VARCHAR(100)  DEFAULT NULL,updated_at DATETIME  DEFAULT NULL,created_at DATETIME  DEFAULT NULL , PRIMARY KEY (id,first_name) ) ENGINE=InnoDB  DEFAULT CHARACTER SET = latin1 COMMENT = 'some comment';", $sql->query);
    }
}

$test =& new TestOfCreateTable();
$test->run(new TextReporter());
?>