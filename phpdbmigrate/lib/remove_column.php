<?php
require_once('base.php');

class RemoveColumn extends Base {
    function RemoveColumn($db, $data) {
        parent::Base();
        $this->_clear_attributes();
        $this->db = $db;
        $this->query = $this->_determine_attributes($data);
    }

    function execute($linefeed = "\n") {
        if (is_null($this->query)) {
            error_log("Database does not support operation");
        } else {
            error_log("Running remove column from: {$this->table}, column: {$this->column_name}");
            $this->db->execute_raw_query($this->query);
        }

        return True;
    }

    private function _determine_attributes($query) {
        $query = $this->_trim_table($query);
        $query = $this->_trim_column_name($query);
        return $this->db->format_remove_column($this->table, $this->column_name);
    }
}
?>
