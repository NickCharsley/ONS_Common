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

class iPAD extends jqTouch {

    static function Start(){
    //  parent::start();
/** /
        HTML5::Script("http://persistencejs.local/lib/persistence.js");
        HTML5::Script("http://persistencejs.local/lib/persistence.store.sql.js");
        HTML5::Script("http://persistencejs.local/lib/persistence.store.websql.js");
        HTML5::Script("http://persistencejs.local/lib/persistence.sync.js");
        HTML5::Script("http://persistencejs.local/lib/persistence.migrations.js");
/**/
    }

    static function appIcon($icon){
        global $links;
        $links['raw'][]=array('rel'=>'apple-touch-icon','href'=>$icon);
    }

    static function NavButton(){

    }

    static function Button($text="",$address="",$script="return true;",$class="ipadButton"){
        global $ipad_buttons;
        $ipad_buttons++;
        print "<a " .
                "class='$class' " .
                "id='ipadButton$ipad_buttons' " .
                "name='ipadButton$ipad_buttons' " .
                ($address==""?"":"href='$address'") .
                ">$text</a>\n";
        HTML5::addonClick("ipadButton$ipad_buttons",$script);
    }

    static function BackButton($text="Back",$address="",$script="return true;"){
        if ($address=="") iPad::BackButton("Back",$text);
        else {
            print "<a class='Back Button' id='BackButton' name='BackButton' href='$address'>$text</a>\n";
            HTML5::addonClick("BackButton",$script);
        }
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
