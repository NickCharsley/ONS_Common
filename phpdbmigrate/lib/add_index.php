<?php
require_once('base.php');

class AddIndex extends Base {
    function AddIndex($db, $data) {
        parent::Base();$this->_clear_attributes();
        $this->db = $db;
        $this->query = $this->_determine_attributes($data);
    }

    function execute($linefeed = "\n") {
         error_log( "Running add index to: " . $this->table . ", column: " . $this->column_name . $linefeed);
        $this->db->execute_raw_query($this->query);
        return True;
    }

    function _determine_attributes($query) {
        $query = $this->_trim_table($query);
        $query = $this->_trim_column_name($query);
        $this->_build_index_dictionary($query);

        return $this->db->format_add_index($this->table, $this->column_name, $this->attributes);
    }

    function _build_index_dictionary($query) {
        $attributes = array();
        $start = strpos($query, "[");

        $head = substr($query, 0, $start);
        $tail = substr($query, $start);
        $this->_build_dictionary($head);
        $this->attributes['columns'] = $tail;
    }
}
?>
