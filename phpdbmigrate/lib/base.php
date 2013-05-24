<?php
class Base {
    var $db = NULL;
    var $table = "";
    var $column_name = "";
    var $new_name = "";
    var $query = "";
    var $attributes = array();

    function Base() {

    }

    private function _clear_attributes() {
        $this->db = Null;
        $this->table = "";
        $this->column_name = "";
        $this->new_name = "";
        $this->query = "";
        $this->attributes = array();
    }

    private function _trim_table($query) {
        $end = strpos($query, ",");

        if ($end === false) {
            $this->table = trim($query);
            return $this->table;
        }

        $this->table = trim(substr($query,0,$end));
        return trim(substr($query,$end + 1));
    }

    private function _trim_column_name($query) {
        $end = strpos($query, ",");
        if ($end === false) {
            $this->column_name = trim($query);
            return $this->column_name;
        }
        $this->column_name = trim(substr($query,0,$end));
        return trim(substr($query,$end + 1));
    }

    private function _trim_new_name($query) {
        $end = strpos($query, ",");
         if ($end === false) {
            $this->new_name = trim($query);
            return $this->new_name;
        }

        $this->new_name = trim(substr($query,0,$end));
        return trim(substr($query,$end + 1));
    }

    private function _build_dictionary($query) {
        $elements = explode(",",$query);

        foreach($elements as $element) {
            $attribute = explode(":", $element);
            $this->attributes[strtolower(trim($attribute[0]))] = trim($attribute[1]);
        }
    }

}
?>
