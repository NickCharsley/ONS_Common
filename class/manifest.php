<?php
/*
 * File manifest.php
 * Created on 3 Jun 2011 by Nick
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
    //TODO:any gtk specific code for manifest.php goes here
} else {
    //TODO:any web specific code for manifest.php goes here
}
//TODO:any generic code for manifest.php goes here

class manifest {
    private $files=array();
    private $on=true;
    private $hash="";

    function getManifest(){
        return $this->files;
    }

    function on(){
        debug_error_log("Manifest On");
        $this->on=true;
    }

    function off(){
        debug_error_log("Manifest Off");
        $this->on=false;
    }

    function getHash($add=""){
        return md5($this->hash.$add);
    }

    function file($file,$segment="CACHE MANIFEST",$hash=true){
        if ($segment===false){
            $hash=false;
            $segment="CACHE MANIFEST";
        }
        if (!$this->on) $segment="NETWORK:";
        $this->files[$segment][$file]=$file;
        if ($hash){
            //debug_error_log("Hash $file=".md5_file($file));
            $this->hash.=md5_file($file);
        }
        return $file;
    }

    function directory($directory,$segment="CACHE MANIFEST",$add=true){
        if ($segment===false){
            $add=false;
            $segment="CACHE MANIFEST";
        }
        if (!$this->on) $segment="NETWORK:";
        $this->map_directory($directory,$directory,$segment,$add);
    }

    function map_directory($directory,$map,$segment="CACHE MANIFEST",$add=true){
        if ($segment===false){
            $hash=false;
            $segment="CACHE MANIFEST";
        }
        if (!$this->on) $segment="NETWORK:";
        $dir=new RecursiveDirectoryIterator($directory);
        foreach(new RecursiveIteratorIterator($dir) as $file){
            if ($file->Isfile() and substr($file->getFilename(),0,1)!='.' and strpos($file->getFilename(),"log")==false) {
                if ($add) $this->file(str_replace("\\","/",buildPath($map,substr($file,strlen($directory)))),$segment,false);
                //debug_error_log("Hash $file=".md5_file($file));
                $this->hash.=md5_file($file);
            }
        }
    }

    function network($file){
        $segment="NETWORK:";
        $this->files[$segment][$file]=$file;
    }
}

//** Eclipse Debug Code **************************
if (str_replace("/","\\",__FILE__)==str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"])){
    if (class_exists('gtk',false)) {
        //TODO:any gtk specific code for manifest.php goes here
    } else {
        //TODO:any web specific code for manifest.php goes here
    }

}
//************************************************
debug_error_log("Exit ".__FILE__);
?>