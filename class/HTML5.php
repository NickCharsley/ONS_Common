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
class HTML5 {

	protected $params=array();
	protected $header=array();
	protected $phpheader=array();
	protected $scripts=array();
	protected $links=array();
	protected $raw=false;
	protected $actions=array();
	protected $jscripts=array();
	protected $css=array();
	protected $started=false;
	protected $linkTranslator=null;
	protected $manifest=null;
	protected $manifest_files;
	

	// Hold an instance of the class
    protected static $instance;

    // A private constructor; prevents direct creation of object
    protected function __construct()
    {
    }

    public static function set($key,$value){
    	HTML5::singleton()->params[$key]=$value;
    }
    
    public static function get($key){
    	return HTML5::singleton()->params[$key];
    }
    
    // The singleton method
    public static function singleton()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    // Prevent users to clone the instance
    public function __clone()
    {
        error_log('Clone is not allowed.', E_USER_ERROR);
    }

    static function linkTranslator($class){
    	HTML5::singleton()->linkTranslator=$class;
    }
    
	static function raw(){
		HTML5::singleton()->raw=true;
	}

	static function jsFunction($function){
		//Strip <script> tags
		while (!(strpos(strtolower($function),"<script")===false)){
			//remove <script ...>
			$start=strpos(strtolower($function),"<script");
			$end=strpos(strtolower($function),">",$start)+1;			
			$function=substr($function,0, $start).substr($function, $end);
			//remove </script ...>
			$start=strpos(strtolower($function),"</script");
			$end=strpos(strtolower($function),">",$start)+1;			
			$function=substr($function,0, $start).substr($function, $end);
		}
		HTML5::singleton()->jscripts[]=$function;
	}

	static function addOnClick($id,$script){
		HTML5::singleton()->actions[$id]['onClick']=$script;
	}

	static function addOnLoad($id,$script){
		HTML5::singleton()->actions[$id]['onload']=$script;
	}

	static function getManifest(){
		return HTML5::singleton()->manifest_files;
	}
	
	static function Start($type="HTML5"){
		//Clean arrays
		$html5=HTML5::singleton();

		$html5->header=array();
		$html5->phpheader=array();
		$html5->scripts=array();
		$html5->links['css']=array();
		$html5->links['raw']=array();
		$html5->raw=false;
		$html5->actions=array();
		$html5->jscripts=array();
		
		$html5->params=array();
		$html5->linkTranslator=null;
		$html5->manifest=null;
		$html5->manifest_files=new manifest();	
		//Restart Buffer;
		$html5->started=true;
	}

	static function Manifest($href){
		HTML5::singleton()->manifest=$href;
	}
	
	static function Script($script){
		global $html5;
		global $root;
		if (is_array($script))
			HTML5::singleton()->scripts[]=$script;
		else if (strpos($script,"http")===false)
			HTML5::singleton()->scripts[]="$root/$script";
		else
			$html5->scripts[]=$script;
	}
	static function Header($head){
		HTML5::singleton()->header[]=$head;
	}
	static function Title($title){
		HTML5::singleton()->header[]="<title>$title</title>\n";
	}
	static function META($name,$meta=""){
		if (is_array($name)){
			HTML5::singleton()->header[]=HTML5::tag('meta',$name);
		}
		else HTML5::singleton()->header[]="<meta name='$name' content='$meta' />";
	}

	static function href($address){
		global $root;
		if (strpos($address,"http")===false)
			return "$root/$address";
		else
			return $address;
	}

	static function js($href){
		HTML5::Script(array("type"=>"text/javascript","src"=>HTML5::href($href)));		
	}
	
	static function cssStyle($style){
		//Strip <style> tags
		while (!(strpos(strtolower($style),"<style")===false)){
			//remove <style ...>
			$start=strpos(strtolower($style),"<style");
			$end=strpos(strtolower($style),">",$start)+1;			
			$style=str_replace("\n\n","\n",trim(substr($style,0, $start))."\n".trim(substr($style, $end)));			
		}
		while (!(strpos(strtolower($style),"</style")===false)){
			//remove </style ...>
			$start=strpos(strtolower($style),"</style");
			$end=strpos(strtolower($style),">",$start)+1;			
			$style=str_replace("\n\n","\n",trim(substr($style,0, $start))."\n".trim(substr($style, $end)));
		}		
		if (strlen(trim($style))>3)
			HTML5::singleton()->css[]=trim($style);
	}
	
