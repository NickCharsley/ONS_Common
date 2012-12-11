<?php
/*
 * File dbRoot.php
 * Created on 17 Oct 2011 by Nick
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
//TODO:any generic code for dbRoot.php goes here
class dbRoot extends DB_DataObject {	
}

if (class_exists('gtk',false)) {
	//TODO:any gtk specific code for dbRoot.php goes here
} else {
	//TODO:any web specific code for dbRoot.php goes here
}



//** Eclipse Debug Code **************************
if (strtolower(str_replace("/","\\",__FILE__))==strtolower(str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"]))){
	if (class_exists('gtk',false)) {
		//TODO:any gtk specific code for dbRoot.php goes here
	} else {
		//TODO:any web specific code for dbRoot.php goes here
	}

}
//************************************************
debug_error_log("Exit ".__FILE__);
?>