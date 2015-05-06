<?php
/*
 * File JQTOUCH.php
 * Created on 22 May 2011 by Nick
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
if (class_exists('gtk',false)) {
    //TODO:any gtk specific code for JQTOUCH.php goes here
} else {
    //TODO:any web specific code for JQTOUCH.php goes here
}
//TODO:any generic code for JQTOUCH.php goes here
class jqTouch extends HTML5 {
    protected $theme="jqt";

    static function Theme($theme=""){
        if ($theme<>"")
            JQTOUCH::singleton()->theme=$theme;
        return JQTOUCH::singleton()->theme;
    }

    static function themeImg($img){
        global $local;
        if ($local){
            return "http://jqtouch.local/themes/".jqTouch::Theme()."/img/$img";
        } else {
            return "http://www.bytenight.co.uk/jqtouch/themes/".jqTouch::Theme()."/img/$img";
        }
    }

    static function start(){
        global $local;
        if (get_class(HTML5::singleton())!=__CLASS__)
            self::$instance=new JQTOUCH();

        parent::start();
        /**/
        if ($local){
            HTML5::css("screen",HTML5::getManifest()->file("http://jqtouch.local/jqtouch/jqtouch.css"),0);
            HTML5::Script(array("type"=>"text/javascript","src"=>HTML5::getManifest()->file("http://jquery.local/jquery.js")));
            HTML5::Script(array("type"=>"text/javascript","src"=>HTML5::getManifest()->file("http://jqtouch.local/jqtouch/jqtouch.js")));
            HTML5::Script(array("type"=>"text/javascript","src"=>HTML5::getManifest()->file("http://jqtouch.local/extensions/jqt.autotitles.js")));

            HTML5::getmanifest()->map_directory('C:\Users\Nick\workspace\jQTouch\themes',"http://jqtouch.local/themes/");
        }
        else {
            HTML5::css("screen",HTML5::getManifest()->file("http://www.bytenight.co.uk/jqtouch/jqtouch/jqtouch.css"),0);
            HTML5::Script(array("type"=>"text/javascript","src"=>HTML5::getManifest()->file("http://code.jquery.com/jquery-1.6.1.min.js")));
            HTML5::Script(array("type"=>"text/javascript","src"=>HTML5::getManifest()->file("http://www.bytenight.co.uk/jqtouch/jqtouch/jqtouch.js")));
            HTML5::Script(array("type"=>"text/javascript","src"=>HTML5::getManifest()->file("http://www.bytenight.co.uk/jqtouch/extensions/jqt.autotitles.js")));

            HTML5::getmanifest()->map_directory('/home/bytenig/www/jqtouch/themes',"http://www.bytenight.co.uk/themes/");
        }
        /**/
    }

    protected function preWrap(){
        global $local;
        if ($local){
            HTML5::css("screen","http://jqtouch.local/themes/".$this->theme."/theme.css",1);
        } else {
            HTML5::css("screen","http://www.bytenight.co.uk/jqtouch/themes/".$this->theme."/theme.css",1);
        }
    }
}


//** Eclipse Debug Code **************************
if (str_replace("/","\\",__FILE__)==str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"])){
    if (class_exists('gtk',false)) {
        //TODO:any gtk specific code for JQTOUCH.php goes here
    } else {
        //TODO:any web specific code for JQTOUCH.php goes here
    }

}
//************************************************
debug_error_log("Exit ".__FILE__);
?>