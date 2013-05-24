<?php
if (!function_exists("endswith")) {
    require_once(dirname(__FILE__) . "../lib/general_functions.php");
}

class Base_db {
    var $connection = NULL;

    function Base_db() {

    }

    function execute_raw_query($query) {
        throw new Exception("Must override");
    }

    function format_create_table($table, $query) {
        throw new Exception("Subclass Must Inherit");
    }

    function format_change_column($table, $column_name, $attributes) {
        return "ALTER TABLE " . $table . " MODIFY COLUMN " . $column_name . " " . $this->_build_column_type($attributes) . " " . $this->_build_remainder($attributes) . ";";
    }

    function format_add_index($table, $index_name, $query) {
        $attributes = $this->_index_build_dictionary($query);
        return "CREATE " . $this->_unique($attributes) . " INDEX " . $index_name . " " . $this->_using($attributes) . " ON " . $table . " (" . $this->_index_build_remainder($attributes) . ");";
    }

    function format_add_column($table, $column_name, $attributes) {
        return "ALTER TABLE " . $table . " ADD " . $column_name . " " . $this->_build_column_type($attributes) . " " . $this->_build_remainder($attributes) . ";";
    }

    function format_rename_column($table, $column_name, $new_name, $attributes) {
        throw new Exception("Subclass Must Inherit");
    }

    function format_rename_table($table, $new_name) {
        return "ALTER TABLE " . $table . " RENAME TO " . $new_name . ";";
    }

    function format_remove_column($table, $column_name) {
        return "ALTER TABLE " . $table . " DROP COLUMN " . $column_name . ";";
    }

    function format_drop_table($table) {
        return "DROP TABLE " . $table . ";";
    }

    function update_schema($version) {
        $query = "UPDATE migration_info SET version = '" . $version . "';";
        $this->execute_safe_query($query);
    }

    private function _strip_comma($desc) {
        if (endswith(trim($desc), ",")) {
            $comma = strrpos($desc, ",");
            $desc = trim(substr($desc, 0, $comma));
        }
        if (startswith(trim($desc), ",")) {
            $comma = strpos($desc, ",");
            $desc = trim(substr($desc, $comma + 1));
        }
        return $desc;
    }

    private function _build_table($query) {
        $desc = "";
        $columns_string = "";
        $table_description = array();
        $columns_description = array();
        $pk_pos = strpos($query, "primary_key:");
        $col_pos = strpos($query, "column:");

        if (($pk_pos < $col_pos) && $pk_pos !== FALSE) {
            $desc = substr($query, 0, $pk_pos);
            $columns_string = substr($query, $pk_pos);
        } else if ($pk_pos > $col_pos && $pk_pos !== FALSE) {
            $desc = substr($query, 0, $col_pos);
            $columns_string = substr($query, $col_pos, $pk_pos);
        } else {
            $desc = substr($query, 0, $col_pos);
            $columns_string = substr($query, $col_pos);
        }

        $table_description = $this->_build_table_description($desc);
        $keys = $this->_build_primary_key($columns_string);

        $columns_description = $this->_build_columns_list($keys[1]);
        return array($table_description, $columns_description, $keys[0]);
    }

    private function _build_columns_list($desc) {
        $columns = array();
        $keys = explode("column:", $desc);
        foreach ($keys as $key) {
            if (!empty($key)) {
                $columns[] = trim($key);
            }
        }
        return $columns;
    }

    private function _build_primary_key($desc) {
        $pk_pos = strpos($desc, "primary_key:");
        $col_pos = strpos($desc, "column:");

        $primary_key = "";
        $primary_string = "";
        $column_string = "";

        if ($pk_pos < $col_pos && $pk_pos !== False) {
            #we only have to cut once
            $primary_string = substr($desc, 0, $col_pos);
            $column_string = substr($desc, $col_pos);
        } else if ($pk_pos > $col_pos && $pk_pos !== False) {
            #check to make sure that primary key isn't mixed in
            $test_pos = strrpos($desc, "column:");
            if ($test_pos > $pk_pos) {
                #mixed in
                $column_string = substr($desc, 0, $pk_pos) . substr($desc, $test_pos);
            } else {
                $primary_string = substr($desc, $pk_pos);
                $column_string = substr($desc, 0, $pk_pos);
            }
        } else {
            $column_string = $desc;
        }

        $primary_string = $this->_strip_comma($primary_string);
        $column_string = $this->_strip_comma($column_string);
        $primary_string = trim(str_replace("primary_key:", "", $primary_string));

        return array($primary_string, $column_string);
    }

