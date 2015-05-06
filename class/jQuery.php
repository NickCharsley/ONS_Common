<?php
/*
 * File HTML.php
 * Created on 13 Aug 2010 by nick
 * email php@oldnicksoftware.co.uk
 *
 *
 * Copyright 2010 ONS
 *
 */
 if (!defined("__ONS_COMMON__"))
    include_once('ons_common.php');
 debug_error_log("Enter ".__FILE__);
//************************************************
$js=true;

class jQuery extends HTML5 {

    // A private constructor; prevents direct creation of object
    static function Start()
    {
        global $js;
        global $web;

        //krumo($web);
        if (get_class(jQuery::singleton())!=__CLASS__)
            self::$instance=new jQuery();

        parent::start();
        if ($web){
            HTML5::Script(array('type'=>"text/javascript",'src'=>"http://www.bytenight.co.uk/jslib/jquery-1.5/modernizr-1.5.min.js"));
            HTML5::script(array('type'=>"text/javascript",'src'=>"http://code.jquery.com/jquery-1.8.3.js"));
            HTML5::script(array('type'=>"text/javascript",'src'=>"http://code.jquery.com/ui/1.9.2/jquery-ui.js"));
            HTML5::css("http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css");
        }
        else {
            HTML5::Script(array('type'=>"text/javascript",'src'=>"http://jquery.local/modernizr-1.5.min.js"));
            HTML5::script(array('type'=>"text/javascript",'src'=>"http://jquery.local/jquery.js"));

        //if ($js){
            //Load persistence.js and plugins
            HTML5::script(array('type'=>"text/javascript",'src'=>"http://persistencejs.local/persistence.js"));
            HTML5::script(array('type'=>"text/javascript",'src'=>"http://persistencejs.local/persistence.store.sql.js"));
            HTML5::script(array('type'=>"text/javascript",'src'=>"http://persistencejs.local/persistence.store.websql.js"));
            HTML5::script(array('type'=>"text/javascript",'src'=>"http://persistencejs.local/persistence.store.memory.js"));
            HTML5::script(array('type'=>"text/javascript",'src'=>"http://persistencejs.local/persistence.sync.js"));
        //}
        }
    }
}

//** Eclipse Debug Code **************************
if (str_replace("/","\\",__FILE__)==str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"])){

    if (class_exists('gtk',false)) {
        print($_SERVER["SCRIPT_FILENAME"]."\n\r");
        //TODO:any gtk specific code for HTML.php goes here
    } else {
        print("<h1 align='center'>".$_SERVER["SCRIPT_FILENAME"]."</h1>\n");
        //TODO:any web specific code for HTML.php goes here
    }
    //TODO:any generic code for HTML.php goes here


}
//************************************************
debug_error_log("Exit ".__FILE__);
?>
