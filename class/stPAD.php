<?php
/*
 * File stPAD.php
 * Created on 1 Feb 2011 by nick
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
$ipad_buttons=0;

class stPAD extends HTML5 {

    static function start(){
        parent::start();

        stPAD::meta(array('charset'=>"utf-8"));
        //<!-- Sencha Touch CSS -->
        stPAD::link(array('rel'=>"stylesheet",'href'=>"sencha.local/resources/css/sencha-touch.css",'type'=>"text/css"));
        //<!-- Sencha Touch JS -->
        stPAD::script(array('type'=>"text/javascript",'src'=>"sencha.local/sencha-touch-debug.js"));
    }
}

if (class_exists('gtk',false)) {
    //TODO:any gtk specific code for iPAD.php goes here
} else {
    //TODO:any web specific code for iPAD.php goes here
}
//TODO:any generic code for iPAD.php goes here

//DONE: It's just a stub file'

//** Eclipse Debug Code **************************
if (str_replace("/","\\",__FILE__)==str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"])){
    if (class_exists('gtk',false)) {
        print($_SERVER["SCRIPT_FILENAME"]."\n\r");
        //TODO:any gtk specific code for iPAD.php goes here
    } else {
        print("<h1 align='center'>".$_SERVER["SCRIPT_FILENAME"]."</h1>");
        //TODO:any web specific code for iPAD.php goes here
    }
    //TODO:any generic code for iPAD.php goes here
    phpinfo();
}
//************************************************
debug_error_log("Exit ".__FILE__);
?>
