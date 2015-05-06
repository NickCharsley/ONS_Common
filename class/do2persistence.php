<?php
/*
 * File do2persistence.php
 * Created on 26 Jun 2011 by Nick
 * email php@oldnicksoftware.co.uk
 *
 *
 * Copyright 2011 ONS
 *
 */
 if (!defined("__ONS_COMMON__"))
 	include_once('ons_common.php');
 debug_error_log("Enter ".__FILE__);
//************************************************
//TODO:any generic code for do2persistence.php goes here
class do2persistence extends DB_DataObject {
    protected $map=array();
    protected $tables=array();
    protected $persited=0;
    protected $sync=0;

    protected static $instance;

    // A private constructor; prevents direct creation of object
    protected function __construct()
    {
        global $_DB_DATAOBJECT;
        if (!array_key_exists($this->persistenceID(), $this->table())){
            //We don't have the field in the dataset so need to add it
            //Need to alter database
            query("ALTER TABLE `".$this->tableName()."` ADD `".$this->persistenceID()."` varchar(50) NOT NULL;");
            query("CREATE UNIQUE INDEX `".$this->tableName()."_idx".$this->persistenceID()."`  ON `".$this->tableName()."` (`".$this->persistenceID()."`);");
            //Need to alter ini file
            $options =&PEAR::getStaticProperty("DB_DataObject",'options');
            $schema=parse_ini_file(buildPath($options['schema_location'],basename($options['database']).".ini"),tue);
            $schema[$this->tableName()][$this->persistenceID()]=DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL + DB_DATAOBJECT_TXT;
            write_ini_file(buildPath($options['schema_location'],basename($options['database']).".ini"), $schema);
            unset($_DB_DATAOBJECT['INI'][$this->_database]);
            $this->databaseStructure();
            //Should now re-load schema
            //Need to alter class
        }
    }

    // The singleton method
    public static function singleton()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    public static function getTable($table){
        if (!isset(do2persistence::singleton()->tables[$table])){
            do2persistence::singleton()->tables[$table]=Safe_DataObject_factory($table);
        }
        return do2persistence::singleton()->tables[$table];
    }

    function persistenceTable(){
        return "p".$this->tableName();
    }

    protected function getSyncTables($tables=null){
        if ($tables==null) $tables=array();
        $links=$this->links();
   		if (count($links)){
   			foreach($links as $field=>$link){
   				list($to_table,$to_field)=split(":",$link);
   				if (array_search(strtolower($do_table), $tables)===false)
   					$tables=do2persistence::getTable($to_table)->getSyncTables($tables);
   			}
   		}
   		if (array_search(strtolower($this->tableName()), $tables)===false)
   			$tables[]=strtolower($this->tableName());
   		return $tables;
    }

    protected function getdoSync($success,$error){
        if ($success=="") $success="function() {console.log('Class ".$this->persistenceTable() .": Sync Ok!');}";
        if ($error=="")	$error="function() {console.log('Class ".$this->persistenceTable() .": Sync Error!');}";
        return $this->persistenceTable().".syncAll(persistence.sync.preferLocalConflictHandler, $success, $error);";
    }

    function doSync($class,$success="",$error=""){
        $tables=do2persistence::getTable($class)->getSyncTables();
        /**/
   		$sql="";
   		if (count($tables)==1){
   			$sql.="\t//Sync ".$tables[0]." only\n";
   			$sql.="\t".do2persistence::getTable($tables[0])->getdoSync($success,$error)."\n";
   		}
   		else for ($i=0;$i<count($tables);$i++){
   			if ($i==0){
   				$sql.="\t//Sync ".$tables[$i]." to ".$tables[$i+1]."\n";
   				$sql.="\t".do2persistence::getTable($tables[$i])->getdoSync($success."_".$tables[$i]."2".$tables[$i+1],"")."\n";
   			}
   			else if (($i+1)==count($tables)){
   				$sql.="\t//Sync ".$tables[$i]." only\n";
   				$fn ="function ".$success."_".$tables[$i-1]."2".$tables[$i]."(){\n";
   				$fn.="\t//Sync ".$tables[$i]." only\n";
   				$fn.="\t".do2persistence::getTable($tables[$i])->getdoSync($success,$error)."\n";
   				$fn.="}";
   				HTML5::jsFunction($fn);
   			}
   			else {
   				$sql.="\t//Then sync ".$tables[$i]." to ".$tables[$i+1]."\n";
   				$fn ="function ".$success."_".$tables[$i-1]."2".$tables[$i]."(){\n";
   				$fn.="\t//Then sync ".$tables[$i]." to ".$tables[$i+1]."\n";
   				$fn.="\t".do2persistence::getTable($tables[$i])->getdoSync($success."_".$tables[$i]."2".$tables[$i+1],"")."\n";
   				$fn.="}";
   				HTML5::jsFunction($fn);
   			}
   		}
        return "<script type='text/javascript'>\n".$sql."\n</script>";
    }

