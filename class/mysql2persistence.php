<?php
/*
 * File mysql2per.php
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
//TODO:any generic code for mysql2persistence.php goes here


if (class_exists('gtk',false)) {
	//TODO:any gtk specific code for mysql2persistence.php goes here
} else {
	//TODO:any web specific code for mysql2persistence.php goes here
}



//** Eclipse Debug Code **************************
if (strtolower(str_replace("/","\\",__FILE__))==strtolower(str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"]))){
	if (class_exists('gtk',false)) {
		//TODO:any gtk specific code for mysql2persistence.php goes here
	} else {
		//TODO:any web specific code for mysql2persistence.php goes here
	}

}
//************************************************
debug_error_log("Exit ".__FILE__);
?>