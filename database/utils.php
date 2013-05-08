<?php
/*
 * File database.php
 * Created on 25 Aug 2010 by nick
 * email php@oldnicksoftware.co.uk
 *
 *
 * Copyright 2010 ONS
 *
 */
 if (!defined("__ONS_COMMON__"))
    include_once('ons_common.php');
 debug_error_log("Enter ".__FILE__);
//************************************************
 	require_once("DB/DataObject/FormBuilder/QuickForm.php"); 
 	 	
 	function setupDB($root_path,$do_ini,$debug){
 		global $config; 		
 		
 		$config = parse_ini_file(buildpath($root_path,"database",$do_ini), true);
		if ($debug) krumo($config);
 		if (isset($GLOBALS['TESTMODE'])){
 			$prefix="test_";
 			if ($GLOBALS['TESTMODE']=="adhoc")
 				$prefix.="adhoc_";
 			 				
	 		$db_name=split("/",$config['DB_DataObject']['database']);
	 		
	 		$name=$db_name[count($db_name)-1];
	 		$db_name[count($db_name)-1]=$prefix.$name;
	 		
	 		$config['DB_DataObject']['database']=join("/",$db_name);
	 		//Now need to 'copy' the schema files as they have the wrong name :(
	 		
	 		$d = dir($config['DB_DataObject']['schema_location']);
	 		while (false !== ($target = $d->read())) {
	 			if (strpos($target,".ini") and substr($target,0,5)!="test_"){
	 				$link=str_replace($name, $prefix.$name, $target);
	 				if ($link<>$arget){
		 				@unlink(buildpath($d->path,$link));
		 				link(buildpath($d->path,$target),buildpath($d->path,$link));
	 				}
	 			}
	 		}
 		}
 		
 		if ($debug) krumo($config);
 		if ($debug) print(__FILE__."(".__LINE__.")<br/>\n");
 		
 		foreach($config as $class=>$values) {
 			$options = &PEAR::getStaticProperty($class,'options');
 			$options = $values;
 		}
 		if ($debug) print(__FILE__."(".__LINE__.")<br/>\n");
 		
 		PEARError($db = MDB2::connect($config['DB_DataObject']['database']),"Early out");
 		//$db->setFetchMode(DB_FETCHMODE_ASSOC);
 		$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
 		set_time_limit(0);
 		DB_DataObject::debugLevel(5);
 		if ($debug)	showload("t_dimension"); 		
 		if ($debug) print(__FILE__."(".__LINE__.")<br/>\n");
 		
 		DB_DataObject::debugLevel($debug?5:0);
 		
 		if ($debug) krumo($db);

 		return $db;
 	}
 	
 	function Safe_DataObject_factory($table){
 		/*
 		 * I  have a problem on Linux as I like to Camel Case my Table Names,
 		* This wraps then safely and removes the issue
 		*/
 		$table=ucfirst(strtolower($table));
 		return PEARError(DB_DataObject::factory($table));
 	}
 	
	function field($data){
		if (is_numeric($data)) return $data;
		if (is_string($data)) return "'$data'";
		return $data;
	}
 
	function execute($template,$args=array()){
		global $db;
		$sql="";
		if (count($args)>0){
			$asql=split("\?",$template);
			for ($i=0;$i<count($args);$i++){
				$sql.=$asql[$i].field($args[$i]);
			}
			$sql.=$asql[$i];
		}	
		else $sql=$template;
		print_line($sql);
		PEARError($db->query($sql));
	}
 
 	function executeMultiple($sql,$values){
 		foreach ($values as $args){
 			foreach ($sql as $qry){
 				execute($qry,$args);
 			}
 		}
 	}
 
 	function getTableList(){
 		global $config;
  	} 
 
  	function doClassName($table){
  		$options =&PEAR::getStaticProperty("DB_DataObject",'options');
  		return (isset($options['class_prefix'])?$options['class_prefix']:'').$table;
  	}
  	
	function updateUUID($table=""){
		global $config;
		global $db;
		
		if ($table==""){
			foreach(getTableList() as $key)
	        {
	            if (strpos($key,"key") === false and strpos($key,"default") === false){
	            	updateUUID($key);
	            }
	        }
	    }
		else {
	   		$sql[]="update $table set ID=? where ID='?'";	            	
			    		
			foreach(getTableList() as $key)
	        {
	            if (strpos($key,"key") === false and strpos($key,"default") === false){
	            	$do=Safe_DataObject_factory($key);
	            	//We have an issue with case
	            	if (array_key_exists(strtolower($table."ID"),array_change_key_case($do->table()))){
	            		$sql[]="update $key set ".$table."ID=? where ".$table."ID='?'";	            	
	            	}            	
	            }
	        }
	        $do=Safe_DataObject_factory($table);
	        $do->whereAdd("length(id)<30");
	        $do->find();
	        $actions=array();
	        while($do->fetch()){
	        	$newid=createUUID();
	        	$actions[]=array($newid,$do->ID);        	
	        }
	        executeMultiple($sql,$actions);        
		}	     
	}
 
	function truncateTable($table){
		global $db;
		$db->exec("SET FOREIGN_KEY_CHECKS = 0; -- Disable foreign key checking.");
		$db->exec("truncate table $table");
		$db->exec("SET FOREIGN_KEY_CHECKS = 1; -- Enable foreign key checking.");
	}
	
    function resetDB(){
        global $config;
        global $root_path;
        global $db;
        
        foreach(array_keys(parse_ini_file(buildPath($config['DB_DataObject']['schema_location'],basename($config['DB_DataObject']['database']).".ini"),true)) as $key)
        {
            if (strpos($key,"key") === false){
                truncateTable($key);
            }
        }
    }

    function reloadDB($script){
    	global $root_path,$do_ini;
    	
    	$work=split(";\r",file_get_contents($script));
		foreach($work as $sql){ 	
		 	if (trim($sql)<>""){
		 		debug_print($sql);
		 		query("$sql;"); 		
		 		print "<hr/>";
			}
		}
		print "<pre>";
		passthru('C:\xampp\php\php.exe C:\xampp\php\PEAR\DB\DataObject\createTables.php "'.buildpath($root_path,"database",$do_ini).'"');
		print "</pre>";		
    }

    $sl_count=0;
    function showTable($data,$key="",$value=""){
    	showLoad($data,$key,$value);
    }
    function showLoad($data,$key="",$value=""){
    	global $sl_count;
    	//  DB_DataObject::debugLevel(5);
    	if (is_string($data)){
    		showLoad(Safe_DataObject_factory($data),$key,$value);
    	}
    	else {
    		$sl_count++;
    		// Instantiate the DataGrid
    		krumo($data);
    		$datagrid = new Structures_DataGrid();
    		$datagrid->setRequestPrefix("sl_$sl_count");
    
    		if ($key<>"" and $value<>""){
    			$data->$key=$value;
    			$data->find();
    		}
    		// Bind the DataSource container
    		PEARError($datagrid->bind($data,array("link_level"=>1)));
    		print "<h2>".get_class($data)."</h2>\n";
    		// Print the DataGrid with the default renderer (HTML Table)
    		PEARError($datagrid->render());
    		print "<hr/>";
    	}
    }
    
    function showForm($data){
    	if (is_string($data)){
    		showForm(Safe_DataObject_factory($data));
    	}
    	else {
    		$fg =& DB_DataObject_FormBuilder::create($data);
    		$form =& $fg->getForm();
    		$form->display();
    	}
    }
    
    
//** Eclipse Debug Code **************************
if (str_replace("/","\\",__FILE__)==str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"])){
    if (class_exists('gtk',false)) {
        print($_SERVER["SCRIPT_FILENAME"]."\n\r");
        //TODO:any gtk specific code for database.php goes here
    } else {
        print("<h1 align='center'>".$_SERVER["SCRIPT_FILENAME"]."</h1>");
        //TODO:any web specific code for database.php goes here
    }
    //TODO:any generic code for database.php goes here
}
//************************************************
debug_error_log("Exit ".__FILE__);
?>
