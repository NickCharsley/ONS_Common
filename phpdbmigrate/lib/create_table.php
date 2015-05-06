<?php
require_once('base.php');

class CreateTable extends Base {
    function CreateTable($db,$data) {
        parent::Base();
        $this->_clear_attributes();
        $this->db = $db;
        $this->query = $this->_determine_attributes($data);
    }

    function execute($linefeed = "\n") {
        error_log("Running create table: {$this->table}");
        $this->db->execute_raw_query($this->query);
        return True;
    }

    private function _determine_attributes($query) {
        $query = $this->_trim_table($query);
        return $this->db->format_create_table($this->table, $query);
    }
}
?>