    protected function getsetupSync(){
        if ($this->sync){
            $sql="/**** ".str_pad("Request for sync setup of ".$this->persistenceTable()." Number ".$this->persited." ",60,"*")."*/\n";
            $this->sync++;
        } else {
            $links=$this->links();
            $sql="";

            if (count($links)){
                   foreach($links as $field=>$link){
                       list($to_table,$to_field)=split(":",$link);
                       $sql.=do2persistence::getTable($to_table)->getsetupSync();
                   }
               }
            $sql.=  $this->persistenceTable().".enableSync('rest/".$this->tableName()."/'+localStorage.computerid);\n";
            $this->sync++;
            return "<script type='text/javascript'>\n".$sql."\n</script>";
        }
    }

    function setupSync($class){
        return do2persistence::getTable($class)->getsetupSync();
    }

    protected function persistenceFields(){
        /*
         * By default we remove ID and _lastChange as they are internal to persistence
         * Obviously we store them in the DB but they get created automatically!
         *
         * We may have a diferent percistenceID which is used internaly but maps to ID!
         */
        $ret=$this->table();
        unset($ret['id']);
        unset($ret['ID']);
        unset($ret['_lastChange']);
        unset($ret[$this->persistenceID()]);
        /*
         * Also Need to remove any Links we are createing
         */
        foreach ($this->links() as $field=>$junk){
            unset($ret[$field]);
        }
        return $ret;
    }

    protected function persistenceKeys(){
        return $this->keys();
    }

    protected function fieldType($type){
        $sql="UNKNOWN (".$type.")";
             if (DB_DATAOBJECT_INT            & $type) $sql='INTEGER';
        else if (DB_DATAOBJECT_STR            & $type) $sql='TEXT';
        else if (DB_DATAOBJECT_DATE           & $type) $sql='REAL';
        else if (DB_DATAOBJECT_TIME           & $type) $sql='REAL';
        else if (DB_DATAOBJECT_BOOL           & $type) $sql='INTEGER';
        else if (DB_DATAOBJECT_TXT            & $type) $sql='TEXT';
        else if (DB_DATAOBJECT_BLOB           & $type) $sql='BLOB';
        else if (DB_DATAOBJECT_MYSQLTIMESTAMP & $type) $sql="MYSQLTIMESTAMP";
        return $sql;
    }

    public function persistence($type=""){
        if ($type==""){
            if (!$this->persited){
                $this->persited++;
                $fields=$this->persistenceFields();
                $keys=$this->persistenceKeys();
                $links=$this->links();
                $sql="";

                if (count($links)){
                    foreach($links as $field=>$link){
                        list($to_table,$to_field)=split(":",$link);
                        $sql.=do2persistence::getTable($to_table)->persistence();
                    }
                }
                $sql.="/*****".str_pad("*",60,"*")."*\\\n";
                $sql.="  Table : ".$this->tableName()." persisted as ".$this->persistenceTable()."\n";
                $sql.="  Fields :\n";
                foreach($fields as $field=>$type){
                    $sql.="    [$field]=>".$this->fieldType($type)."\n";
                }
                if (count($links)) {
                    $sql.="  Links :\n";
                    foreach($links as $field=>$link){
                        $sql.="    [$field]=>$link\n";
                    }
                }
                $sql.="\\*****".str_pad("*",60,"*")."*/\n";
                $sql.="var ".$this->persistenceTable()." = persistence.define('".$this->persistenceTable()."', {";
                foreach($fields as $name=>$type){
                    if (!(DB_DATAOBJECT_MYSQLTIMESTAMP & $type) and strtolower($name)<>"id"){
                        $sql.="$name: ";
                        $sql.='"'.$this->fieldType($type).'" ';
                        $sql.=",";
                    }
                }
                $sql=substr($sql,0,-2);
                $sql.="});\n";
                if (count($links)){
                    foreach($links as $field=>$link){
                        list($to_table,$to_field)=split(":",$link);
                        $sql.=do2persistence::getTable($to_table)->persistenceTable().".hasMany('".$this->tableName()."s',".$this->persistenceTable().",'$field')\n";
                    }
                }
                /** /
                if (count($keys)){
                    if (count($keys)>1 or strtolower($keys[0])<>"id"){
                        $sql.=$this->persistenceTable().".index([";
                        foreach($keys as $name){
                            if (strtolower($name)<>"id")
                                $sql.="'$name',";
                        }
                        $sql=substr($sql,0,-1);
                        $sql.="]);\n";
                    }
                }
                /**/
            } else {
                $sql="/**** ".str_pad("Request for creation of ".$this->persistenceTable()." Number ".$this->persited." ",60,"*")."*/\n";
                $this->persited++;
            }
            return "<script type='text/javascript'>\n".$sql."\n</script>";
        } else {
            return do2persistence::getTable($type)->persistence();
        }
    }

