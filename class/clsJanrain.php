<?php
/*
 * File clsJanrain.php
 * Created on 15 May 2011 by Nick
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
    //TODO:any gtk specific code for clsJanrain.php goes here
} else {
    //TODO:any web specific code for clsJanrain.php goes here
}
//TODO:any generic code for clsJanrain.php goes here
$rxp_enabled=false;

class clsJanrain {
    static $rpx_api_key = '0e9ffa8c6cf3c2eaea3380191f22acaa2acfcdf0';

    static function Script(){
        global $rxp_enabled;
        
        if (!$rxp_enabled){
            HTML5::script(array('type'=>"text/javascript",'src'=>"http://static.rpxnow.com/js/lib/rpx.js"));
        ?>
    <script type="text/javascript">
      RPXNOW.overlay = true;
      RPXNOW.language_preference = 'en';
    </script>
        <?php
        $rxp_enabled=true; 
        }
    }

    static function LogonLink($class="",$text="Sign In",$args=array()){
        global $root;
        clsJanrain::Script();
        if (!isset($args['href']))
            $args['href']="$root".$_SERVER["REQUEST_URI"];
        print "<a class='rpxnow $class' 
                  onclick='return false;' 
                  href='https://ons.rpxnow.com/openid/v2/signin?token_url=http%3A%2F%2Fiwin.local%2Frpx.php'>
                  $text
               </a>";
    }

    static function Process(){
        global $debug_out;
        global $LU;
        
        ob_start();
        
        /* STEP 1: Extract token POST parameter */
        $token = $_POST['token'];
        
        if(strlen($token) == 40) {//test the length of the token; it should be 40 characters
        
          /* STEP 2: Use the token to make the auth_info API call */
          $post_data = array('token'  => $token,
                             'apiKey' => clsJanrain::$rpx_api_key,
                             'format' => 'json',
                             'extended' => 'true'); //Extended is not available to Basic.
        
          $curl = curl_init();
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($curl, CURLOPT_URL, 'https://rpxnow.com/api/v2/auth_info');
          curl_setopt($curl, CURLOPT_POST, true);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
          curl_setopt($curl, CURLOPT_HEADER, false);
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($curl, CURLOPT_FAILONERROR, true);
          $result = curl_exec($curl);
          if ($result == false){
            echo "\n".'Curl error: ' . curl_error($curl);
            echo "\n".'HTTP code: ' . curl_errno($curl);
            echo "\n"; var_dump($post_data);
          }
          curl_close($curl);
        
        
          /* STEP 3: Parse the JSON auth_info response */
          $auth_info = json_decode($result, true);
        
          if ($auth_info['stat'] == 'ok') {
            print_line("Auth_info:");
            print_pre($auth_info);
        
            
            PEARError($provider=Safe_DataObject_factory($auth_info['profile']['providerName']));
            $provider->authInfo($auth_info);
            if (!$provider->UserID >0 ){            
                print_line("New User Credentials");
                print_pre("1) Check if there is an other account with this ID.");
                $user=Safe_DataObject_factory('user');
                $user->UserName=$provider->preferredUsername;
                if ($user->find(true)){
                    print_pre("2) if so ask if we want this account merged with it.");
                } 
                else {
                    print_pre("3) if not then we create it.");
                    
                    $user->Name=$provider->formattedName;
                    $user->insert();
                    $user->find(true);
                    $provider->UserID =$user->ID;
                    $provider->update();
                }   
            }
                //Do Logon
                    $LU->login("","",false,$provider->UserID);
                    print_line($LU->isLoggedIn()?"Logged In":"Not Logged In");                          
            } else {
              // Gracefully handle auth_info error.  Hook this into your native error handling system.
              echo "\n".'An error occured: ' . $auth_info['err']['msg']."\n";
              var_dump($auth_info);
              echo "\n";
              var_dump($result);
            }
        } else {
          // Gracefully handle the missing or malformed token.  Hook this into your native error handling system.
          echo 'Authentication canceled.';
        }
        $debug_out = ob_get_contents();
        ob_end_clean(); 
    
    }

}



//** Eclipse Debug Code **************************
if (str_replace("/","\\",__FILE__)==str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"])){
    if (class_exists('gtk',false)) {
        //TODO:any gtk specific code for clsJanrain.php goes here
    } else {
        //TODO:any web specific code for clsJanrain.php goes here
    }

}
//************************************************
debug_error_log("Exit ".__FILE__);
?>