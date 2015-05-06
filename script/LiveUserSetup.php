<?php
/*
 * File LiveUserSetup.php
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
    //TODO:any gtk specific code for LiveUserSetup.php goes here
} else {
    //TODO:any web specific code for LiveUserSetup.php goes here
}
//TODO:any generic code for LiveUserSetup.php goes here

$auth =
    array(
        'debug' => true,
        'session'  => array(
            'name'     => 'PHPSESSION',           // liveuser session name
            'varname'  => 'ludata'                // liveuser session var name
        ),
        'login' => array(
            'force'    => false                   // should the user be forced to login
        ),
        'logout' => array(
            'destroy'  => true                    // whether to destroy the session on logout
        ),
        'authContainers' => array(
            "users"=>array(
                'type'          => 'MDB2',        // auth container name
                'expireTime'    => 3600,          // max lifetime of a session in seconds
                'idleTime'      => 1800,          // max time between 2 requests
                'allowDuplicateHandles' => 0,
                'allowEmptyPasswords'   => 1,     // 0=false, 1=true
                'passwordEncryptionMode'=> 'MD5',
                'storage' => array(
                    'dsn' => $config['DB_DataObject']['database'],
                    'prefix'=>'',
                    'alias' => array(             
                        'users' => 'user',
                        'auth_user_id'=>'ID',
                        'handle'=>'UserName',
                    )                    
                )
            )
        ),
        'permContainer' => array(
            'type' => 'Medium',
            'storage' => array(
                'MDB2' => array(              // storage container name
                    'dsn' => $config['DB_DataObject']['database'],
                    //'prefix' => 'liveuser_',  // table prefix
                    'tables' => array(        // contains additional tables
                                              // or fields in existing tables
                        'groups' => array(
                            'fields' => array(
                                'owner_user_id'  => false,
                                'owner_group_id' => false,
                                'is_active'      => false
                            )
                        )
                    ),
                    'fields' => array(        // contains any additional
                                              // or non-default field types
                        'owner_user_id'  => 'integer',
                        'owner_group_id' => 'integer',
                        'is_active'      => 'boolean'
                    ),
                    'alias'  => array(        // contains any additional
                                              // or non-default field alias
                        'owner_user_id'  => 'owner_user_id',
                        'owner_group_id' => 'owner_group_id',
                        'is_active'      => 'is_active'
                    )
                )
            )
        )
    );

    
$LU = LiveUser::singleton($auth);

if (!$LU->init()) {
    print_pre($LU->getErrors());
    die("Live User Error");
}



//** Eclipse Debug Code **************************
if (str_replace("/","\\",__FILE__)==str_replace("/","\\",$_SERVER["SCRIPT_FILENAME"])){
    if (class_exists('gtk',false)) {
        //TODO:any gtk specific code for LiveUserSetup.php goes here
    } else {
        //TODO:any web specific code for LiveUserSetup.php goes here
    }
    
}
//************************************************
debug_error_log("Exit ".__FILE__);
?>