	static function css($media,$css="",$at=-1){
		global $root;
		if (is_array($media)){
			$media["rel"]="stylesheet";
			$media["type"]="text/css";
			HTML5::link($media,$at);
		}
		else if ($css==""){
			if (strpos($media,"http")===false){
				//HTML5::singleton()->links['css'][]="$root/$media";
				HTML5::link(array("rel"=>"stylesheet","type"=>"text/css","href"=>"$root/$media"),$at);
			} else {
				//HTML5::singleton()->links['css'][]=$media;
				HTML5::link(array("rel"=>"stylesheet","type"=>"text/css","href"=>$media),$at);
			}
		}
		else {
			HTML5::link(array("rel"=>"stylesheet","type"=>"text/css","media"=>$media,"href"=>$css),$at);
		}
	}

	static function link($link,$at=-1){
		if ($at==-1)
			HTML5::singleton()->links['raw'][]=$link;
		else {
			$max=count(HTML5::singleton()->links['raw']);
			for ($i=$max;$i>$at;$i--){
				HTML5::singleton()->links['raw'][$i]=HTML5::singleton()->links['raw'][$i-1];
			}
			HTML5::singleton()->links['raw'][$at]=$link;
		}
	}

	static function phpHeader($head){
		HTML5::singleton()->phpheader[]=$head;
	}

	static function  tag($type,$attribs){
		$ret.="<$type ";
		foreach ($attribs as $att=>$value){
			$ret.="$att='$value' ";
		}
		$ret.="/>\n";
		return $ret;
	}

	static function  closedTag($type,$attribs){
		$ret.="<$type ";
		foreach ($attribs as $att=>$value){
			$ret.="$att='$value' ";
		}
		$ret.="></$type>\n";
		return $ret;
	}

