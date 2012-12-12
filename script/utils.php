<?php
/*
 * File utils.php
 *
 * Created on 01-Feb-2006 by N.A.Charsley
 *
 * Copyright 2006 ONS
 *
 *
 *
 */

	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	if (!isset($start_time))
		$start_time = $time;


	ini_set('error_log',dirname(dirname(__FILE__))."/errorLogs/error.".basename($_SERVER["PHP_SELF"]).".log");
	ini_set('max_execution_time',300);
	
    error_log("Enter ".__FILE__);
    
    include_once "$common_path/krumo/class.krumo.php";
    
    function ons_InDrupal(){
        return class_exists('DrupalCacheArray',false);
    }
 
    function getValue(&$data,$tag){
        //Assumption Tag is last thing in text,
        //all data past this point is part of the value
        //the tag is removed.
        if (($pos=strpos($data,$tag))===false)
            $ret="";
        else {
            $ret=trim(substr($data,$pos+strlen($tag)));
            $data=trim(substr($data,0,$pos));
        }

        return $ret;
    }

    function pg_value($type,$default=""){
        if (isset($_POST[$type])) return $_POST[$type];
        if (isset($_GET[$type])) return $_GET[$type];
        return $default;
    }

    function compress($data){
        return str_replace(" ","",$data);
    }

    function  getGetLine($append=true){
        $ret="";

        if  (isset($_GET)){
            foreach ($_GET as  $key=>$value){
                if (strlen($ret)>0)
                    $ret.="&";
                else {
                    if ($append)
                        $ret="&";
                    else
                        $ret="?";
                }

                $ret.="$key=$value";
            }
        }
        return $ret;
    }
/*
    function dumpFile($file){
        $lines = file($file);

        // Loop through our array, show HTML source as HTML source; and line numbers too.
        foreach ($lines as $line_num => $line) {
            echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
        }

    }
*/

function PEARError($obj,$msg="Pear Error",$die=true){
    if (PEAR::isError($obj)) {        
        if ($die) {
            debug_print($obj);
            die("$msg ".$obj->getMessage());
        } else 
            debug_error_log("$msg ".$obj->getMessage());
    }
    return $obj;
}

    function query($sql,$msg="",$die=true){
        global $db;        
        return PEARError($db->query($sql),$msg,$die);
    }
    
    function truncateTable($table){
        global $db;        
        
        if (!PEAR::isError(PEARError($db->exec("truncate table $table"),"Truncating Table $table",false)))
            debug_error_log("Table $table Truncated");
        else  if (!PEAR::isError(PEARError($db->exec("delete from $table"),"Deleting from $table, which may be referenced in a foreign key constraint"/*,false*/)))
            debug_error_log("Truncated $table which may be referenced in a foreign key constraint");
        else 
            debug_error_log("Truncating $table failed");
    }
    
    function ons_autoload($class_name) {
        global $fps;
        if (@fclose(fopen($class_name . '.class.php','r',true))){
        	debug_error_log("ons_autoload($class_name)");
        	require_once $class_name . '.class.php';
        }
        else if (@fclose(fopen($class_name . '.php','r',true))){
        	debug_error_log("ons_autoload($class_name)");            
            require_once $class_name . '.php';
        } else if (strpos($class_name,'_')){
        	debug_error_log("ons_autoload($class_name)");
        	ons_autoload(str_replace("_",$fps,$class_name));
        }        
    }
 
    function register_autoloader(){
        global $autoloader;
        if (!$autoloader){
            spl_autoload_register('ons_autoload');
            $autoloader++;
        }
    }
    
    function unregister_autoloader(){
        global $autoloader;
        if ($autoloader){
            spl_autoload_unregister('ons_autoload');
            $autoloader--;
        }
    }
    
    function resetIncludePath(){
        global $inc_path;
        ini_set("include_path",$inc_path);
    }
    
    function debug_print($text){
        global $local;
        if ($local) {
            if (ons_InDrupal() and function_exists("ddl")){
                ddl($text);
            }
            else if(function_exists("Krumo")){
                Krumo($text);
                krumo::backtrace();
            }
            else print_pre($text);
        }
        debug_error_log($text);
    }
    
    function debug_error_log($message){
        if (is_array($message)){
            error_log(var_export($message,true));           
        }
        else if (is_object($message)){          
            //Krumo($text);
            //krumo::backtrace();
        }
        else error_log($message);
    }    
    
    function print_pre($data,$ret=false){
    	global $web;
    	if ($web)
        	$strRet="<pre>".htmlspecialchars(print_r($data,true))."</pre>\n";
    	else 
    		$strRet=print_r($data,true)."\n";
        if (!$ret) echo $strRet;
        return $strRet;
    }
    function printLine($line,$ret=false){
        global $web;
        $strRet= $line.(($web)?"<br>\n":"\n");
        if (!$ret) print $strRet;
        return $strRet;
    }
    function print_line($line,$ret=false){
        return printLine($line,$ret);
    }
    function AddButton($text,$action){
        return "<a class='Button' href='$action'?>$text</a>\n";
    }
    
    function AddIconButton($icon,$text,$action){
        return "<a class='Button' href='$action'?><img src='$icon' alt='$text'/></a>\n";
    }
    function array2Table($table){
        print "<table>";
        foreach($table as $row){
            print "<tr>";
            foreach ($row as $cell){
                print "<td>$cell</td>";
            }
            print "</tr>";
        }
        print "</table>";
        die;
    }
    function buildPath(){
        global $fps;
        $arg_list = func_get_args();
        $numargs = func_num_args();

        //print_pre($arg_list);
        
        $ret=$arg_list[0];
        if (substr($ret,-1)=="/") $ret=substr($ret,0,-1);
        if (substr($ret,-1)=="\\") $ret=substr($ret,0,-1);
        for ($i = 1; $i < $numargs; $i++) {
            if (substr($arg_list[$i],0,1)=="\\")
                $ret.=$arg_list[$i];
            else if (substr($arg_list[$i],0,1)=="/")
                $ret.=$arg_list[$i];
            else
                $ret.=$fps.$arg_list[$i];
            if (substr($ret,-1)=="/") $ret=substr($ret,0,-1);
            if (substr($ret,-1)=="\\") $ret=substr($ret,0,-1);      
        }
        //print_line($ret);
        return $ret;
    }
    

