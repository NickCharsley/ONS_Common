<?php

 if (!defined("__ONS_COMMON__"))
    include_once('ons_common.php');

 include_once("database/utils.php");
 
class UtilsTest extends PHPUnit_Framework_TestCase
{    

    function testSplitDataObjectConfig(){
        global $config;
        $config['DB_DataObject']=
                array(
                    "database" => "mysql://showmanager:AA5md9qXNdBSKMVp@localhost/showmanager",
                    "schema_location" => "C:\\users\\nick\\workspace\\ons_Common\\test\\resources\\database\\schema",
                    "class_location" => "C:\\users\\nick\\workspace\\ons_Common\\test\\resources\\database",
                    "class_prefix" => "do",
                    "db_driver"	=> "MDB2",
                    "build_views"=> 1,
                    "generator_var_keyword"=> "public",
                    "extends"=> "dbRoot",
                    "extends_location" => "dbRoot.php"
                );
        $this->assertEquals(array(
                    "build_views"=> 1,
                    "class_location" => "C:\\users\\nick\\workspace\\ons_Common\\test\\resources\\database",
                    "class_prefix" => "do",
                    "database"=>"showmanager",
                    "db_driver"	=> "MDB2",
                    "driver" => "mysql",
                    "extends"=> "dbRoot",
                    "extends_location" => "dbRoot.php",
                    "generator_var_keyword"=> "public",
                    "host"=>"localhost",
                    "password"=>"AA5md9qXNdBSKMVp",
                    "schema_location" => "C:\\users\\nick\\workspace\\ons_Common\\test\\resources\\database\\schema",
                    "user"=>"showmanager"
                ), SplitDataObjectConfig());
    }

    /**
     * @depends testSplitDataObjectConfig
     * @expectedException Exception
     * @expectedException Exception
     * @expectedExceptionMessage Exit Called: No updates to run
     */
    function testMigrateDatabaseNoDirectory(){
        global $config;
        $config['DB_DataObject']=
                array(
                    "database" => "mysql://test:sas0527@localhost/test_ons_common",
                    "schema_location" => "C:\\users\\nick\\workspace\\ons_Common\\test\\no directory",
                );
        //Need to drop all tables in test_ons_common;
        
        $options = array(
            'debug'       => 2,            
        );

        // uses MDB2::factory() to create the instance
        // and also attempts to connect to the host
        @$mdb2 =& MDB2::connect($config['DB_DataObject']['database'], $options);        
        @$mdb2->exec("drop table migration_info");
        @$mdb2->exec("drop table my_table");
        @$mdb2->exec("drop table my_other_table");
        
        MigrateDatabase("test");
    }   
    
    /**
     * @depends testMigrateDatabaseNoDirectory
     * @expectedException Exception
     * @expectedExceptionMessage Exit Called: Migration Finished
     */
    function testMigrateDatabase(){
        global $config;
        $config['DB_DataObject']=
                array(
                    "database" => "mysql://test:sas0527@localhost/test_ons_common",
                    "schema_location" => "C:\\users\\nick\\workspace\\ons_Common\\test\\resources",
                );        
        MigrateDatabase("test");
    }
    
    /**
     * @depends testSplitDataObjectConfig
     * @expectedException Exception
     * @expectedExceptionMessage Exit Called: Database is currently at version 2
     */
    function testMigrateDatabaseNothingToDo(){
        global $config;
        $config['DB_DataObject']=
                array(
                    "database" => "mysql://test:sas0527@localhost/test_ons_common",
                    "schema_location" => "C:\\users\\nick\\workspace\\ons_Common\\test\\resources",
                );
        MigrateDatabase("test");
    }
    
    /**
     * @depends testMigrateDatabase
     */
    function testSetupDB(){ 
        global $config,$db;
        
        $config=null;
        $db=null;
        
        setupDB(dirname(dirname(__FILE__))."/resources", "build.ini", false);
        $this->assertNotNull($db);
    }
    
    /**
     * @depends testMigrateDatabase
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