	static function printLink($href,$text=""){
		if (!is_array($href)){
			HTML5::printLink(array("href"=>$href,"text"=>"$text"));	
		}
		else {
			print "<a ";
				foreach ($href as $attrib=>$value){
					if (strtolower($attrib)<>"text")
					{
						print strtoupper($attrib)."='$value' ";
					}
				}
			print ">".$href['text']."</a>";
		}		
	}
	protected function preWrap(){
		return true;
	}
	function wrapHTML($buffer){
		$this->preWrap();
		
		if ($this->raw!=true){

			$flag['body']=!(strpos(strtolower($buffer),"<body>")===FALSE);
			$flag['head']=!(strpos(strtolower($buffer),"<head>")===FALSE);
			$flag['html']=!(strpos(strtolower($buffer),"<html>")===FALSE);
			$flag['doctype']=!(strpos(strtolower($buffer),"<!doctype")===FALSE);

			$flags ="Body=[".$flag['body']."]<br/>";
			$flags.="Head=[".$flag['head']."]<br/>";
			$flags.="Html=[".$flag['html']."]<br/>";
			$flags.="DOCTYPE=[".$flag['doctype']."]<br/>";

			$debug=$flags;
	/**/
			if (!$flag['body']){
				//$buffer="<body onLoad='try { loadPage(); } catch(e) { return true; }' >\n".$buffer."\n</body>\n";
				$buffer="<body>\n".$buffer."\n</body>\n";
			}
			else {
				$start=strpos(strtolower($buffer),"<body");
				$end  =strpos($buffer,">",$start);

				$left=substr($buffer,0,$start);
				$right=substr($buffer,$end+1);

				$html=substr($buffer,$start+5,$end-$start-4);
				//$debug.="[$start][$end]".htmlentities($html);
				$buffer="<body ".$html.$left.$right;
			}
	/**/	$left="";
			$rigth="";

			if (!$flag['head']){
				$head="<head>\n";
				$left="</head>\n";
				$right=$buffer;
			} else {
				$start=strpos(strtolower($buffer),"<head");
				$end  =strpos(strtolower($buffer),"head>",$start+5)+5;

				$left=substr($buffer,0,$start);
				$right=substr($buffer,$end+1);

				$head=substr($buffer,$start,$end-$start-7);
			}

			foreach($this->header as $key=>$line){
				$head.="$line";
			}
			
			if (isset($this->links['css']))
			foreach($this->links['css'] as $media=>$css){
				if (is_array($css)){
					$css['rel']='stylesheet';
					$head.=HTML5::tag($css);
				}
				else
					$head.="<link rel='stylesheet'".(is_numeric($media)?"":" media='$media'")." type='text/css' href='$css'/>\n";
			}

			if (isset($this->links['raw']))
			foreach($this->links['raw'] as $link){
				$head.=HTML5::tag('link',$link);
			}

			foreach($this->scripts as $key=>$line){
				if (is_array($line))
					$head.=HTML5::closedtag('script',$line);
				else
				$head.="<script type='application/x-javascript' src='$line'></script>\n";
			}
			
			if (count($this->css)){
				if ($this->raw!=true) $head.="<style type='text/css'>";				
				foreach ($this->css as $lines){
					$head.=$lines."\n";
				}
				$head.="</style>";
			}
			
			$buffer=$head.$left.$right;
	/**/
			if (!$flag['html'])
				$buffer="<html ".(isset($this->manifest)?"manifest='".$this->manifest."'":"")." class='no-js'>\n".$buffer."\n</html>\n";
			else {//Need to add no-js to html and move to top
				$start=strpos(strtolower($buffer),"<html");
				$end  =strpos($buffer,">",$start);

				$left=substr($buffer,0,$start);
				$right=substr($buffer,$end+1);

				$html=substr($buffer,$start+5,$end-$start-4);
				//$debug.="[$start][$end]".htmlentities($html);
				$buffer="<html class='no-js' ".$html.$left.$right;
			}
	/**/
			if (!$flag['doctype'])
				$buffer="<!DOCTYPE html>\n".$buffer;
			else {//Need to find and replace DOCTYPE
				$start=strpos(strtolower($buffer),"<!doctype");
				$end=strpos($buffer,">",$start);
				$left=substr($buffer,0,$start);
				$right=substr($buffer,$end+1);
				$buffer="<!DOCTYPE html>\n".$left.$right;
			}
	/**/
		} else {
			foreach($this->phpheader as $key=>$line){
				header($line);
			}
			if ($_GET['type']=='js' or $_GET['type']=='css')
				$buffer=unwrap($buffer);
		}
		//add jsFunctions
		if (count($this->jscripts)){
			if ($this->raw!=true) $js="<script type='text/javascript'>\n";
			foreach($this->jscripts as $key=>$line){
				$js.="$line\n\n";
			}
			if ($this->raw!=true) $js.="</script>\n";
			$buffer.=$js;
		}
		//add Actions
		foreach($this->actions as $key=>$line){
			$split=strpos($buffer,'name="'.$key.'"')+6;
			if ($split<7) $split=strpos($buffer,"name='$key'")+6;
			if ($split<7) $split=strpos($buffer,'id="'.$key.'"')+4;
			if ($split==0) $split=strpos($buffer,"id='$key'")+4;

			if ($split>0){
				$split+=(2+strlen($key));
				$left=substr($buffer,0,$split);
				$right=substr($buffer,$split-1);
				$buffer=$left.key($line)."='".str_replace("'",'"',current($line))."'".$right;
			}
		}		
		return $buffer;//."<div>$debug</div>";
	}

	static function dump(){
		
		$html=HTML5::singleton();
		$html->preWrap();
		
		print_line("Header");
		print_pre($html->header);
		print_line("phpHeader");
		print_pre($html->phpheader);
		print_line("Scripts");
		print_pre($html->scripts);
		print_line("Links");
		print_pre($html->links);
		print_line("Actions");
		print_pre($html->actions);
		print_line("Javascrips");
		print_pre($html->jscripts);
//		print_line("Params");
//		print_pre($html->$params);
		print_line("Raw");
		print_pre($html->raw);
		print_line("Css");
		print_pre($html->css);
		print_line("Started");
		print_pre($html->started);
		print_line("Link Translator");
		print_pre($html->linkTranslator);
		print_line("Manifest");
		print_pre($html->manifest);
		print_line("manifest_files");
		print_pre($html->manifest_files);			
	}
}

ob_start("ob_ons_tidy");

function ob_ons_tidy($buffer)
{
	$html5=HTML5::singleton();

	if (isset($html5)){
		return $html5->wrapHTML($buffer);
	}
	else {
		return $buffer;
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


	HTML5::Header("<title>".($mobile?"iWin":"weWin")."</title>");

	echo "<p>It's like comparing apples to oranges.</p>\n";

	print_pre(HTML5::singleton());//	$html5->dump();
}
//************************************************
debug_error_log("Exit ".__FILE__);
?>
