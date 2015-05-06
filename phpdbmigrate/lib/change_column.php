<?php
require_once('base.php');

class ChangeColumn extends Base {
    function ChangeColumn($db, $data) {
        parent::Base();
        $this->_clear_attributes();
        $this->db = $db;
        $this->query = $this->_determine_attributes($data);
    }

    function execute($linefeed = "\n") {
        if (is_null($this->query)) {
            throw new Exception("Database does not support this operation");
        } else {
            error_log("Running change column to: {$this->table}, column: {$this->column_name}");
            $this->db->execute_raw_query($this->query);
        }
        return True;
    }

    private function _determine_attributes($query) {
        $query = $this->_trim_table($query);
        $query = $this->_trim_column_name($query);
        $this->_build_dictionary($query);
        return $this->db->format_change_column($this->table, $this->column_name, $this->attributes);
    }
}
?>
