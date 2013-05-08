<?php

abstract class ONS_Tests_DatabaseTestCase extends PHPUnit_Extensions_Database_TestCase
{
    // only instantiate pdo once for test clean-up/fixture load
    static private $pdo = null;
    
    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    private $conn = null;
    private $conn_sync = null;

	//ONS Helpers, only expect one to be set 
    public $tables=array();
	abstract public function FileName();
	
	/**
     * Performs operation returned by getSetUpOperation().
     */
    protected function setUp()
    {
    	error_log("Run Setup:".__FILE__);
    	
    	$this->getConnection()->getConnection()->query("SET FOREIGN_KEY_CHECKS = 0; -- Disable foreign key checking.");
		parent::setUp();
		$this->getConnection()->getConnection()->query("SET FOREIGN_KEY_CHECKS = 1; -- Enable foreign key checking.");		        
	}

    final public function getConnection()
    {
		global $db;
    	try {
	    	@DB_DataObject::debugLevel($GLOBALS['DB_DEBUG']);
			//
			if (!strpos($GLOBALS['DB_DSN'],'test_')) {
				print_line("phpUnit.xml DSN is not pointing at a test database!");
				dieHere();
				return;
			}
    		if (strpos($GLOBALS['DB_DBNAME'],'test_')===false) {
				print_line("phpUnit.xml DBNAME is not pointing at a test database!");
				dieHere();
				return;
			}
			if (strpos($db->database_name,'test_')===false) {
				print_line("do_XXXX.ini DSN is not pointing at a test database!");
				dieHere();
				return;
			}

	        if ($this->conn === null) {
	            if (self::$pdo == null) {
	                self::$pdo = new PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
					self::$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	            }
				
	            $this->conn= $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
	            
	            $this->getConnection()->getConnection()->query("SET FOREIGN_KEY_CHECKS = 0; -- Disable foreign key checking.");
	            foreach ($this->tables as $table){
	            	$this->sync($table);
	            }
	            $this->getConnection()->getConnection()->query("SET FOREIGN_KEY_CHECKS = 1; -- Enable foreign key checking.");
	        }
	        return $this->conn;
    		
    	} catch(PDOException $e) {  
			print_line($e->getMessage());
			dieHere();				
		}  
    }
    
	private function sync($table){
		$sql="";
		$exec="";
		try {
			$STH = $this->getConnection()->getConnection()->query("show create table {$GLOBALS['DB_SYNCNAME']}.$table");		
			# setting the fetch mode
			$STH->setFetchMode(PDO::FETCH_ASSOC);
			
			while($row = $STH->fetch()) {
				$tableset=isset($row['Create Table']);
								
				$sql="use {$GLOBALS['DB_DBNAME']};\nDrop ".($tableset?"Table ":"View ")." $table;\n";
				error_log("Sync DB ($table) $sql");
				@$this->getConnection()->getConnection()->query($sql);
				
				$sql="use {$GLOBALS['DB_DBNAME']};\n".$row['Create '.($tableset?"Table":"View")];
				if (!$tableset){
					$sql="CREATE ".substr($sql,strpos($sql,"VIEW"));
					$sql=str_replace("{$GLOBALS['DB_SYNCNAME']}", "{$GLOBALS['DB_DBNAME']}", $sql);
				}
				error_log("Sync DB ($table) $sql");
				@$this->getConnection()->getConnection()->query($sql);
			}	
		} catch(PDOException $e) {
			print_line("While trying to Sync $table");
			print_line($sql);
			print_line($e->getMessage());			
			dieHere();
		}			
	}	
		
	public function getDataSet()
    {   	
    	//Now load Dataset
    	global $test_path;    	
    	$file=(file_exists(buildPath($test_path,"testData",$this->FileName().".xml")))?$this->FileName():$this->tables[0];
    	
        return $this->createXMLDataSet($this->xmlData($file));
    }
	
	protected function xmlData($file){
		global $test_path;
		return buildPath($test_path,"testData","$file.xml");
	}
	
}

?>