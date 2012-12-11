<?php
/*
 * File Auth_Log_Observer.php
 * Created on 18 May 2011 by Nick
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
    //TODO:any gtk specific code for Auth_Log_Observer.php goes here
} else {
    //TODO:any web specific code for Auth_Log_Observer.php goes here
}
//TODO:any generic code for Auth_Log_Observer.php goes here
class Auth_Log_Observer extends Log_observer {

    private $messages = array();

    function notify($event) {

        $this->messages[] = $event;

    }

    function dump(){
        print '<h3>Logging Output:</h3>'
             .'<b>'.$this->_priority.' level messages:</b><br/>';

        foreach ($this->messages as $event) {
            print $event['priority'].': '.$event['message'].'<br/>';
        }
    }

}



//** Eclipse Debug Code **************************
if (str_replace("/","\\",__FILE__)==str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"])){
    if (class_exists('gtk',false)) {
        //TODO:any gtk specific code for Auth_Log_Observer.php goes here
    } else {
        //TODO:any web specific code for Auth_Log_Observer.php goes here
    }

}
//************************************************
debug_error_log("Exit ".__FILE__);
?>