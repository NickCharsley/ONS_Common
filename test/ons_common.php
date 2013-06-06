<?php
/*
 * File ons_common.php
 * Created on Jun 1, 2006 by N.A.Charsley
 * email php@oldnicksoftware.co.uk
 *
 *
 * Copyright 2006 ONS
 *
 */

define("__COMMON__",1);
if (!isset($GLOBALS['TESTMODE'])){
    if (!in_array('ob_gzhandler', ob_list_handlers())) {
        ob_start('ob_gzhandler');
    } else {
        ob_start();
    }
}

error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

/************************************************************\
*   Setup
\************************************************************/

global $web,$root,$root_path,$test_path,$ips,$fps,$db,$mobile,$local,$common_path,$system,$do_ini,$term;

	function loadProperties(){
		global $show_properties;
		global $TESTMODE;

		@$props[]=strtolower(PHP_OS);
		@$props[]=strtolower($_SERVER["COMPUTERNAME"]);
		@$props[]=strtolower(getenv("COMPUTERNAME"));
		@$doms=preg_split("#[.]+#", strtolower($_SERVER["SERVER_NAME"]));
		if (count($doms)){
			$url=$doms[0];
			$props[]=$url;
			for($index=1;$index<count($doms);$index++){
				$url.=".{$doms[$index]}";
				$props[]=$url;
			}
		}
		@$props[]=strtolower($_SERVER["TERM"]);
		@list($props[])=preg_split("#[./ (]+#", strtolower($_SERVER["SERVER_SOFTWARE"]),2);

		$ini="";
		$filename=dirname(__FILE__);
                $props[]="test";
                $filename=dirname($filename);


		//Local is last as it has to beable to overwrite other settings
		$props[]="local";

		$filename.="/properties";

		foreach($props as $prop)
			if ($prop<>"")
				@$ini.="\n".file_get_contents("$filename/$prop.properties");

		$vars=parse_ini_string($ini);
		foreach($vars as $var=>$values)
			$GLOBALS[$var]=$values;

		if ($show_properties){
			print ("<pre>\n");

			foreach($props as $prop)
				if ($prop<>"")
					print("$prop.properties\n");
			print("$ini\n");

			print_r($vars);

			print ("Listing of Expected Property Files\n");
			print ("</pre>");
		}
                if (isset($TESTMODE)){
                    foreach($props as $prop)
                        if ($prop<>"")
                            error_log("$prop.properties\n");
                    error_log("$ini\n");
                    error_log(print_r($vars,true));
                    error_log("Listing of Expected Property Files\n");                    
                }
		return array_keys($vars);
	}

	loadProperties();

        $common_path=$root_path;
        
	ini_set('log_errors',"on");
	ini_set('error_log',$root_path."/test/php_error.log");
	ini_set('max_execution_time',30000);

	error_log("Enter ".__FILE__);//Late so it goes to the Error Log :)
	error_log("ShowManager Properties Loaded");

    $time_start=microtime(true);
    $debug=isset($_GET['debug']);


    if ($debug) print(__FILE__."(".__LINE__.")<br/>\n");

    $include=ini_get("include_path")
            /*Project Code* /
            .$ips.$root_path
            .$ips.$root_path.$fps."script"
            .$ips.$root_path.$fps."class"
            .$ips.$root_path.$fps."font"
            .$ips.$root_path.$fps."pages"
            .$ips.$root_path.$fps."extensions"
            .$ips.$root_path.$fps."database"
            /*Test Code*/
            .(isset($test_path)?$ips.$test_path.$fps."class":"")
            /*Common Code*/
            .$ips.$common_path
            .$ips.$common_path.$fps."script"
            .$ips.$common_path.$fps."class"
            .$ips.$common_path.$fps."font"
            .$ips.$common_path.$fps."pages"
            .$ips.$common_path.$fps."extensions"
            .$ips.$common_path.$fps."phpdbmigrate"
            .$ips.$common_path.$fps."googleApi"
            .$ips.$common_path.$fps."googleApi".$fps."contrib"
            /**/
        ;
    
    if (isset($GLOBALS['TESTMODE'])) error_log($include);
    
    ini_set("include_path",$include);
    if ($debug) print(__FILE__."(".__LINE__.")<br/>\n");
/************************************************************\
*   Common Utils
\************************************************************/
    if ($debug) print(__FILE__."(".__LINE__.")<br/>\n");
    @include_once "script/utils.php";
    @include_once "const.php";
    PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'PEAR_ErrorToPEAR_Exception');
    krumo::disable();
   
//************************************************
error_log("Exit ".__FILE__);
?>