function unwrap($text){
    /**/
    $tags=array("<script","</script","<style","</style");
    /**/
    foreach($tags as $tag){     
        if (!(strpos($text,$tag)===false)){
            $start=strpos($text,$tag);
            $end=strpos($text,">",$start)+1;
            debug_error_log("unwrap [$start]-[$end]");
            $l=substr($text,0,$start);
            $r=substr($text,$end);
            $text=unwrap($l.$r);
        }
    }
    /**/
    return $text;
}

function Odd($data){
    return !Even($data);
}

function Even($data){
    return ($data % 2==0);
}

 /**
     * Generates a UUID according to http://www.ietf.org/rfc/rfc4122.txt
     */
function createUUID() {
    $s = array();
    $hexDigits = "0123456789ABCDEF";
    for ( $i = 0; $i < 32; $i++) {
        $s[$i] = substr($hexDigits,rand(0,15), 1);
    }
    $s[12] = "4";
    $s[16] = substr($hexDigits,($s[16] & 0x3) | 0x8, 1);

    $uuid = join("",$s);
    return $uuid;
}

    function safeunlink($file){
        if (file_exists($filename)) unlink($file);
    }
    
    function safe_include($filename){
        if (file_exists($filename)){
            include '$filename';
        }
    }
   function exception_error_handler($errno, $errstr, $errfile, $errline ) {
      echo "ErrorException:\n$errstr\nIn $errfile at $errline\n";
      throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
   }

   function write_ini_file($filename,$sections){
    $handle=fopen($filename, "w");
    foreach ($sections as $section=>$items){
        fwrite($handle, "[$section]\n");
        foreach ($items as $item=>$value){
            fwrite($handle, "$item=$value\n");
        }
        fwrite($handle, "\n");
    }
    fclose($handle);
   }

   function pageTime(){
   	global $start_time;
   	$time = microtime();
   	$time = explode(' ', $time);
   	$time = $time[1] + $time[0];
   	$finish = $time;
   	$total_time = round(($finish - $start_time), 4);
   	print_line('Page generated in '.$total_time.' seconds.');
   }
    
   
function getHTMLElement($data,$element,$class=""){
    $data=str_replace("&nbsp;"," ",$data);
    if (is_array($element)){
        if (!is_array($class)) $class=array();
        $html=$data;
        for ($i=0;$i<count($element);$i++){
            if (!isset($class[$i])) $class[$i]="";
            $html=getHTMLElement($html,$element[$i],$class[$i]);
        }
    } else {
        if ($class=="")
            $html= substr($data,1+strpos($data,">",strpos(strtolower($data),strtolower("<$element "))));
        else        
            $html= substr($data,1+strpos($data,">",strpos(strtolower($data),strtolower("<$element class=\"$class"))));
        $html=trim(substr($html,0,strpos(strtolower($html),strtolower("</$element"))));
    }
    return $html;
}

   
 register_autoloader();

 function PEAR_ErrorToPEAR_Exception($err)
 {
 	Krumo($err);
 	if ($err->getCode()) {
 		throw new PEAR_Exception($err->getMessage(),
 				$err->getCode());
 	}
 	throw new PEAR_Exception($err->getMessage());
 }

 function exception_handler($exception) {
 	//If we have a xdebug_message we will just use it
 	if (isset($exception->xdebug_message)){
 		print "<font size='1'><table class='xdebug-error xe-parse-error' dir='ltr' border='1' cellspacing='0' cellpadding='1'>";
 		print $exception->xdebug_message;
 		print "</table>";
 		return;
 	}
 	
 // these are our templates
    $traceline = "#%s %s(%s): %s(%s)";
    $msg = "PHP Fatal error:  Uncaught exception '%s' with message '%s' in %s:%s\nStack trace:\n%s\n  thrown in %s on line %s";

    // alter your trace as you please, here
    $trace = $exception->getTrace();
    Krumo($exception);
    foreach ($trace as $key => $stackPoint) {
        // I'm converting arguments to their type
        // (prevents passwords from ever getting logged as anything other than 'string')
        $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
    }

    // build your tracelines
    $result = array();
    foreach ($trace as $key => $stackPoint) {
        $result[] = sprintf(
            $traceline,
            $key,
            (isset($stackPoint['file'])?$stackPoint['file']:'<?>'),
            (isset($stackPoint['line'])?$stackPoint['line']:'<?>'),
            (isset($stackPoint['function'])?$stackPoint['function']:'<?>'),
            (isset($stackPoint['args'])?implode(', ', $stackPoint['args']):'<?>')
        );
    }
    // trace always ends with {main}
    $result[] = '#' . ++$key . ' {main}';

    // write tracelines into main template
    $msg = sprintf(
        $msg,
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        implode("\n", $result),
        $exception->getFile(),
        $exception->getLine()
    );

    // log or echo as you please
    error_log($msg);
	print($msg);    
 }
 
 set_exception_handler('exception_handler');
 
 
 debug_error_log("Exit ".__FILE__);
?>

