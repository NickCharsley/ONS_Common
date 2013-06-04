<?php
class Base {
    protected $db = NULL;
    protected $table = "";
    protected $column_name = "";
    protected $new_name = "";
    protected $query = "";
    protected $attributes = array();

    function Base() {

    }

    protected function _clear_attributes() {
        $this->db = Null;
        $this->table = "";
        $this->column_name = "";
        $this->new_name = "";
        $this->query = "";
        $this->attributes = array();
    }

    protected function _trim_table($query) {
        $end = strpos($query, ",");

        if ($end === false) {
            $this->table = trim($query);
            return $this->table;
        }

        $this->table = trim(substr($query,0,$end));
        return trim(substr($query,$end + 1));
    }

    protected function _trim_column_name($query) {
        $end = strpos($query, ",");
        if ($end === false) {
            $this->column_name = trim($query);
            return $this->column_name;
        }
        $this->column_name = trim(substr($query,0,$end));
        return trim(substr($query,$end + 1));
    }

    protected function _trim_new_name($query) {
        $end = strpos($query, ",");
         if ($end === false) {
            $this->new_name = trim($query);
            return $this->new_name;
        }

        $this->new_name = trim(substr($query,0,$end));
        return trim(substr($query,$end + 1));
    }

    protected function _build_dictionary($query) {
        $elements = explode(",",$query);

        foreach($elements as $element) {
            $attribute = explode(":", $element);
            $this->attributes[strtolower(trim($attribute[0]))] = trim($attribute[1]);
        }
    }

}
?>
