<?php
/*
 * File do2model.php
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
//TODO:any generic code for do2model.php goes here
class do2sencha extends DB_DataObject {
    protected $map=array();
    protected $tables=array();
    protected $persited=0;
    protected $sync=0;

    protected static $instance;

    // A private constructor; prevents direct creation of object
    protected function __construct()
    {
    }

    static function addTable($table){
        do2sencha::singleton()->tables[]=$table;
    }

    static function getSencha(){
        $tables=do2Sencha::singleton()->tables;
        if (count($tables)==0) $tables=array_keys(parse_ini_file(buildPath($config['DB_DataObject']['schema_location'],basename($config['DB_DataObject']['database']).".ini"),tue));
        foreach($tables as $key)
        {
            /**/
            if (strpos($key,"key") === false){
                $table="app/models/auto/".$key.".js";
                HTML5::getmanifest()->file($table,false);
                HTML5::Script(array("type"=>"text/javascript","src"=>$table));

                $table="app/stores/auto/".$key.".js";
                HTML5::getmanifest()->file($table,false);
                HTML5::Script(array("type"=>"text/javascript","src"=>$table));

                $table="app/forms/auto/".$key.".js";
                HTML5::getmanifest()->file($table,false);
                HTML5::Script(array("type"=>"text/javascript","src"=>$table));
            }
            /**/
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

    protected function fieldType($type){
        $sql="UNKNOWN (".$type.")";
             if (DB_DATAOBJECT_INT            & $type) $sql='int';
        else if (DB_DATAOBJECT_STR            & $type) $sql='string';
        else if (DB_DATAOBJECT_DATE           & $type) $sql='date';
        else if (DB_DATAOBJECT_TIME           & $type) $sql='date';
        else if (DB_DATAOBJECT_BOOL           & $type) $sql='boolean';
        else if (DB_DATAOBJECT_TXT            & $type) $sql='string';
        else if (DB_DATAOBJECT_BLOB           & $type) $sql='auto';
        else if (DB_DATAOBJECT_MYSQLTIMESTAMP & $type) $sql="int";
        return $sql;
    }

    protected function formFieldType($type){
        $sql="UNKNOWN (".$type.")";
             if (DB_DATAOBJECT_INT            & $type) $sql='integer';
        else if (DB_DATAOBJECT_STR            & $type) $sql='string';
        else if (DB_DATAOBJECT_DATE           & $type) $sql='date';
        else if (DB_DATAOBJECT_TIME           & $type) $sql='date';
        else if (DB_DATAOBJECT_BOOL           & $type) $sql='boolean';
        else if (DB_DATAOBJECT_TXT            & $type) $sql='string';
        else if (DB_DATAOBJECT_BLOB           & $type) $sql='auto';
        else if (DB_DATAOBJECT_MYSQLTIMESTAMP & $type) $sql="int";
        return $sql;
    }


    function json(){
   		$table=$this->table();
        $ret=array();
   		foreach($table as $name=>$type){
   			if (DB_DATAOBJECT_MYSQLTIMESTAMP & $type){
   				//print_pre($table);
   				$dt=new DateTime($this->$name);
   				$ret[$name]=intval($dt->format('U'));
   			}
   			else $ret[$name]=$this->$name;
   		}
        return $ret;
    }

    function getData($since,$limit){
            $msg="";
            $msg="REST request for ".$this->tableName()." at ".date("Y-m-d H:i:s",$since);
            $msg.=" Results =".$this->find();
            debug_error_log($msg);
            //$ret["now"]=intval(date("U"));
            //$ret['updates']=array();
            while ($this->fetch()){
                $ret[]=$this->json();
            }
            //$ret['status']='ok';
            return $ret;
    }

    public static function getTable($table){
        if (!isset(do2sencha::singleton()->tables[$table])){
            do2sencha::singleton()->tables[$table]=Safe_DataObject_factory($table);
        }
        return do2sencha::singleton()->tables[$table];
    }

    function getForm(){
   		$fields=$this->table();
   		$sql="";

   		$sql.="/*****".str_pad("*",60,"*")."*\\\n";
   		$sql.="  Auto Generated Form \n";
   		$sql.="  Table : ".$this->tableName()."\n";
   		$sql.="  Fields :\n";
   		foreach($fields as $field=>$type){
   			$sql.="    [$field]=>".$this->formFieldType($type)."\n";
   		}
        if (count($links)) {
            $sql.="  Links :\n";
            foreach($links as $field=>$link){
   				$sql.="    [$field]=>$link\n";
   			}
   		}
   		$sql.="\\*****".str_pad("*",60,"*")."*/\n";
   		/** /
   		$sql.="Ext.regModel('".$this->tableName()."', {\n";
        $sql.="	fields: [\n";
        foreach($fields as $name=>$type){
            $sql.="\t\t{name :'$name', type:'".$this->formFieldType($type)."'},\n";
        }
        $sql=substr($sql,0,-2);
        $sql.="\n\t]\n";
        $sql.="\n});\n";
        if (count($links)){
            foreach($links as $field=>$link){
                list($to_table,$to_field)=split(":",$link);
                $sql.=do2model::getTable($to_table)->modelTable().".hasMany('".$this->tableName()."s',".$this->modelTable().",'$field')\n";
            }
        }
        /**/
        return $sql;
    }

    function getStore(){
   		$sql="";

   		$sql.="/*****".str_pad("*",60,"*")."*\\\n";
   		$sql.="  Auto Generated Store \n";
   		$sql.="  Table : ".$this->tableName()."\n";
        $sql.="\\*****".str_pad("*",60,"*")."*/\n";
        $sql.="Ext.regStore('".$this->tableName()."', {\n";
        $sql.="    model: '".$this->tableName()."',\n";
        $sql.="    proxy: {\n";
        //$sql.="        autoLoad: true,\n";
        $sql.="        type: 'rest',\n";
        $sql.="        url : 'rest/".$this->tableName()."'\n";
        //$sql.="        type: 'ajax',\n";
        //$sql.="        url : 'app/data/".$this->tableName().".json'\n";
        $sql.="    },\n";
        $sql.=$this->sorters();
        $sql.=$this->groupstring();
        $sql.=$this->filters();
        $sql.="    autoLoad: true\n";
        $sql.="});";
   		return $sql;
    }

    function sorters(){}
    function groupstring(){}
    function filters(){}

    function getModel(){
   		$fields=$this->table();
   		$sql="";

   		$sql.="/*****".str_pad("*",60,"*")."*\\\n";
   		$sql.="  Auto Generated Model \n";
   		$sql.="  Table : ".$this->tableName()."\n";
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
   		$sql.="Ext.regModel('".$this->tableName()."', {\n";
        $sql.="	fields: [\n";
        foreach($fields as $name=>$type){
            $sql.="\t\t{name :'$name', type:'".$this->fieldType($type)."'},\n";
        }
        $sql=substr($sql,0,-2);
        $sql.="\n\t]\n";
        $sql.="\n});\n";
        if (count($links)){
            foreach($links as $field=>$link){
                list($to_table,$to_field)=split(":",$link);
                $sql.=do2model::getTable($to_table)->modelTable().".hasMany('".$this->tableName()."s',".$this->modelTable().",'$field')\n";
            }
        }
        return $sql;
    }
}

if (class_exists('gtk',false)) {
    //TODO:any gtk specific code for do2model.php goes here
} else {
    //TODO:any web specific code for do2model.php goes here
}



//** Eclipse Debug Code **************************
if (strtolower(str_replace("/","\\",__FILE__))==strtolower(str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"]))){
    if (class_exists('gtk',false)) {
        //TODO:any gtk specific code for do2model.php goes here
    } else {
        //TODO:any web specific code for do2model.php goes here
    }

}
//************************************************
debug_error_log("Exit ".__FILE__);
?>