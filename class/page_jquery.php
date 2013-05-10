<?php
/*
 * File jqtouch.php
 * Created on 23 May 2011 by Nick
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
    //TODO:any gtk specific code for jqtouch.php goes here
} else {
    //TODO:any web specific code for jqtouch.php goes here
}
//TODO:any generic code for jqtouch.php goes here

class page_jquery extends page {
    public function js($inline=true){
        /*Headers*/
        $ret="
<script type='text/javascript'>
    // Check if a new cache is available on page load.
    window.addEventListener('load', function(e) {

        window.applicationCache.addEventListener('updateready', function(e) {
            if (window.applicationCache.status == window.applicationCache.UPDATEREADY) {
                // Browser downloaded a new app cache.
                // Swap it in and reload the page to get the new hotness.
                window.applicationCache.swapCache();
                if (confirm('A new version of this site is available. Load it?')) {
                    window.location.reload();
                }
            } else {
                // Manifest didn't changed. Nothing new to server.
            }
        }, false);

}, false);
</script>";
        /*Global Variable Definitions*/
        foreach ($this->pages($this) as $page){
            $ret.= "/**** ".str_pad("Global Variable Definitions ".get_class($page)." Start ",60,"*")."****/\n";
            $ret.= $page->js_global();
            $ret.= "/**** ".str_pad("Global Variable Definitions ".get_class($page)." Finish ",60,"*")."****/\n";
        }
        /*Actions to performe on Document ready*/
        $ret.= '$(document).ready(function(){'."\n";
        foreach ($this->pages($this) as $page){
            $ret.= "/**** ".str_pad("Document ready ".get_class($page)." Start ",60,"*")."****/\n";
            $ret.= $page->js_ready();
            $ret.= "/**** ".str_pad(" Document ready ".get_class($page)." Finish ",60,"*")."****/\n";
        }
        $ret.=  '});'."\n";
        /*Global Functions*/
        foreach ($this->pages($this) as $page){
            $ret.= "/**** Global Functions ".str_pad(get_class($page)." Start ",41,"*")."****/\n";
            $ret.= $page->js_functions();
            $ret.= "/**** Global Functions ".str_pad(get_class($page)." Finish ",41,"*")."****/\n";
        }
        /**/
        if (!$inline){
            HTML5::raw();
            HTML5::phpHeader('content-type:text/javascript');
            print $ret;
        }
        else HTML5::jsFunction($ret);
    }

    protected function js_global(){
        $ret="";
        foreach ($this->tables as $table){
            $ret.= dbRoot::persistence($table);
            $ret.= dbRoot::setupSync($table);
        }
        if ($ret="") $ret="/**** ".str_pad("MT Global Function ".get_class($this)." ",60,"*")."****/\n";
        return $ret;
    }

    protected function js_ready(){
        return "/**** ".str_pad("MT Ready Function ".get_class($this)." ",60,"*")."****/\n";
    }
    protected function js_functions(){
        return "/**** ".str_pad("MT Function ".get_class($this)." ",60,"*")."****/\n";
    }

    protected function js_pageAnimationEnd(){
        return "/**** ".str_pad("MT sync".ucfirst($this->id)." Function ".get_class($this)." ",60,"*")."****/\n";
    }

    protected function js_pageAnimationStart(){
        return "$('#".$this->id." ul li:gt(0)').remove();\n";
    }

    function js_sync($result){
        return "refresh".ucfirst($this->id)."('sync".ucfirst($this->id).":$result');";
    }

    protected function render_middle(){
        if (isset($this->linkPage)){
            $ret =$this->tabIn()."<ul class=\"edgetoedge\">\n";
            $ret.=$this->tabIn()."<li id=\"".$this->id."Template\" class=\"arrow\" style=\"display:none\">\n";
            $ret.=$this->tabInOut()."<a href=\"#prizesectionclasses\"><span class=\"label\">Label</span></a>\n";
            $ret.=$this->tabOut()."</li>\n";
            $ret.=$this->tabOut()."</ul>\n";
        }
        else
        $ret=parent::render_middle();
        return $ret;
    }
 /**/
    function render($frag=false,$print=true){
        $ret=$this->tabIn()."<div id='jqt'>\n";
        $ret.=parent::render($frag,false);
        $ret.=$this->tabInOut()."</div>";
        if ($print and !$frag) print $ret;
        return $ret;
    }
/**/
    protected function templateName(){
        return strtolower($this->id)."Template";
    }

}

//** Eclipse Debug Code **************************
if (str_replace("/","\\",__FILE__)==str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"])){
    if (class_exists('gtk',false)) {
        //TODO:any gtk specific code for jqtouch.php goes here
    } else {
        //TODO:any web specific code for jqtouch.php goes here
    }

}
//************************************************
debug_error_log("Exit ".__FILE__);
?>