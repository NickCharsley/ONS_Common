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
 error_log("Enter ".__FILE__);
//************************************************
require_once("DB/DataObject/FormBuilder/QuickForm.php"); 

function setupDB($root_path,$do_ini="build.ini",$debug=false){
    global $config,$db;

    $target="production";
    $config = parse_ini_file(buildpath($root_path,"database",$do_ini), true);
    //We may override this from Globals
    foreach(array_keys($config['DB_DataObject']) as $key){
        if (isset($GLOBALS['DB_DataObject_config_'.$key])){
            $config['DB_DataObject'][$key]=$GLOBALS['DB_DataObject_config_'.$key];
        }
    }
        
    if ($debug) krumo($config);
    if (isset($GLOBALS['TESTMODE'])){
        $prefix="test_";        
        if ($GLOBALS['TESTMODE'] == "adhoc") {
            $prefix.="adhoc_";
        }
        $target=substr($prefix,0,-1);
        $db_name=split("/",$config['DB_DataObject']['database']);

        $name=$db_name[count($db_name)-1];
        $db_name[count($db_name)-1]=$prefix.$name;

        $config['DB_DataObject']['database']=join("/",$db_name);
        //if we have a global DNS defined need to update it as well!!!
        //This is done in phpunit for testing look in test dir!!!
        //Now need to 'copy' the schema files as they have the wrong name :(
        $dns=SplitDataObjectConfig();
        $d = dir($dns['schema_location']);
            
        while (false !== ($target = $d->read())) {
            if (strpos($target,".ini") and substr($target,0,5)!="test_"){
                $link=str_replace($name, $prefix.$name, $target);
                if ($link<>$target){
                    @unlink(buildpath($d->path,$link));
                    //print("link(".buildpath($d->path,$target).",".buildpath($d->path,$link).")\n");
                    copy(buildpath($d->path,$target),buildpath($d->path,$link));
                }
            }
        }
    }

    $dns=SplitDataObjectConfig();
    $config['DB_DataObject']['schema_location']=$dns['schema_location'];
    $config['DB_DataObject']['class_location']=$dns['class_location'];

    if ($debug) krumo($config);
    if ($debug) print(__FILE__."(".__LINE__.")<br/>\n");

    foreach($config as $class=>$values) {
        $options = &PEAR::getStaticProperty($class,'options');
        $options = $values;
    }
    if ($debug) print(__FILE__."(".__LINE__.")<br/>\n");

    if ($debug) print_r($config['DB_DataObject']);
    
    PEARError($db = MDB2::connect($config['DB_DataObject']['database']),"Early out");
    //$db->setFetchMode(DB_FETCHMODE_ASSOC);
    $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
    set_time_limit(0);
    DB_DataObject::debugLevel(5);
    if ($debug)	showload("Exhibition"); 		
    if ($debug) print(__FILE__."(".__LINE__.")<br/>\n");

    DB_DataObject::debugLevel($debug?5:0);

    if ($debug) krumo($db);    
    $tmp=tempnam(buildpath($root_path,"database"), "ini").".ini";
    //need to write out tmp.ini               
    write_ini_file($tmp, $config);
    try {
        MigrateDatabase($target,$tmp);
    } catch(Exception $e){
        //Should really Set Exception level and 
    }
    return $db;
}

    /*
     * Really only split DNS :)
     */
    function SplitDataObjectConfig(){
        global $config;
        $dbc=$config['DB_DataObject'];
        list($dbc['driver'],$dbc['user'],$dbc['password'],$dbc['host'],$dbc['database'])=preg_split('`://|[:@/]`',$dbc['database']);
        ksort($dbc);
        //If $GLOBALS is set we should validate and die if different database;
        if (isset($GLOBALS["DB_DSN"])){
                if ($GLOBALS["DB_DSN"]!=$dbc['driver'].":host=".$dbc['host'].";dbname=".$dbc['database'])
                    throw new Exception("DSN Mismatch!\n\tGlobals   ={$GLOBALS["DB_DSN"]},\n\tDataObject=".$dbc['driver'].":host=".$dbc['host'].";dbname=".$dbc['database']."\n");                        
        }
	if (isset($GLOBALS["DB_DBNAME"])){
            if ($GLOBALS["DB_DBNAME"]!=$dbc['database'])
                throw new Exception("Database Name Mismatch!\n\tGlobals={$GLOBALS["DB_DBNAME"]},\n\tDataObject={$dbc['database']}\n");                        
        }
        //Need to do Variable Replacement
        foreach ($dbc as $key=>$value){
            if (!(strpos($value,"{")===false)){
                $tokens=preg_split('|[{}]|',$value);
                for($index=0;$index<count($tokens);$index++){
                    if (substr($tokens[$index],0,1)=="$"){
                        $tokens[$index]=$GLOBALS[substr($tokens[$index],1)];
                    }
                }
                $dbc[$key]=join('',$tokens);
            }
        }
        
        return $dbc;
    }
    
    function MigrateDatabase($target="Development",$iniFile=""){        
        global $config,$ips,$fps;
        //Need to add path to phpdbmigrate so we can use it
        ini_set("include_path",ini_get("include_path")
                            /*phpdbmigrate*/                            
                            .$ips.dirname(dirname(__FILE__)).$fps."phpdbmigrate"
                );
        $dns=SplitDataObjectConfig();
        
        $phpdbmigrate= 
            array(
                $target => array(
                    "db" => $dns,
                    ),
                "migrations_path" => buildpath($dns['schema_location'],'migrations'),
                "return_type" => "\n",
                );
        $lib = new PHPDbMigrate($phpdbmigrate);
        try {
            $lib->run(NULL,$target);
        } catch (Exception $e) {
            if ($lib->changed){
                print_pre("Changed {$e->getMessage()}\n");
                reCreateTables($iniFile);
            }
            if ($e->getCode()) { 
                throw new PEAR_Exception($e->getMessage(), $e->getCode()); 
            } 
            throw new PEAR_Exception($e->getMessage()); 
        }
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
            else $sql=trim($template);
            if ($sql=="") return;
            //error_log($sql);
            PEARError($db->query($sql),$sql);
    }

    function executeMultiple($sql,$values=array(array())){
            foreach ($values as $args){
                    foreach ($sql as $qry){
                            execute($qry,$args);
                    }
            }
    }

    function executeScript($sql){
        executeMultiple(split(";",$sql));
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
/*            
            $db->exec("SET FOREIGN_KEY_CHECKS = 0; -- Disable foreign key checking.");
            try {
                $db->exec("truncate table $table");
            } catch (Exception $e){
                error_log($e->getMessage());
            }
            $db->exec("SET FOREIGN_KEY_CHECKS = 1; -- Enable foreign key checking.");
 */
            $do=  Safe_DataObject_factory($table);
            $do->find();
            while ($do->fetch()){
                $do->delete();
            }
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

function reCreateTables($iniFile){
    global $php_exe,$createTables_php;
    echo "Reload DB<br/>\n";
    $cmd= $php_exe." $createTables_php \"$iniFile\"";
    echo "May need to $cmd<br/>\n";
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
    reCreateTables($do_ini);
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
