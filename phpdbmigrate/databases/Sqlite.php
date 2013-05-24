<?php
require_once('Base_db.php');

class Sqlite extends Base_db {
    function Sqlite($config) {
        parent::Base_db();
        $this->_get_connection($config);
    }

    function get_version() {
        $result = array(0);
        $results = $this->execute_raw_query("SELECT version FROM schema_info");
        if (!$results) {
            $this->_create_schema();
        } else {
            $result = $results->fetch();
        }
        
        return (int)$result[0];
    }

    function close() {
        sqlite_close($this->connection);
    }

    function execute_raw_query($query) {
        if ($query == "") {
            return;
        }

        $result = $this->connection->query($query, SQLITE_ASSOC, $query_error);
            
        return $result;
    }

    function format_create_table($table, $query) {
        $keys = $this->_build_table($query);
        return "CREATE TABLE IF NOT EXISTS " . $table .
               " ( " . $this->_columns($keys[1]) .
               " ) " . $this->_engine($keys[0]) .
               " " . $this->_char_set($keys[0]) .
               " " . $this->_comment($keys[0]) . ";";
    }

    function format_remove_index($table, $index_name) {
        return "DROP INDEX " . $index_name . ";";
    }

    function format_add_column($table, $column_name, $attributes) {
        return "ALTER TABLE " . $table . " ADD COLUMN " . $column_name . " " . $this->_build_column_type($attributes) . " " . $this->_build_remainder($attributes);
    }

    function format_rename_column($table, $column_name, $new_name, $attributes) {
        return "";
    }

    function format_change_column($table, $column_name, $attributes) {
        return "";
    }

    function format_remove_column($table, $column_name) {
        return "";
    }

    private function _get_connection($config) {
        $this->connection =& new SQLiteDatabase($config['database'], 0666, $error) or die($error);
    }

    private function _create_schema() {
        $this->execute_raw_query("DROP TABLE IF EXISTS schema_info;");
        $this->execute_raw_query("CREATE TABLE schema_info(version varchar(255) default '000');");
        $this->execute_raw_query("INSERT INTO schema_info (version) VALUES ('000');");
        return 0;
    }

    private function _build_remainder($attributes) {
        $string = "";

        if (array_key_exists('not_null', $attributes) && strtolower($attributes['not_null']) == 'true') {
            $string .= " NOT NULL";
        }

        if (array_key_exists('default', $attributes) && strtolower($attributes['default']) != "null") {
            $string .= " DEFAULT '" . $attributes['default'] . "'";
        } else if (in_array('default', array_keys($attributes)) && strtolower($attributes['default']) == "null") {
            $string .= " DEFAULT NULL";
        }

        if (array_key_exists('collate', $attributes)) {
            $string .= " COLLATE " . $attributes['collate'];
        }

        if (array_key_exists('comment', $attributes)) {
            $string .= " COMMENT " . $attributes['comment'];
        }

        if (array_key_exists('auto', $attributes)) {
            $string .= " PRIMARY KEY AUTOINCREMENT";
        }

        if (array_key_exists('unique', $attributes)) {
            $string .= " UNIQUE";
        }

        return $string;
    }
}
?>
