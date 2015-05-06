<?php
/*
 * File rest.php
 * Created on 12 Jul 2012 by Nick
 * email php@oldnicksoftware.co.uk
 *
 *
 * Copyright 2012 ONS
 *
 */
 if (!defined("__COMMON__"))
 	include_once('ons_common.php');
 debug_error_log("Enter ".__FILE__);
//************************************************
//TODO:any generic code for rest.php goes here
class rest {
    public $url_elements;
    public $verb;
    public $parameters;

    public function __construct() {
        $this->verb = $_SERVER['REQUEST_METHOD'];
        $this->url_elements = explode('/', $_SERVER['PATH_INFO']);
        $this->parseIncomingParams();
        // initialise json as default format
        $this->format = 'json';
        if(isset($this->parameters['format'])) {
            $this->format = $this->parameters['format'];
        }
        $table=$this->url_elements[1];
        $do=Safe_DataObject_factory($table);
        $verb="do".ucfirst(strtolower($this->verb));

        debug_error_log("REST {$table}->{$verb}");
        $result=$do->$verb($this);
        debug_error_log("REST Result $result");

        if ($result<>"") print($result);
        else
            print_r($this);
        return true;
    }

    public function parseIncomingParams() {
        $parameters = array();

        // first of all, pull the GET vars
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $parameters);
        }

        // now how about PUT/POST bodies? These override what we got from GET
        $body = file_get_contents("php://input");
        $content_type = false;
        if(isset($_SERVER['CONTENT_TYPE'])) {
            $content_type = $_SERVER['CONTENT_TYPE'];
        }
        switch($content_type) {
            case "application/json":
                $body_params = json_decode($body);
                if($body_params) {
                    foreach($body_params as $param_name => $param_value) {
                        $parameters[$param_name] = $param_value;
                    }
                }
                $this->format = "json";
                break;
            case "application/x-www-form-urlencoded":
                parse_str($body, $postvars);
                foreach($postvars as $field => $value) {
                    $parameters[$field] = $value;

                }
                $this->format = "html";
                break;
            default:
                // we could parse other supported formats here
                break;
        }
        $this->parameters = $parameters;
    }
}

if (class_exists('gtk',false)) {
    //TODO:any gtk specific code for rest.php goes here
} else if (ons_InDrupal()) {
//TODO:any Drupal specific code for rest.php goes here
} else {
    //TODO:any web specific code for rest.php goes here
}



//** Eclipse Debug Code **************************
if (strtolower(str_replace("/","\\",__FILE__))==strtolower(str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"]))){
    if (class_exists('gtk',false)) {
        //TODO:any gtk specific code for rest.php goes here
    } else {
        //TODO:any web specific code for rest.php goes here
    }

}
//************************************************
debug_error_log("Exit ".__FILE__);
?>