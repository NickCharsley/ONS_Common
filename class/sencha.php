<?php
/*
 * File sencha.php
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
    //TODO:any gtk specific code for sencha.php goes here
} else {
    //TODO:any web specific code for sencha.php goes here
}
//TODO:any generic code for sencha.php goes here
class sencha extends HTML5 {    
    static function start(){
        global $local;
        if (get_class(HTML5::singleton())!=__CLASS__)
            self::$instance=new sencha();

        parent::start();
        /**/
        if ($local){
            HTML5::css("screen",HTML5::getManifest()->file("http://sencha.local/resources/css/sencha-touch.css"),0);
            HTML5::Script(array("type"=>"text/javascript","src"=>HTML5::getManifest()->file("http://sencha.local/sencha-touch.js")));
        }
        else {
            HTML5::css("screen",HTML5::getManifest()->file("http://www.bytenight.co.uk/sencha/sencha/sencha.css"),0);
            HTML5::Script(array("type"=>"text/javascript","src"=>HTML5::getManifest()->file("http://code.jquery.com/jquery-1.6.1.min.js")));
            HTML5::Script(array("type"=>"text/javascript","src"=>HTML5::getManifest()->file("http://www.bytenight.co.uk/sencha/sencha/sencha.js")));
			HTML5::Script(array("type"=>"text/javascript","src"=>HTML5::getManifest()->file("http://www.bytenight.co.uk/sencha/extensions/jqt.autotitles.js")));
                        
            HTML5::getmanifest()->map_directory('/home/bytenig/www/sencha/themes',"http://www.bytenight.co.uk/themes/");    
        }
        /**/
    }

    protected function preWrap(){
    	$order['base']=array();
    	$order['app']=array();
    	$order['controller']=array();
    	$order['model_auto']=array();
    	$order['model']=array();
    	$order['store_auto']=array();
    	$order['store']=array();
    	$order['form_auto']=array();
    	$order['form']=array();
    	$order['view_subs']=array();
    	$order['view']=array();
    	
    	krumo($this->scripts);
    	
   		foreach ($this->scripts as $script){
    		if (is_array($script)){
    			if (isset($script['src'])){
					if (!(strpos($script['src'],"models")===false)){
						if (!(strpos($script['src'],"auto")===false))
    						$order['model_auto'][]=$script;
    					else    				
    						$order['model'][]=$script;
					} 
    				else if (!(strpos($script['src'],"stores")===false)){
						if (!(strpos($script['src'],"auto")===false))
    						$order['store_auto'][]=$script;
    					else    				
    						$order['store'][]=$script;
    				}    				
    				else if (!(strpos($script['src'],"forms")===false)){
						if (!(strpos($script['src'],"auto")===false))
    						$order['form_auto'][]=$script;
    					else    				
    						$order['form'][]=$script;
    				}    				
    				else if (!(strpos($script['src'],"views")===false)){
    					$dirs=split("/", $script['src']);
    					$view_dir=array_search("views", $dirs);
    					if (count($dirs)>($view_dir+3))
    						$order['view_subs'][]=$script;
    					else
    						$order['view'][]=$script;
    				}    				
    				else if (!(strpos($script['src'],"controllers")===false))
    					$order['controller'][]=$script;    				
    				else if (!(strpos($script['src'],"app")===false))
    					$order['app'][]=$script;    				
    				else
    					$order['base'][]=$script;
    			}
    			else
    				$order['base'][]=$script;
    		}
    		else {
    			$order['base'][]=$script;
    		}
    	}
    	$this->scripts=array();
    	krumo($order);
    	
    	foreach($order as $scripts)
    		foreach($scripts as $script)
    			$this->scripts[]=$script;
    	krumo($this->scripts);
    	return true;
	}
    
}



//** Eclipse Debug Code **************************
if (str_replace("/","\\",__FILE__)==str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"])){
    if (class_exists('gtk',false)) {
        //TODO:any gtk specific code for sencha.php goes here
    } else {
        //TODO:any web specific code for sencha.php goes here
    }

}
//************************************************
debug_error_log("Exit ".__FILE__);
?>