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
 
    function resetDB(){
        global $config;
        global $root_path;
        global $db;
        
        foreach(array_keys(parse_ini_file(buildPath($config['DB_DataObject']['schema_location'],basename($config['DB_DataObject']['database']).".ini"),tue)) as $key)
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
    
    		if ($key<>"" and $value<>"")
    			$data->$key=$value;
    
    		// Bind the DataSource container
    		PEARError($datagrid->bind($data));
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
