<?php
/*
 * File testDOT
 * Created on  by nick
 * email php@oldnicksoftware.co.uk
 *
 *
 * Copyright  ONS
 *
 */
if (!defined("__COMMON__"))
 	include_once 'ons_common.php';
error_log("Enter ".__FILE__);
//************************************************

class JenkinsTest extends PHPUnit_Framework_TestCase
{

	/*
	 *
	 */
	function testJenkinsActive(){
		$this->assertEquals(true,true);
	}

}

//************************************************
error_log("Exit ".__FILE__);
?>

