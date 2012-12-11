<?php
/*
 * File page_sencha.php
 * Created on 4 Jan 2012 by Nick
 * email php@oldnicksoftware.co.uk
 *
 *
 * Copyright 2012 ONS
 *
 */
 if (!defined("__COMMON__"))
 	include_once('ons_common.php');
 debug_error_log("Enter ".__FILE__);
//************************************************
//TODO:any generic code for page_sencha.php goes here


if (class_exists('gtk',false)) {
	//TODO:any gtk specific code for page_sencha.php goes here
} else if (ons_InDrupal()) {
//TODO:any Drupal specific code for page_sencha.php goes here
} else {
	//TODO:any web specific code for page_sencha.php goes here
}

class page_sencha extends page {
	function __construct($id,$title="",$appDir="app"){
		sencha::start();
		parent::__construct($id,$title);
	}
	
	function addAppDir($appDir="app"){
		global $root_path;
		$fullDir=buildpath($root_path,$appDir);
		$dir=new RecursiveDirectoryIterator($fullDir);
        foreach(new RecursiveIteratorIterator($dir) as $file){
            if ($file->Isfile() and substr($file->getFilename(),0,1)!='.' and substr($file->getFilename(),-3)==".js") {
        		HTML5::Script(array("type"=>"text/javascript","src"=>$this->manifest->file($appDir.str_replace("\\","/",substr($file,strlen($fullDir))))));        
            }
        }
        /**/
		global $db;
		if (isset($db)){
			do2sencha::getSencha();				
		}
		/**/        
	}
	
	function render(){
    	//NOP
    }
    
	public function css($inline=true){
    }
    
    public function js($inline=true){
    }    
}

//** Eclipse Debug Code **************************
if (strtolower(str_replace("/","\\",__FILE__))==strtolower(str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"]))){
	if (class_exists('gtk',false)) {
		//TODO:any gtk specific code for page_sencha.php goes here
	} else {
		//TODO:any web specific code for page_sencha.php goes here
	}

}
//************************************************
debug_error_log("Exit ".__FILE__);
?>