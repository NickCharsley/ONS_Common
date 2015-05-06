<?php
/*
 * File page.php
 * Created on 30 Aug 2010 by nick
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

$cid=0;

class page {
    protected $id;
    protected $title;
    protected $selected;
    protected $extraPage=array();
    protected $subPage=array();
    protected $subPage_link;
    protected $frag;
    protected $back=array("href"=>"#","title"=>"Back","class"=>'button back');
    protected $navButton=array();
    protected $index=false;
    protected $sub=false;
    protected $rendered=false;
    protected $linkPage;
    protected $tables=array();

    public    $manifest;

    public function getParents($class=null, $plist=array()) {
        $plist[]=$class ? $class : get_class($this);
        $class = $class ? $class : $this;
        $parent = get_parent_class($class);
        if($parent) {
            /*Do not use $this. Use 'self' here instead, or you
            * will get an infinite loop. */
            $plist = self::getParents($parent, $plist);
        }
        return $plist;
    }

    protected function pages($add=null){
        $ret=array();
        if (isset($add)) $ret[$add->id]=$add;
        foreach ($this->subPage as $page)
        {
            $ret=array_merge($ret,$page->pages($page));
        }
        foreach ($this->extraPage as $page)
        {
            $ret=array_merge($ret,$page->pages($page));
        }
        foreach ($this->navButton as $page)
        {
            $ret=array_merge($ret,$page->pages($page));
        }
        return $ret;
    }
    protected function tab(){
        global $tab;
        return str_repeat("\t", $tab);
   	}
    protected function tabIn(){
        global $tab;
        $tab++;
        return $this->tab();
    }
    protected function tabInOut(){
        global $tab;
        $tab++;
        $ret= $this->tab();
        $tab--;
        return $ret;
    }
    protected function tabOut(){
        global $tab;
        $ret= $this->tab();
        $tab--;
        return $ret;
    }

    function js($inline){
        debug_error_log(get_class($this).": JS request");
        $ls="";
        foreach ($this->pages() as $page){
            $js.=$page->js($inline);
        }
        if (!$inline){
            HTML5::raw();
            HTML5::phpHeader('Content-type: text/javascript');
            print $js;
        }
        else HTML5::jsFunction($js);
    }

    function manifest(){
        debug_error_log(get_class($this).": Manifest request");
        HTML5::raw();

        $manifest=array_merge_recursive(HTML5::getManifest()->getManifest(),$this->manifest->getManifest());
        HTML5::phpHeader('Content-type: text/cache-manifest');
        foreach ($this->pages() as $page){
            $manifest=array_merge_recursive($manifest,$page->manifest());
        }
        if ($this->index){
            foreach ($manifest as $section=>$files){
                print "$section\n";
                foreach ($files as $file){
                    if (is_array($file))
                        print $file[0]."\n";
                    else
                        print "$file\n";
                }
                print "\n";
            }
            echo "# Hash ".$this->manifest->getHash();
        }
        return $manifest;
    }


    function css($inline=true){
        debug_error_log(get_class($this).": CSS request ".($inline?"Inline":"file"));
        $css="";
        foreach ($this->pages() as $page){
            $css.=$page->css();
        }
        if (!$inline){
            HTML5::raw();
            HTML5::phpHeader('Content-type: text/css');
            print $css;
        } else HTML5::cssStyle($css);
    }

    protected function __construct($id,$title="",$selected=false){
        global $cid;
        if ($title==""){
            $title=$id;
            $id=strtolower(str_replace(" ","_",$title));
        }
        $this->selected=$selected;
        $this->title=$title;
        $this->id=strtolower($id);//."_".$cid++;
        $this->subPage_link="arrow";
        $this->manifest=new manifest();
    }

    function linkTo(){
        $ret =$this->tabIn()."<li class='".$this->subPage_link."'>\n";
        $ret.=$this->tabInOut()."<a href='#".$this->id."'>".$this->title."</a>\n";
        $ret.=$this->tabOut()."</li>\n";
        return $ret;
    }

    function subPage($page){
        $page->sub=true;
        $this->subPage[]=$page;
        return $page;
    }

    function extraPage($page){
        $page->sub=true;
        $this->extraPage[]=$page;
        return $page;
    }

    function LinkPage($page){
        $page->sub=true;
        $this->linkPage=$page;
        return $this->extraPage($page);
    }

    function navButton($page){
        $page->sub=true;
        $this->navButton[]=$page;
        return $page;
    }
    protected function render_nav(){
        $ret=$this->tabIn()."<div class='toolbar'>\n";
        $ret.=$this->tabInOut()."<h1>".$this->title."</h1>\n";
        if ($this->sub) $ret.=$this->tabInOut()."<a class='".$this->back['class']."' href='".$this->back['href']."'>".$this->back['title']."</a>\n";
        foreach ($this->navButton as $page){
            $ret.=$page->linkTo();
        }
        $ret.=$this->tabOut()."</div>\n";
        return $ret;
    }
    protected function render_top(){
        return "";
    }
    protected function render_middle(){
        $ret=$this->tabIn()."<ul class='edgetoedge'>\n";
        foreach ($this->subPage as $page){
            $ret.=$page->linkTo();
        }
        $ret.=$this->tabOut()."</ul>\n";
        return $ret;
    }
    protected function render_bottom(){
        return "";
    }

    public function render($frag=false,$print=true){

        debug_error_log(get_class($this).": Render request");
        HTML5::Title($this->title);

        foreach ($this->pages($this) as $page){
            $ret.=$page->render_Sub();
        }
        if ($print and !$frag) print $ret;
        return $ret;
    }

    public function render_Sub(){
        $ret=$this->tabIn()."<div id='".$this->id."'>\n";
        if (!($this->sub and $this->frag)){
            $ret.=$this->render_nav();
            $ret.=$this->render_top();
            $ret.=$this->render_middle();
            $ret.=$this->render_bottom();
        }
        $ret.=$this->tabOut()."</div>\n";
        return $ret;
    }
}

//** Eclipse Debug Code **************************
if (str_replace("/","\\",__FILE__)==str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"])){
    if (class_exists('gtk',false)) {
        print($_SERVER["SCRIPT_FILENAME"]."\n\r");
        //TODO:any gtk specific code for page.php goes here
    } else {
        print("<h1 align='center'>".$_SERVER["SCRIPT_FILENAME"]."</h1>");
        //TODO:any web specific code for page.php goes here
    }
    //TODO:any generic code for page.php goes here
    phpinfo();
}
//************************************************
debug_error_log("Exit ".__FILE__);
?>
