<?php
require_once('Base_db.php');

class Mysql extends Base_db {
    function Mysql($config) {
        parent::Base_db();
        $this->_get_connection($config);
    }

    function get_version() {
        $result = array(0);
        $results = $this->execute_safe_query("SELECT version FROM migration_info");
        if (!$results) {
            $this->_create_schema();
        } else {
            if(function_exists("mysqli_connect")) {
                $result = mysqli_fetch_row($results);
            } else {
                $result = mysql_fetch_row($results);
            }
        }

        return (int)$result[0];
    }

    function close() {
        if(function_exists("mysqli_connect")) {
            mysqli_close($this->connection);
        } else {
            mysql_close($this->connection);
        }

    }

    function execute_raw_query($query) {
        if(function_exists("mysqli_connect")) {
            $result = mysqli_query($this->connection, $query);
            if ($result === False) {
                throw new Exception("Query could not be run.");
            }
        } else {
            $result = mysql_query($query, $this->connection);
            if ($result === False) {
                throw new Exception("Query could not be run.");
            }
        }

        return $result;
    }

    function execute_safe_query($query) {
        if(function_exists("mysqli_connect")) {
            $result = mysqli_query($this->connection, $query);
        } else {
            $result = mysql_query($query, $this->connection);
        }

        return $result;
    }

    function format_create_table($table, $query) {
        $keys = $this->_build_table($query);
        return "CREATE TABLE IF NOT EXISTS " . $table .
               " ( " . $this->_columns($keys[1]) .
               " " . $this->_primary_key($keys[2]) .
               " ) " . $this->_engine($keys[0]) .
               " " . $this->_char_set($keys[0]) .
               " " . $this->_comment($keys[0]) . ";";
    }

    function format_remove_index($table, $index_name) {
        return "ALTER TABLE " . $table . " DROP INDEX " . $index_name . ";";
    }

    function format_rename_column($table, $column_name, $new_name, $attributes) {
        return "ALTER TABLE " . $table . " CHANGE COLUMN " . $column_name . " " . $new_name . " " . $this->_build_column_type($attributes) . " " . $this->_build_remainder($attributes) . ";";
    }

    function _get_connection($config) {
        if(function_exists("mysqli_connect")) {
            $this->connection =& new Mysqli($config['host'], $config['user'], $config['password'], $config['database']) or die('Could not connect: ' . mysqli_error());
        } else {
            $this->connection =& mysql_connect($config['host'], $config['user'], $config['password'], $config['database']) or die('Could not connect: ' . mysql_error());
        }
    }

    function _create_schema() {
        $this->execute_safe_query("DROP TABLE IF EXISTS migration_info;");
        $this->execute_safe_query("CREATE TABLE migration_info(version varchar(255) NOT NULL default '0');");
        $this->execute_safe_query("INSERT INTO migration_info (version) VALUES (0);");
        return 0;
    }

    function _engine($attributes) {
        $string = "";
        if (in_array('engine', array_keys($attributes))) {
            $string = "ENGINE=" . $attributes['engine'] . " ";
        }

        return $string;
    }

    function _using($attributes) {
        $string = "";
        if (in_array('using', array_keys($attributes))) {
            $string = "USING " . strtoupper($attributes['using']);
        }

        return $string;
    }

    function _char_set($attributes) {
        if (in_array('char_set', array_keys($attributes))) {
            return 'DEFAULT CHARACTER SET = ' .  $attributes['char_set'];
        } else {
            return "";
        }
    }

    function _build_remainder($attributes) {
        $string = "";

        if (array_key_exists('not_null', $attributes) && strtolower($attributes['not_null']) == 'true') {
            $string .= " NOT NULL";
        }

        if (array_key_exists('default', $attributes) && strtolower($attributes['default']) != "null") {
            $string .= " DEFAULT '" . $attributes['default'] . "'";
        } else if (in_array('default', array_keys($attributes)) && strtolower($attributes['default']) == "null") {
            $string .= " DEFAULT NULL";
        }

        if (array_key_exists('after', $attributes)) {
            $string .= " AFTER `" . $attributes['after'] . "`";
        }

        if (array_key_exists('char_set', $attributes)) {
            $string .= " CHARACTER SET " . $attributes['char_set'];
        }

        if (array_key_exists('collate', $attributes)) {
            $string .= " COLLATE " . $attributes['collate'];
        }

        if (array_key_exists('comment', $attributes)) {
            $string .= " COMMENT " . $attributes['comment'];
        }

        if (array_key_exists('auto', $attributes)) {
            $string .= " AUTO_INCREMENT";
        }

        if (array_key_exists('unique', $attributes)) {
            $string .= " UNIQUE";
        }

        return $string;
    }
}
?>