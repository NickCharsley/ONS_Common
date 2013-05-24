<?php
/**
 * @codeCoverageIgnore
 */
/*
 * File common.php
 * Created on Sep 17, 2006 by N.A.Charsley
 * email php@oldnicksoftware.co.uk
 *
 *
 * Copyright 2006 ONS
 *
 */
 if (!defined("__COMMON__"))
 	include_once(dirname(__FILE__).'/../ons_common.php');
 error_log("Enter ".__FILE__);
//************************************************

//DONE: It's just a stub file'

//** Eclipse Debug Code **************************
if (strpos($_SERVER['SCRIPT_NAME'],"common.php")>0){
	print("<h1 align='center'>common.php</h1>");
	phpinfo();
}
//************************************************
error_log("Exit ".__FILE__);
?>
