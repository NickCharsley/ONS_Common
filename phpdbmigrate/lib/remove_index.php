<?php
require_once('base.php');

class RemoveIndex extends Base {
    function RemoveIndex($db, $data) {
        parent::Base();
        $this->_clear_attributes();
        $this->db = $db;
        $this->query = $this->_determine_attributes($data);
    }

    function execute($linefeed = "\n") {
        if (is_null($this->query)) {
             error_log( "Database does not support operation{$linefeed}");
        } else {
             error_log( "Running remove index from: {$this->table}, index: {$this->column_name}{$linefeed}");
            $this->db->execute_raw_query($this->query);
        }

        return True;
    }

    function _determine_attributes($query) {
        $query = $this->_trim_table($query);
        $query = $this->_trim_column_name($query);
        return $this->db->format_remove_index($this->table, $this->column_name);
    }
}
?>
