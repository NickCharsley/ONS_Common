<?php
/*
 * File iPAD.php
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

class iuiPAD extends HTML5 {


    static function start(){
        if (get_class(iuiPAD::singleton())!=__CLASS__)
            self::$instance=new iuiPAD();
        parent::start();

        iuiPAD::meta(array('name'=>"viewport",'content'=>"width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"));
        iuiPAD::meta(array('name'=>"apple-mobile-web-app-capable",'content'=>"yes"));
        iuiPAD::link(array('rel'=>"stylesheet",'href'=>"http://iui.local/iui/iui.css",'type'=>"text/css"));
        iuiPAD::link(array('rel'=>"stylesheet",'title'=>"Default",'href'=>"http://iui.local/iui/t/default/default-theme.css",'type'=>"text/css"));
        iuiPAD::link(array('rel'=>"stylesheet",'href'=>"http://iui.local/css/iui-panel-list.css",'type'=>"text/css"));
        iuiPAD::script(array('type'=>"application/x-javascript",'src'=>"http://iui.local/iui/iui.js"));

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
    iuiPAD::Start();
    phpinfo();
}
//************************************************
debug_error_log("Exit ".__FILE__);
?>