    private function _columns($columns) {
        $string = "";
        $count = 0;
        foreach($columns as $element) {
            $keys = $this->_column_name($element);
            if ($count == 0) {
                $string = $keys[0] . " " . $this->_format_attributes($keys[1]);
                $count = 1;
            } else {
                $string = $string . "," . $keys[0] . " " . $this->_format_attributes($keys[1]);
            }
        }

        $string = $this->_strip_comma($string);

        return trim($string);
    }

    private function _format_attributes($desc) {
        $attribute_dict = $this->_build_dictionary($desc);
        $column_type = $this->_build_column_type($attribute_dict);
        $remainder_string = $this->_build_remainder($attribute_dict);
        return "{$column_type} {$remainder_string}";
    }

    private function _build_table_description($desc) {
        $desc = $this->_strip_comma($desc);
        $attributes = array();
        $elements = explode(",", $desc);

        foreach($elements as $element) {
            $attribute = explode(":", $element);
            $attributes[strtolower(trim($attribute[0]))] = trim($attribute[1]);
        }

        return $attributes;
    }

    private function _primary_key($primary_key) {
        if ($primary_key != "") {
            return ", PRIMARY KEY ({$primary_key})";
        } else {
            return "";
        }
    }

    private function _comment($attributes) {
        if (array_key_exists('comment', $attributes)) {
            return "COMMENT = '{$attributes['comment']}'";
        } else {
            return "";
        }
    }

    private function _column_name($desc) {
        $comma = strpos($desc, ",");
        $name = substr($desc, 0, $comma);
        $name = str_replace("column:", "", $name);
        return array($this->_strip_comma(trim($name)), $this->_strip_comma(substr($desc, $comma)));
    }

    private function _build_dictionary($query) {
        $attributes = array();
        $elements = explode(",", $query);
        foreach($elements as $element) {
            $attribute = explode(":", $element);
            $attributes[trim(strtolower($attribute[0]))] = trim($attribute[1]);
        }

        return $attributes;
    }

    private function _build_column_type($attributes) {
        $type = strtoupper($attributes['type']);

        if (array_key_exists('length', $attributes)) {
            $type = "{$type}({$attributes['length']}";

            if (array_key_exists('precision', $attributes)) {
                $type = "{$type},{$attributes['precision']})";
            } else {
                $type = "{$type})";
            }
        }
        return $type;
    }

    private function _unique($attributes) {
        if (array_key_exists('unique', $attributes) && strtolower($attributes['unique']) == "true") {
            return "UNIQUE";
        }
        return "";
    }

    private function _engine($attributes) {
        return "";
    }

    private function _char_set($attributes) {
        return "";
    }

    private function _using($attributes) {
        return "";
    }

    private function _index_build_dictionary($query) {
        $attributes = array();

        foreach ( $query as $k => $v) {
            $attributes[trim(strtolower($k))] = trim($v);
        }

        $columns = str_replace("[", "", str_replace("]", "", $attributes['columns']));
        $columns = explode(",", $columns);
        $attributes['columns'] = $columns;

        return $attributes;
    }

    private function _index_column_build($columns) {
        $listing = array();
        $new_columns = explode(",", $columns);
        for ($i = 0; $i < sizeof($new_columns); $i++) {
            $new_columns[$i] = trim($new_columns[$i]);
        }

        foreach($new_columns as $column) {
            $elements = explode(" ", $column);
            $elements[0] = str_replace("`", "", $elements[0]);
            $elements[0] = str_replace("'", "", $elements[0]);

            if (sizeof($elements) == 1) {
                $listing[] = trim($elements[0]);
            } else {
                $listing[] = trim($elements[0]) . " " . trim($elements[1]);
            }
        }

        return $listing;
    }

    private function _index_build_remainder($attributes) {
        $string = "";
        $count = 0;
        foreach($attributes['columns'] as $column) {
            if ($count > 0) {
                $string = "{$string},{$column}";
            } else {
                $count = 1;
                $string = $column;
            }
        }

        return $string;
    }
}
?>