<?php
/*
 * File const.php
 * Created on Sep 21, 2006 by N.A.Charsley
 * email php@oldnicksoftware.co.uk
 *
 *
 * Copyright 2006 ONS
 *
 */
 if (!defined("__ONS_COMMON__"))
    include_once('ons_common.php');
 debug_error_log("Enter ".__FILE__);
//************************************************

    define('GP_STARTDATE',0);
    define('GP_ENDDATE',1);
    define('GP_SORTDATE',2);    
    
    //Fuzzy group of date modifiers  - certainty
    define('GP_EXACT',0); //0 always means exact "hard" date for all modifiers
    define('GP_CIRCA',1); //close to, somewhere around
    define('GP_ABOUT',2); //similiar to circa/say
    define('GP_SAY',3); //more of a guess
    define('GP_EST',4); //another guess/estimate
    define('GP_CALC',5); //Calculated such as from Age or unrelated event
 
     //before after group
    define('GP_BEFORE',1); //Date is somewhere before
    define('GP_AFTER',2); //Date is somewhere after
    define('GP_ONORBEFORE',3); //Date is on or sometime before
    define('GP_ONORAFTER',4); //Date is on or sometime after
    //Ranges - dates that need 2 dates to express
    define('GP_SINGLEDATE',0); //not a range date alias for EXACT
    define('GP_BETWEEN',1); //Single point date between two date
    define('GP_FROMTO',2); //Event that spans range or duration
    define('GP_EITHEROR',3); //One date or the other but not both
    //Unsure and Irreg
    define('GP_NOTIRR',0); //not irregular or unsure alias for EXACT                    
    define('GP_IRREG',1); //No known way to convert to numeric value
    define('GP_UNSURE',2); // (?) on date - researcher unsure due to context or legibility

//** Eclipse Debug Code **************************
if (strpos($_SERVER['SCRIPT_NAME'],"const.php")>0){
    print("<h1 align='center'>const.php</h1>");
    phpinfo();  
}
//************************************************
debug_error_log("Exit ".__FILE__);
?>
