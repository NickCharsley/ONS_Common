<?php
require_once('base.php');

class RenameTable extends Base {
    function RenameTable($db, $data) {
        parent::Base();
        $this->_clear_attributes();
        $this->db = $db;
        $this->query = $this->_determine_attributes($data);
    }

    function execute($linefeed = "\n") {
        if (is_null($this->query)) {
             error_log( "Database does not support operation{$linefeed}");
        } else {
             error_log( "Running rename table from: {$this->table}, column: {$this->new_name}{$linefeed}");
            $this->db->execute_raw_query($this->query);
        }

        return True;
    }

    function _determine_attributes($query) {
        $query = $this->_trim_table($query);
        $query = $this->_trim_new_name($query);
        return $this->db->format_rename_table($this->table, $this->new_name);
    }
}
?>