    function persistenceID(){
        $options =&PEAR::getStaticProperty("DB_DataObject",'options');
        return isset($options['persistenceID'])?$options['persistenceID']:'id';
    }

    function insert(){
        /*When we insert we always create a new ID if it is blank!*/
        $map=$this->fieldMap($this->persistenceID());
        if (!isset($this->$map) or $this->$map=='') $this->$map=createUUID();
        parent::insert();
    }

    function json(){
   		$table=$this->table();
        $ret=array();
   		foreach($table as $name=>$type){
   			if (DB_DATAOBJECT_MYSQLTIMESTAMP & $type){
   				$dt=new DateTime($this->$name);
   				$ret[$name]=intval($dt->format('U'));
   			}
   			else if (strtolower($name)=="id")
   				$ret['id']=$this->$name;
   			/*else if (DB_DATAOBJECT_BOOL & $type){
   				$ret[$name]=($this->$name?'true':'false');
   			}*/
   			else $ret[$name]=$this->$name;
   		}
        return $ret;
    }

    function getUpdateSince($since){
            $msg="";

            $this->whereAdd("_lastChange>='".date("Y-m-d H:i:s",$since)."'");
            $ret['then']=$_GET['since'];
            $msg="REST request for ".$this->tableName()." at ".date("Y-m-d H:i:s",$since);
            $msg.=" Results =".$this->find();
            debug_error_log($msg);
            $ret["now"]=intval(date("U"));
            $ret['updates']=array();
            while ($this->fetch()){
                $ret['updates'][]=$this->json();
            }
            $ret['status']='ok';
            return $ret;
    }

    protected function fieldMap($map){
        if (!isset($this->map[$map])){
            foreach($this->table() as $name=>$type){
                if (strtolower($map)==strtolower($name)){
                    $this->map[$map]=$name;
                    break;
                }
            }
        }
        return $this->map[$map];
    }

    function doUpdateSince($row){
        $keys=$this->keys();
        foreach ($keys as $key){
            $map=$this->fieldMap($key);
            $this->$map=$row[$key];
  		}
   		DB_DataObject::debugLevel(0);
   		$action=(($this->find())?'update':'insert');
   		//print_pre($row);
   		foreach($row as $field=>$value){
   			$map=$this->fieldMap($field);
   			$this->$map=$value;
   		}
   		$this->$action();
   		//print_pre($this);
   		debug_error_log("$action ".$this->tablename()." id=".$this->id);
   		//die;
    }
}

if (class_exists('gtk',false)) {
    //TODO:any gtk specific code for do2persistence.php goes here
} else {
    //TODO:any web specific code for do2persistence.php goes here
}



//** Eclipse Debug Code **************************
if (strtolower(str_replace("/","\\",__FILE__))==strtolower(str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"]))){
    if (class_exists('gtk',false)) {
        //TODO:any gtk specific code for do2persistence.php goes here
    } else {
        //TODO:any web specific code for do2persistence.php goes here
    }

}
//************************************************
debug_error_log("Exit ".__FILE__);
?>