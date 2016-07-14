<?php
function default_config() {
    return array(
                "development" => array(
                    "db" => array(
                        "driver" => "",
                        "host" => "",
                        "user" => "",
                        "password" => "",
                        "database" => ""
                                  )
                                        ),
                //Place your database information in this array
                //Use only Mysql for now
                "test" => array("db" => array(
                        "driver" => "Mysql",
                        "host" => "localhost",
                        "user" => "root",
                        "password" => "",
                        "database" => "migrate_test"
                                  )
                              ),
                "production" => array("db" => ""),
                "migrations_path" => "migrations"
                );
}

function default_database_config() {
    $ary = default_config();
    return $ary['test']['db'];
}
?>