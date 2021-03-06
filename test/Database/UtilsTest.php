<?php

 if (!defined("__ONS_COMMON__"))
    include_once('ons_common.php');

 include_once("database/utils.php");
 
class UtilsTest extends PHPUnit_Framework_TestCase
{

    static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        self::dropTables();

    }

    function testSplitDataObjectConfig(){
        global $config,$root_path;
        $config['DB_DataObject']=
                array(
                    "database" => "mysql://test:bhSTGCsFY32ApKeF@localhost/test_ons_common",
                    "schema_location" => '{$root_path}\\test\\resources\\database\\schema',
                    "class_location" => '{$root_path}\\test\\resources\\database',
                    "class_prefix" => "do",
                    "db_driver"	=> "MDB2",
                    "build_views"=> 1,
                    "generator_var_keyword"=> "public",
                    "extends"=> "dbRoot",
                    "extends_location" => "dbRoot.php"
                );
        $this->assertEquals(array(
                    "build_views"=> 1,
                    "class_location" => $root_path."\\test\\resources\\database",
                    "class_prefix" => "do",
                    "database"=>"test_ons_common",
                    "db_driver"	=> "MDB2",
                    "driver" => "mysql",
                    "extends"=> "dbRoot",
                    "extends_location" => "dbRoot.php",
                    "generator_var_keyword"=> "public",
                    "host"=>"localhost",
                    "password"=>"bhSTGCsFY32ApKeF",
                    "schema_location" => $root_path."\\test\\resources\\database\\schema",
                    "user"=>"test"
                ), SplitDataObjectConfig());
    }

    static private function dropTables(){
        //Need to drop all tables in test_ons_common;
        // uses MDB2::factory() to create the instance
        // and also attempts to connect to the host
        global $config;
                
        @$mdb2 =& MDB2::connect('mysql://test:bhSTGCsFY32ApKeF@localhost');
        @$mdb2->exec("DROP DATABASE IF EXISTS test_ons_common");
        @$mdb2->exec("CREATE DATABASE test_ons_common");
    }


    /**
     * @depends testSplitDataObjectConfig
     * @expectedException Exception
     * @expectedExceptionMessage Exit Called: No updates to run
     */
    function testMigrateDatabaseNoDirectory(){
        global $config,$test_path;
        $config['DB_DataObject']=
                array(
                    "database" => "mysql://test:bhSTGCsFY32ApKeF@localhost/test_ons_common",
                    "schema_location" => buildPath($test_path,"no directory"),
                );
        $this->dropTables();
        MigrateDatabase("test");
    } 

    /**
     * @depends testSplitDataObjectConfig
     * @expectedException Exception
     * @expectedExceptionMessage Exit Called: Migration Finished
     * @large
     */
    function testMigrateDatabase(){
        global $config,$test_path;
        $config['DB_DataObject']=
            array(
                "database" => "mysql://test:bhSTGCsFY32ApKeF@localhost/test_ons_common",
                "schema_location" => buildPath($test_path,"resources"),
            );
        $this->dropTables();
        MigrateDatabase("test");
    }

    /**
     * @depends testMigrateDatabase
     * @expectedException Exception
     * @expectedExceptionMessage Exit Called: Database is currently at version 2
     * @large
     */
    function testMigrateDatabaseNothingToDo(){
        global $config,$test_path;
        $config['DB_DataObject']=
            array(
                "database" => "mysql://test:bhSTGCsFY32ApKeF@localhost/test_ons_common",
                "schema_location" => buildPath($test_path,"resources"),
            );
        MigrateDatabase("test");
    }

    /**
     * @depends testSplitDataObjectConfig
     */
    function testSetupDB(){ 
        global $config,$db;

        $config=null;
        $db=null;

        setupDB(dirname(dirname(__FILE__))."/resources", "build.ini", false);
        $this->assertNotNull($db);
    }

    /**
     * @depends testSplitDataObjectConfig
     */
    function testDefaultSetupDB(){
        global $config,$db;

        $config=null;
        $db=null;

        setupDB(dirname(dirname(__FILE__))."/resources");
        $this->assertNotNull($db);
    }
}
?>
